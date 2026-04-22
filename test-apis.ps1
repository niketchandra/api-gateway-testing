# AtGlance API Comprehensive Test Suite
# Tests all endpoints, captures responses, diagnoses failures, and generates reports

param(
    [string]$ProvidedPatToken = ""
)

$ErrorActionPreference = "Continue"
$baseUrl = "http://localhost:8002"
$apiBaseUrl = "$baseUrl"
$timestamp = Get-Date -Format "yyyy-MM-dd_HHmmss"
$reportDir = "api-test-reports"
$reportName = "api-status-report"

if (-not (Test-Path $reportDir)) { New-Item -ItemType Directory -Path $reportDir | Out-Null }

$testResults = @()
$sessionToken = $null
$patToken = if ([string]::IsNullOrWhiteSpace($ProvidedPatToken)) { $null } else { $ProvidedPatToken }

function Test-Endpoint {
    param(
        [string]$Method,
        [string]$Endpoint,
        [string]$Name,
        [hashtable]$Headers = @{},
        [object]$Body = $null,
        [string]$Description = ""
    )
    
    $fullUrl = "$apiBaseUrl$Endpoint"
    $testResult = @{
        name = $Name
        description = $Description
        method = $Method
        endpoint = $Endpoint
        fullUrl = $fullUrl
        expectedStatus = 200
        actualStatus = 0
        success = $false
        requestBody = if ($Body) { $Body | ConvertTo-Json -Depth 5 } else { "N/A" }
        responseBody = ""
        errorMessage = ""
        rootCause = ""
        remediation = ""
        duration = 0
    }
    
    try {
        $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
        
        $params = @{
            Uri = $fullUrl
            Method = $Method
            ContentType = "application/json"
            Headers = $Headers
            ErrorAction = "Stop"
            TimeoutSec = 30
            MaximumRedirection = 0
        }
        
        if ($Body) {
            $params['Body'] = $Body | ConvertTo-Json -Depth 5
        }
        
        $response = Invoke-RestMethod @params
        
        $stopwatch.Stop()
        $testResult.actualStatus = 200
        $testResult.success = $true
        $testResult.responseBody = $response | ConvertTo-Json -Depth 5
        $testResult.duration = $stopwatch.ElapsedMilliseconds
        
        if ($response.PSObject.Properties['token']) {
            $script:sessionToken = $response.token
            $testResult.responseBody += "`n[TOKEN_EXTRACTED]"
        }
        if ($response.PSObject.Properties['pat_token']) {
            $script:patToken = $response.pat_token
            $testResult.responseBody += "`n[PAT_TOKEN_EXTRACTED]"
        }
    }
    catch {
        $testResult.actualStatus = if ($_.Exception.Response.StatusCode) { [int]$_.Exception.Response.StatusCode.value__ } else { 0 }
        $testResult.success = $false
        $testResult.errorMessage = $_.Exception.Message
        $testResult.duration = if ($stopwatch) { $stopwatch.ElapsedMilliseconds } else { 0 }
        
        try {
            if ($_.Exception.Response.Content) {
                $errorResponse = $_.Exception.Response.Content | ConvertFrom-Json
                $testResult.responseBody = $errorResponse | ConvertTo-Json -Depth 5
                if ($errorResponse.message) { $testResult.errorMessage = $errorResponse.message }
            }
        }
        catch { }
        
        if ($testResult.errorMessage -match "connection refused|connection reset") {
            $testResult.rootCause = "Service not responding. Container may not be running."
            $testResult.remediation = "Run: docker compose ps; check all containers are Up."
        }
        elseif ($testResult.errorMessage -match "timeout|timed out") {
            $testResult.rootCause = "Request timeout. Service unresponsive."
            $testResult.remediation = "Check service logs: docker compose logs"
        }
        elseif ($testResult.actualStatus -eq 401) {
            $testResult.rootCause = "Unauthorized. Invalid authentication token."
            $testResult.remediation = "Verify token in Authorization header is valid."
        }
        elseif ($testResult.actualStatus -eq 403) {
            $testResult.rootCause = "Forbidden. User lacks required role permissions."
            $testResult.remediation = "Verify user role (100=superadmin, 101=admin, 102=user)"
        }
        elseif ($testResult.actualStatus -eq 404) {
            $testResult.rootCause = "Endpoint not found. Route missing."
            $testResult.remediation = "Check kong.yml and routes/api.php"
        }
        elseif ($testResult.actualStatus -eq 302) {
            $testResult.rootCause = "Unexpected redirect to upstream host. Kong returned a Location header pointing at api:8000."
            $testResult.remediation = "Check the backend controller for redirect() calls and verify Kong upstream/host handling for API routes."
        }
        elseif ($testResult.actualStatus -eq 422) {
            $testResult.rootCause = "Validation failed. Invalid request schema."
            $testResult.remediation = "Review request body and validation rules."
        }
        elseif ($testResult.actualStatus -eq 500) {
            $testResult.rootCause = "Server error. Laravel exception occurred."
            $testResult.remediation = "Check: docker compose logs api | grep -i error"
        }
    }
    
    $script:testResults += $testResult
}

# =============================================================================
# Test Sections
# =============================================================================
Write-Host "`n[1] Auth Endpoints..." -ForegroundColor Cyan

Test-Endpoint -Method "POST" -Endpoint "/auth/register" `
    -Name "Register New User" `
    -Description "Create user account" `
    -Body @{
        name = "testuser_$(Get-Random)"
        email = "testuser_$(Get-Random)@test.local"
        password = "TestPass@123"
        password_confirmation = "TestPass@123"
        dob = "1990-01-01"
    }

Test-Endpoint -Method "POST" -Endpoint "/auth/login" `
    -Name "Login with Credentials" `
    -Description "Obtain session token" `
    -Body @{
        email = "superadmin@admin.com"
        password = "Atglance@123"
    }

if ($sessionToken -and -not $patToken) {
    Write-Host "Session token acquired" -ForegroundColor Green
    
    Test-Endpoint -Method "POST" -Endpoint "/auth/pat-tokens" `
        -Name "Create PAT Token" `
        -Description "Mint PAT token" `
        -Headers @{ "Authorization" = "Bearer $sessionToken" } `
        -Body @{}
}

if ($patToken) {
    Write-Host "PAT token available" -ForegroundColor Green
}

# =============================================================================
Write-Host "`n[2] Token Validation..." -ForegroundColor Cyan

Test-Endpoint -Method "GET" -Endpoint "/auth/validate-token" `
    -Name "Validate Token GET" `
    -Description "Validate from header" `
    -Headers @{ "Authorization" = "Bearer $(if($patToken) { $patToken } else { 'invalid' })" }

Test-Endpoint -Method "POST" -Endpoint "/auth/validate-token" `
    -Name "Validate Token POST" `
    -Description "Validate from body" `
    -Body @{ token = if ($patToken) { $patToken } else { "invalid" } }

# =============================================================================
if ($patToken) {
    Write-Host "`n[3] User Management..." -ForegroundColor Cyan
    
    Test-Endpoint -Method "GET" -Endpoint "/users" `
        -Name "List Users" `
        -Description "Get all users" `
        -Headers @{ "Authorization" = "Bearer $patToken" }
    
    Test-Endpoint -Method "POST" -Endpoint "/users" `
        -Name "Create User" `
        -Description "Create new user" `
        -Headers @{ "Authorization" = "Bearer $patToken" } `
        -Body @{
            name = "NewUser_$(Get-Random)"
            email = "new_$(Get-Random)@test.local"
            password = "TempPass@123"
        }
}

# =============================================================================
if ($patToken) {
    Write-Host "`n[4] System Registration..." -ForegroundColor Cyan
    
    Test-Endpoint -Method "POST" -Endpoint "/system-register?system_name=test-postman01&os_type=postman&ip_address=127.0.0.1&validation_hash=1121212121212121212121212" `
        -Name "Register System" `
        -Description "Register system" `
        -Headers @{ "Authorization" = "Bearer $patToken" } `
        -Body @{}
    
    Test-Endpoint -Method "GET" -Endpoint "/system-register" `
        -Name "List Systems" `
        -Description "List systems" `
        -Headers @{ "Authorization" = "Bearer $patToken" }
}

# =============================================================================
if ($patToken) {
    Write-Host "`n[5] Services..." -ForegroundColor Cyan
    
    Test-Endpoint -Method "POST" -Endpoint "/services" `
        -Name "Create Service" `
        -Description "Create service" `
        -Headers @{ "Authorization" = "Bearer $patToken" } `
        -Body @{
            service_name = "svc_$(Get-Random)"
            system_id = 1
            description = "Test"
        }
    
    Test-Endpoint -Method "GET" -Endpoint "/services" `
        -Name "List Services" `
        -Description "List services" `
        -Headers @{ "Authorization" = "Bearer $patToken" }
}

# =============================================================================
if ($patToken) {
    Write-Host "`n[6] Configuration Files..." -ForegroundColor Cyan
    
    Test-Endpoint -Method "POST" -Endpoint "/config-files/upload" `
        -Name "Upload Config" `
        -Description "Upload config file" `
        -Headers @{ "Authorization" = "Bearer $patToken" } `
        -Body @{
            config_name = "cfg_$(Get-Random)"
            service_id = 1
            system_id = 1
            content = "database: localhost"
            description = "Config"
        }
    
    Test-Endpoint -Method "GET" -Endpoint "/config-files" `
        -Name "List Configs" `
        -Description "List configs" `
        -Headers @{ "Authorization" = "Bearer $patToken" }
}

Write-Host "`n[8] Error Scenarios..." -ForegroundColor Cyan

Test-Endpoint -Method "GET" -Endpoint "/nonexistent" `
    -Name "Invalid Endpoint 404" `
    -Description "Test 404 error"

Test-Endpoint -Method "POST" -Endpoint "/auth/login" `
    -Name "Bad Credentials 401" `
    -Description "Test invalid auth" `
    -Body @{
        email = "superadmin@admin.com"
        password = "WrongPassword"
    }

Test-Endpoint -Method "GET" -Endpoint "/users" `
    -Name "Missing Token 401" `
    -Description "No auth header"

Test-Endpoint -Method "POST" -Endpoint "/users" `
    -Name "Validation Error 422" `
    -Description "Missing required" `
    -Headers @{ "Authorization" = "Bearer $(if($patToken) { $patToken } else { 'token' })" } `
    -Body @{ name = "Incomplete" }

# =============================================================================
# Report Generation
# =============================================================================
Write-Host "`n[Generating Reports]" -ForegroundColor Cyan

$passCount = ($testResults | Where-Object { $_.success }).Count
$failCount = ($testResults | Where-Object { -not $_.success }).Count
$totalCount = $testResults.Count
$passRate = if ($totalCount -gt 0) { [math]::Round(($passCount / $totalCount) * 100, 2) } else { 0 }

# JSON Report
$jsonReport = @{
    metadata = @{
        timestamp = $timestamp
        baseUrl = $apiBaseUrl
        totalTests = $totalCount
        passedTests = $passCount
        failedTests = $failCount
        passRate = $passRate
    }
    testResults = $testResults
} | ConvertTo-Json -Depth 10

$jsonFile = "$reportDir\$reportName-$timestamp.json"
$jsonReport | Out-File -FilePath $jsonFile -Encoding UTF8
Write-Host "JSON: $jsonFile" -ForegroundColor Green

# HTML Report
$htmlRows = ""
foreach ($result in $testResults) {
    $bg = if ($result.success) { "style='background:#d4edda'" } else { "style='background:#f8d7da'" }
    $status = if ($result.success) { "PASS" } else { "FAIL" }
    $htmlRows += "<tr $bg><td>$($result.name)</td><td>$($result.method)</td><td>$($result.endpoint)</td><td>$status</td><td>$($result.actualStatus)</td><td>$($result.duration)ms</td></tr>"
}

$detailHtml = ""
foreach ($result in $testResults) {
    $bg = if ($result.success) { "#d4edda" } else { "#f8d7da" }
    $status = if ($result.success) { "PASS" } else { "FAIL" }
    
    $detailHtml += "<div style='border:1px solid #ddd;padding:15px;margin:10px 0;background:$bg;border-radius:5px'>"
    $detailHtml += "<h3>$($result.name) - $status</h3>"
    $detailHtml += "<p><b>Endpoint:</b> $($result.method) $($result.endpoint)</p>"
    $detailHtml += "<p><b>Status Code:</b> $($result.actualStatus) | <b>Duration:</b> $($result.duration)ms</p>"
    $detailHtml += "<p><b>Description:</b> $($result.description)</p>"
    
    if (-not $result.success) {
        $detailHtml += "<p><b>Error:</b> $([System.Web.HttpUtility]::HtmlEncode($result.errorMessage))</p>"
        $detailHtml += "<p><b>Root Cause:</b> $($result.rootCause)</p>"
        $detailHtml += "<p><b>Remediation:</b> $($result.remediation)</p>"
    }
    
    $respPreview = if ($result.responseBody) { $result.responseBody.Substring(0, [Math]::Min(300, $result.responseBody.Length)) } else { "No response" }
    $detailHtml += "<p><b>Response Sample:</b><pre style='background:#fff;padding:10px;border:1px solid #ccc;overflow:auto;max-height:200px'>$([System.Web.HttpUtility]::HtmlEncode($respPreview))</pre></p>"
    $detailHtml += "</div>"
}

$htmlContent = @"
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>AtGlance API Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 20px; color: #333; }
        .header { background: #667eea; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
        .card { background: white; padding: 20px; border-radius: 5px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h3 { color: #667eea; font-size: 2em; margin: 0 0 10px 0; }
        table { width: 100%; border-collapse: collapse; background: white; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th { background: #667eea; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #ddd; }
        tr:hover { background: #f9f9f9; }
        h2 { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px; margin-top: 30px; }
        footer { text-align: center; margin-top: 40px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>AtGlance API Test Report</h1>
        <p>Generated: $timestamp</p>
        <p>Base URL: $apiBaseUrl</p>
    </div>
    
    <div class='summary'>
        <div class='card'><h3>$totalCount</h3><p>Total Tests</p></div>
        <div class='card'><h3>$passCount</h3><p>Passed</p></div>
        <div class='card'><h3>$failCount</h3><p>Failed</p></div>
        <div class='card'><h3>$passRate%</h3><p>Pass Rate</p></div>
    </div>
    
    <h2>Test Results Summary</h2>
    <table>
        <tr>
            <th>Test Name</th>
            <th>Method</th>
            <th>Endpoint</th>
            <th>Status</th>
            <th>Code</th>
            <th>Duration</th>
        </tr>
        $htmlRows
    </table>
    
    <h2>Detailed Results and Diagnostics</h2>
    $detailHtml
    
    <footer>
        <p>AtGlance API Comprehensive Test Suite</p>
        <p>Total: $totalCount | Passed: $passCount | Failed: $failCount | Rate: $passRate%</p>
    </footer>
</body>
</html>
"@

$htmlFile = "$reportDir\$reportName-$timestamp.html"
$htmlContent | Out-File -FilePath $htmlFile -Encoding UTF8
Write-Host "HTML: $htmlFile" -ForegroundColor Green

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "TEST EXECUTION COMPLETE" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Total Tests:    $totalCount" -ForegroundColor White
Write-Host "Passed:         $passCount" -ForegroundColor Green
Write-Host "Failed:         $failCount" -ForegroundColor Red
Write-Host "Success Rate:   $passRate%" -ForegroundColor Yellow
Write-Host "Report Dir:     $(Resolve-Path $reportDir)" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing

$width = 2200
$height = 1400
$bmp = New-Object System.Drawing.Bitmap($width, $height)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.Clear([System.Drawing.Color]::FromArgb(248,250,252))

$fontTitle = New-Object System.Drawing.Font('Segoe UI', 24, [System.Drawing.FontStyle]::Bold)
$fontSection = New-Object System.Drawing.Font('Segoe UI', 14, [System.Drawing.FontStyle]::Bold)
$fontText = New-Object System.Drawing.Font('Segoe UI', 11, [System.Drawing.FontStyle]::Regular)
$fontSmall = New-Object System.Drawing.Font('Segoe UI', 10, [System.Drawing.FontStyle]::Regular)
$titleBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(17,24,39))
$mutedBrush = New-Object System.Drawing.SolidBrush([System.Drawing.Color]::FromArgb(75,85,99))

function Draw-Box {
    param($x,$y,$w,$h,$fillColor,$borderColor,$title,$lines,$titleColor)
    $rect = New-Object System.Drawing.RectangleF($x,$y,$w,$h)
    $fill = New-Object System.Drawing.SolidBrush($fillColor)
    $pen = New-Object System.Drawing.Pen($borderColor,2)
    $g.FillRectangle($fill,$rect)
    $g.DrawRectangle($pen,$x,$y,$w,$h)
    $titleBrushLocal = New-Object System.Drawing.SolidBrush($titleColor)
    $g.DrawString($title,$fontSection,$titleBrushLocal,$x+14,$y+10)
    $lineY = $y + 42
    foreach($line in $lines){
        $g.DrawString($line,$fontSmall,$mutedBrush,$x+14,$lineY)
        $lineY += 20
    }
    $fill.Dispose()
    $pen.Dispose()
    $titleBrushLocal.Dispose()
}

function Draw-Arrow {
    param($x1,$y1,$x2,$y2,$color)
    $pen = New-Object System.Drawing.Pen($color,3)
    $pen.CustomEndCap = New-Object System.Drawing.Drawing2D.AdjustableArrowCap(6,8,$true)
    $g.DrawLine($pen,$x1,$y1,$x2,$y2)
    $pen.Dispose()
}

$g.DrawString('AtGlance System Architecture',$fontTitle,$titleBrush,40,24)
$g.DrawString('Laravel + Kong API Gateway + MySQL + Redis + Queue Worker + Storage (Dockerized)',$fontText,$mutedBrush,44,66)

Draw-Box 60 130 320 140 ([System.Drawing.Color]::FromArgb(236,253,245)) ([System.Drawing.Color]::FromArgb(16,185,129)) 'Clients & Operators' @('Web Browser Dashboard','CLI / Agents / Scripts','Postman Collection') ([System.Drawing.Color]::FromArgb(6,95,70))
Draw-Box 450 130 360 140 ([System.Drawing.Color]::FromArgb(239,246,255)) ([System.Drawing.Color]::FromArgb(59,130,246)) 'Kong API Gateway' @('DB-less config from kong/kong.yml','Routes: /auth, /users, /products, /files','Rate limiting + gateway controls') ([System.Drawing.Color]::FromArgb(30,64,175))
Draw-Box 900 100 540 220 ([System.Drawing.Color]::FromArgb(255,247,237)) ([System.Drawing.Color]::FromArgb(249,115,22)) 'Laravel App (composer/)' @('Web UI: dashboard, settings, system views','API Controllers: auth, users, products, files, config-files','Security: session auth + PAT token validation','Resilience: Circuit Breaker services','Background Jobs: create/update/delete via queue') ([System.Drawing.Color]::FromArgb(154,52,18))
Draw-Box 1520 130 300 140 ([System.Drawing.Color]::FromArgb(250,245,255)) ([System.Drawing.Color]::FromArgb(139,92,246)) 'Queue Worker' @('Consumes queued jobs','Retries with backoff','Recovers failed writes') ([System.Drawing.Color]::FromArgb(91,33,182))

Draw-Box 900 390 300 180 ([System.Drawing.Color]::FromArgb(239,246,255)) ([System.Drawing.Color]::FromArgb(37,99,235)) 'MySQL 8.0' @('users, workspaces, system_register','services, configuration_files','raw_data, activity_logs, tokens') ([System.Drawing.Color]::FromArgb(30,58,138))
Draw-Box 1260 390 260 180 ([System.Drawing.Color]::FromArgb(254,242,242)) ([System.Drawing.Color]::FromArgb(239,68,68)) 'Redis 7' @('Queue buffering','Cache / resilience state','Supports degraded mode') ([System.Drawing.Color]::FromArgb(127,29,29))
Draw-Box 1580 390 300 180 ([System.Drawing.Color]::FromArgb(240,253,250)) ([System.Drawing.Color]::FromArgb(20,184,166)) 'File Storage' @('Local: storage/app/private','Optional S3 disk','UUID file locations') ([System.Drawing.Color]::FromArgb(17,94,89))

Draw-Box 80 650 1880 220 ([System.Drawing.Color]::FromArgb(243,244,246)) ([System.Drawing.Color]::FromArgb(107,114,128)) 'Deployment Topology (docker-compose + docker-compose-kong)' @('Containers: api, mysql, redis, queue-worker, kong, phpmyadmin','Ingress: Kong proxy :8002 -> Laravel API :8000','Admin: Kong admin :8001, phpMyAdmin :8080','Volumes/network keep app code and service communication') ([System.Drawing.Color]::FromArgb(55,65,81))

Draw-Box 80 940 900 180 ([System.Drawing.Color]::FromArgb(254,249,195)) ([System.Drawing.Color]::FromArgb(202,138,4)) 'Primary Request Flow' @('1) Client -> Kong gateway','2) Kong -> Laravel route/controller','3) Laravel checks auth + circuit state','4) Closed circuit: DB/storage write/read response') ([System.Drawing.Color]::FromArgb(113,63,18))
Draw-Box 1060 940 900 180 ([System.Drawing.Color]::FromArgb(254,242,242)) ([System.Drawing.Color]::FromArgb(220,38,38)) 'Failure / Resilience Flow' @('DB failures increment breaker counters','Open circuit: reads return 503','Writes return 202 and enqueue in Redis','Queue worker retries and closes loop on recovery') ([System.Drawing.Color]::FromArgb(127,29,29))

Draw-Arrow 380 200 450 200 ([System.Drawing.Color]::FromArgb(17,24,39))
Draw-Arrow 810 200 900 200 ([System.Drawing.Color]::FromArgb(17,24,39))
Draw-Arrow 1170 320 1050 390 ([System.Drawing.Color]::FromArgb(55,65,81))
Draw-Arrow 1280 320 1370 390 ([System.Drawing.Color]::FromArgb(55,65,81))
Draw-Arrow 1390 320 1670 390 ([System.Drawing.Color]::FromArgb(55,65,81))
Draw-Arrow 1440 200 1520 200 ([System.Drawing.Color]::FromArgb(17,24,39))
Draw-Arrow 1520 220 1390 440 ([System.Drawing.Color]::FromArgb(239,68,68))
Draw-Arrow 1520 220 1080 440 ([System.Drawing.Color]::FromArgb(37,99,235))
Draw-Arrow 1520 220 1730 440 ([System.Drawing.Color]::FromArgb(20,184,166))

$legendPen1 = New-Object System.Drawing.Pen([System.Drawing.Color]::FromArgb(37,99,235),3)
$legendPen2 = New-Object System.Drawing.Pen([System.Drawing.Color]::FromArgb(239,68,68),3)
$legendPen3 = New-Object System.Drawing.Pen([System.Drawing.Color]::FromArgb(20,184,166),3)
$g.DrawLine($legendPen1,84,1190,130,1190)
$g.DrawString('Data/DB flow',$fontSmall,$mutedBrush,140,1182)
$g.DrawLine($legendPen2,300,1190,346,1190)
$g.DrawString('Failure/retry flow',$fontSmall,$mutedBrush,356,1182)
$g.DrawLine($legendPen3,570,1190,616,1190)
$g.DrawString('Storage flow',$fontSmall,$mutedBrush,626,1182)
$legendPen1.Dispose(); $legendPen2.Dispose(); $legendPen3.Dispose()

$outPath = 'c:\Users\HP 745 G6\Documents\api-gateway-test-project\atglance- system-design.png'
$bmp.Save($outPath,[System.Drawing.Imaging.ImageFormat]::Png)

$g.Dispose(); $bmp.Dispose(); $fontTitle.Dispose(); $fontSection.Dispose(); $fontText.Dispose(); $fontSmall.Dispose(); $titleBrush.Dispose(); $mutedBrush.Dispose()
Write-Output "Created: $outPath"

# Configuration Audit Feature - Fix Guide

## Problem Fixed

The audit feature was throwing a JSON parsing error because the error responses weren't being properly handled. When errors occurred (like missing API key or API failures), Laravel was returning HTML error pages instead of JSON responses, causing the frontend to fail with:

```
Error: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
```

## Solution Implemented

### 1. **Enhanced Error Handling in Controller**
- Added check for OpenAI API key at the start of `auditConfiguration()` method
- Returns proper JSON error response (503) if API key is not configured
- Wrapped entire method in try-catch to catch all exceptions
- Added error logging for debugging

### 2. **Improved API Call Error Handling**
- Added validation for JSON response from OpenAI API
- Added logging for invalid responses
- Catches multiple exception types: `RequestException`, `GuzzleException`, and generic `Throwable`
- Provides detailed error messages for each failure type

### 3. **Better Error Messages**
- API key not configured → clear message with instructions
- Network errors → separate handling
- Invalid API response → shows what was received
- All errors return proper JSON with 4xx/5xx status codes

## How to Test

### Prerequisites
1. Ensure `OPENAI_API_KEY` is set in `.env`:
   ```
   OPENAI_API_KEY=sk-your-actual-openai-key
   ```

2. Start the containers:
   ```bash
   docker compose -f docker-compose.yml -f docker-compose-kong.yml up -d
   ```

### Test Steps

1. **Navigate to a configuration file:**
   - Go to `http://localhost:8000/configuration-backups`
   - Click on any configuration file to view it

2. **Click the Audit Configuration button:**
   - You should see a loading spinner
   - Modal will show "Analyzing your configuration with AI..."

3. **Expected Results:**

   **If API key is valid:**
   - Modal shows audit results with 6 sections:
     1. About the Service
     2. About the Configuration
     3. Potential Risk Areas
     4. Current Security Status
     5. Suggested Additional Hardening
     6. Recommended Hardened Override
   - Risk level badge (HIGH, MEDIUM, or LOW)
   - Export button becomes available

   **If API key is missing/invalid:**
   - Error message: "OpenAI API key not configured. Please add OPENAI_API_KEY to your .env file."
   - Check browser console (F12) for detailed error logs

   **If OpenAI API fails:**
   - Error message showing the specific API error
   - Check Docker logs: `docker compose logs api --tail 50`

## Debugging

### View Error Logs
```bash
docker compose exec api tail -100 storage/logs/laravel.log
```

### Check API Key Configuration
```bash
docker compose exec api bash -c "echo $OPENAI_API_KEY"
```

### Clear Views Cache (if needed)
```bash
docker compose exec api php artisan view:clear
```

## API Response Format

### Success Response (200)
```json
{
  "success": true,
  "data": {
    "service_info": "...",
    "config_details": "...",
    "risk_areas": "...",
    "security_status": "...",
    "hardening_suggestions": "...",
    "hardened_override": "..."
  },
  "risk_level": "high|medium|low"
}
```

### Error Response (4xx/5xx)
```json
{
  "success": false,
  "message": "Descriptive error message explaining what went wrong"
}
```

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| "OpenAI API key not configured" | Add valid key to `.env` and restart containers |
| "OpenAI API Error: invalid_api_key" | Verify API key is correct (starts with `sk-`) |
| "Invalid response from OpenAI API" | Check OpenAI API account status and quota limits |
| Blank modal with no results | Check Docker logs for detailed error |
| "Failed to parse ChatGPT response as JSON" | OpenAI API might be returning unexpected format |

## Next Steps

1. Test with different configuration files
2. Monitor logs for any API issues
3. Consider adding rate limiting for API calls
4. Implement audit history/caching if needed

---

For questions or issues, check the Docker logs:
```bash
docker compose logs api -f
```

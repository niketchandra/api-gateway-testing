# AI Settings and Configuration File Auditing Guide

## Overview

The AI Setup feature in AtGlance allows you to configure AI-powered auditing for your configuration files. You can choose to use a Cloud AI provider (ChatGPT, Claude, Gemini, Ollama) or Bring Your Own AI Settings (BYOS) for custom AI servers.

## Quick Start

### 1. Access AI Settings

1. Log in to AtGlance Admin Panel
2. Navigate to **Admin Dashboard → Site Settings**
3. Click the **AI Setup** tab

### 2. Choose Your AI Provider

You have two main options:

#### Option A: Cloud AI Provider

Select one of the following:
- **ChatGPT (OpenAI)** - Most powerful general-purpose model
- **Claude (Anthropic)** - Excellent for detailed analysis
- **Gemini (Google)** - Fast and efficient
- **Ollama** - Self-hosted or local AI model

#### Option B: Bring Your Own AI Settings (BYOS)

Use your own AI server or custom AI configuration with full control over:
- Base URL (your API endpoint)
- Model name (the model identifier)
- Authentication token (API key)
- Max thinking tokens (optional)

## Configuration Steps

### For Cloud AI Providers

#### ChatGPT (OpenAI)

1. Get your API key from [OpenAI API keys](https://platform.openai.com/api-keys)
2. Copy the API key (starts with `sk-`)
3. In AI Setup:
   - Select "Cloud AI Provider"
   - Choose "ChatGPT (OpenAI)"
   - Paste your API key in the **API Key** field
4. Click **Save AI Settings**

**Supported Models:** GPT-4 Turbo

**Cost:** Per-token billing. Check OpenAI pricing.

#### Claude (Anthropic)

1. Get your API key from [Anthropic Console](https://console.anthropic.com/)
2. Copy the API key (starts with `sk-ant-`)
3. In AI Setup:
   - Select "Cloud AI Provider"
   - Choose "Claude (Anthropic)"
   - Paste your API key in the **API Key** field
4. Click **Save AI Settings**

**Supported Models:** Claude 3 Sonnet

**Cost:** Per-token billing. Check Anthropic pricing.

#### Gemini (Google)

1. Get your API key from [Google AI Studio](https://aistudio.google.com/app/apikey)
2. Copy the API key
3. In AI Setup:
   - Select "Cloud AI Provider"
   - Choose "Gemini (Google)"
   - Paste your API key in the **API Key** field
4. Click **Save AI Settings**

**Supported Models:** Gemini Pro

**Cost:** Generous free tier available. Check Google pricing.

#### Ollama (Local/Self-Hosted)

Ollama allows you to run AI models locally without cloud dependencies.

1. **Install Ollama:**
   - Download from [ollama.ai](https://ollama.ai)
   - Follow installation instructions for your OS

2. **Pull a Model:**
   ```bash
   ollama pull mistral      # Lightweight, fast
   ollama pull neural-chat  # Balanced
   ollama pull llama2       # Larger, more capable
   ```

3. **Start Ollama:**
   ```bash
   ollama serve  # Typically runs on http://localhost:11434
   ```

4. **In AtGlance AI Setup:**
   - Select "Cloud AI Provider"
   - Choose "Ollama (Local/Self-Hosted)"
   - Enter Base URL: `http://localhost:11434` (or your server's URL)
   - Enter Model: `mistral` (or your chosen model)
   - No API key needed for local instances
5. Click **Save AI Settings**

**Available Models:** mistral, neural-chat, llama2, dolphin, orca, and more

**Cost:** Free (runs locally)

### For BYOS (Bring Your Own AI Settings)

Use this for custom AI servers, private deployments, or enterprise AI platforms.

1. **Enable BYOS:**
   - Select "Bring Your Own AI Settings (BYOS)"
   - Check the "Enable BYOS Configuration" checkbox

2. **Configure Your AI Server:**
   - **Base URL**: Your AI service endpoint (e.g., `https://api.yourdomain.com`)
   - **Model Name**: The model identifier for your service (e.g., `custom-model-v1`)
   - **Auth Token**: Your API key or authentication token (optional but recommended)
   - **Max Thinking Tokens**: For models that support extended thinking (optional)

3. **Example Configurations:**

   **Private Claude Server:**
   ```
   Base URL: https://your-claude-proxy.example.com
   Model: claude-3-sonnet-20240229
   Auth Token: your-auth-key
   Max Thinking Tokens: 5000
   ```

   **Enterprise Custom LLM:**
   ```
   Base URL: https://llm.internal.company.com
   Model: enterprise-audit-v2
   Auth Token: your-enterprise-api-key
   Max Thinking Tokens: 10000
   ```

4. Click **Save AI Settings**

## Configuration File Auditing

Once you've configured an AI provider, the system will use it to automatically audit your configuration files for:

### What Gets Audited

✅ **Security Vulnerabilities**
- Exposed credentials or secrets
- Weak security settings
- Missing authentication/authorization
- Insecure protocols or encryption

✅ **Compliance Issues**
- GDPR, HIPAA, SOC2 requirements
- Industry-specific standards
- Data retention policies
- Access control compliance

✅ **Best Practices**
- Configuration standards
- Performance optimization opportunities
- Scalability concerns
- Maintainability improvements

✅ **Risk Assessment**
- Critical, High, Medium, Low severity issues
- Recommended remediation steps

### How to Use Auditing

1. **Upload/View Configuration Files:**
   - Go to User Dashboard → Configuration Backups
   - Upload a new configuration file or select an existing one

2. **Trigger Audit (Coming Soon):**
   - View configuration file details
   - Click "Audit with AI" button
   - The system will use your configured AI provider to analyze the file

3. **Review Audit Results:**
   - Security findings
   - Compliance issues
   - Best practice recommendations
   - Severity levels and remediation steps

## Security Considerations

### API Key Safety

✅ **All API Keys Are Encrypted**
- API keys are encrypted before being stored in the database
- Encrypted keys are only decrypted in memory when needed
- Keys are never logged or exposed

⚠️ **Best Practices**

1. **For Cloud Providers:**
   - Use strong, unique API keys
   - Rotate API keys periodically
   - Never share API keys
   - Monitor API usage for unusual activity

2. **For BYOS:**
   - Use HTTPS with valid SSL certificates
   - Implement IP whitelisting if possible
   - Use strong authentication tokens
   - Consider network isolation

3. **General Security:**
   - Limit AI auditing scope to necessary files
   - Review audit results carefully before following recommendations
   - Keep audit logs for compliance
   - Regularly review AI provider access logs

## Troubleshooting

### "Configuration not valid" Error

**Cause:** AI settings are incomplete or incorrect

**Solution:**
1. Go to AI Setup tab
2. Verify all required fields are filled
3. For Cloud providers, ensure API key is valid
4. For BYOS, test connectivity to Base URL
5. Save settings again

### API Connection Failed

**For Cloud Providers:**
- Check internet connectivity
- Verify API key is correct
- Check API provider's status page
- Ensure account has sufficient credits

**For Ollama:**
- Verify Ollama is running: `ollama list`
- Test endpoint: `curl http://localhost:11434/api/tags`
- Check Ollama logs

**For BYOS:**
- Verify Base URL is accessible
- Test authentication: `curl -H "Authorization: Bearer YOUR_TOKEN" https://your-url/test`
- Check server logs

### Audit Results Not Appearing

1. Ensure AI provider is configured
2. Check configuration file has content
3. Verify AI service is responsive
4. Check application logs for errors

## Supported AI Models

### OpenAI (ChatGPT)
- GPT-4 Turbo
- GPT-4
- GPT-3.5 Turbo

### Anthropic (Claude)
- Claude 3 Sonnet
- Claude 3 Opus (larger contexts)
- Claude 3 Haiku (faster, cheaper)

### Google (Gemini)
- Gemini Pro

### Ollama Models
- mistral
- neural-chat
- llama2
- dolphin
- orca
- openchat
- starling-lm

## API Integration (For Developers)

### Using the Audit Service

```php
use App\Services\ConfigAuditHelper;

$auditHelper = new ConfigAuditHelper();

// Check if auditing is configured
if ($auditHelper->isAuditingConfigured()) {
    // Audit a single configuration file
    $configFile = ConfigurationFile::find($fileId);
    $auditResult = $auditHelper->auditConfigurationFile($configFile);
    
    // Use audit results
    if ($auditResult) {
        $severity = $auditResult['issues_found']; // ['critical' => 2, 'high' => 5, ...]
        $auditText = $auditResult['audit_results']; // Full audit report
    }
}
```

## Pricing Comparison

| Provider | Model | Cost | Speed | Local |
|----------|-------|------|-------|-------|
| ChatGPT | GPT-4 Turbo | Per-token (high) | Fast | ❌ |
| Claude | Claude 3 | Per-token (medium) | Medium | ❌ |
| Gemini | Gemini Pro | Free (generous) | Fast | ❌ |
| Ollama | Various | Free | Depends on HW | ✅ |

## Best Practices

1. **Start with Local (Ollama)**
   - Best for testing and development
   - No API costs
   - Full privacy

2. **Use Gemini for Free**
   - Generous free tier for evaluation
   - No credit card required
   - Good performance

3. **Choose Based on Needs**
   - Security-critical: Claude or Anthropic
   - Speed-critical: Gemini
   - Cost-critical: Ollama (local)
   - Enterprise: BYOS with private server

4. **Regular Audits**
   - Schedule regular audits of critical configs
   - Compare results across multiple AI providers for consensus
   - Track changes over time

## Support and Issues

For issues or questions:
1. Check the Troubleshooting section above
2. Review application logs: `storage/logs/laravel.log`
3. Check AI provider's status page
4. Contact your AtGlance administrator

---

**Last Updated:** 2026-05-07

For the latest updates and features, visit the AtGlance documentation.

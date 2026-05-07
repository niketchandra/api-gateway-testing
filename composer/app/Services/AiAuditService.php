<?php

namespace App\Services;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAuditService
{
    private ?string $providerType = null;
    private ?string $provider = null;
    private array $apiKeys = [];
    private string $byosBaseUrl = '';
    private string $byosModel = '';
    private ?string $byosAuthToken = null;
    private int $byosMaxThinkingTokens = 0;
    private string $ollamaBaseUrl = '';
    private string $ollamaModel = '';

    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * Load AI settings from database
     */
    private function loadSettings(): void
    {
        $this->providerType = AdminSetting::getValue('ai_provider_type', 'cloud');
        $this->provider = AdminSetting::getValue('ai_provider', 'chatgpt');

        // Load API keys
        $apiKeysValue = AdminSetting::getValue('ai_api_keys', null);
        if ($apiKeysValue !== null) {
            if (is_array($apiKeysValue)) {
                $this->apiKeys = $apiKeysValue;
            } else {
                $decoded = json_decode((string) $apiKeysValue, true);
                $this->apiKeys = is_array($decoded) ? $decoded : [];
            }
        }

        // Load BYOS settings
        $this->byosBaseUrl = (string) AdminSetting::getValue('byos_base_url', '');
        $this->byosModel = (string) AdminSetting::getValue('byos_model', '');
        $this->byosAuthToken = AdminSetting::getValue('byos_auth_token', null);
        $this->byosMaxThinkingTokens = (int) AdminSetting::getValue('byos_max_thinking_tokens', 0);

        // Load Ollama settings
        $this->ollamaBaseUrl = (string) AdminSetting::getValue('ai_ollama_base_url', '');
        $this->ollamaModel = (string) AdminSetting::getValue('ai_ollama_model', '');
    }

    /**
     * Check if AI settings are properly configured
     */
    public function isConfigured(): bool
    {
        if ($this->providerType === 'byos') {
            return $this->byosBaseUrl !== '' && $this->byosModel !== '';
        }

        // Cloud provider validation
        if ($this->provider === 'ollama') {
            return $this->ollamaBaseUrl !== '' && $this->ollamaModel !== '';
        }

        return isset($this->apiKeys[$this->provider]) && !empty($this->apiKeys[$this->provider]);
    }

    /**
     * Get configuration status for display
     */
    public function getConfigurationStatus(): array
    {
        return [
            'is_configured' => $this->isConfigured(),
            'provider_type' => $this->providerType,
            'provider' => $this->provider,
            'has_api_key' => $this->providerType === 'cloud' && isset($this->apiKeys[$this->provider]),
            'byos_enabled' => $this->providerType === 'byos',
        ];
    }

    /**
     * Audit a configuration file
     *
     * @param string $configContent The configuration file content
     * @param string $configName The name/path of the configuration file (optional)
     * @return array|null Array with audit results or null if not configured
     */
    public function auditConfigFile(string $configContent, string $configName = ''): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            if ($this->providerType === 'byos') {
                return $this->auditWithByos($configContent, $configName);
            }

            return match ($this->provider) {
                'chatgpt' => $this->auditWithChatGpt($configContent, $configName),
                'claude' => $this->auditWithClaude($configContent, $configName),
                'gemini' => $this->auditWithGemini($configContent, $configName),
                'ollama' => $this->auditWithOllama($configContent, $configName),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::error('AI Audit Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    /**
     * Audit using BYOS (Bring Your Own AI Settings)
     */
    private function auditWithByos(string $configContent, string $configName): ?array
    {
        $systemPrompt = $this->buildAuditPrompt($configName);
        
        $payload = [
            'model' => $this->byosModel,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => "Please audit the following configuration file:\n\n```\n{$configContent}\n```",
                ]
            ],
            'temperature' => 0.7,
        ];

        if ($this->byosMaxThinkingTokens > 0) {
            $payload['max_thinking_tokens'] = $this->byosMaxThinkingTokens;
        }

        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($this->byosAuthToken !== null && !empty($this->byosAuthToken)) {
            $headers['Authorization'] = 'Bearer ' . $this->byosAuthToken;
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders($headers)
                ->post($this->byosBaseUrl . '/v1/chat/completions', $payload);

            if (!$response->successful()) {
                Log::warning('BYOS Audit Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            $auditText = $data['choices'][0]['message']['content'] ?? '';

            return $this->parseAuditResponse($auditText);
        } catch (\Throwable $e) {
            Log::error('BYOS Audit Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Audit using ChatGPT
     */
    private function auditWithChatGpt(string $configContent, string $configName): ?array
    {
        $apiKey = $this->apiKeys['chatgpt'] ?? null;
        if (empty($apiKey)) {
            return null;
        }

        $systemPrompt = $this->buildAuditPrompt($configName);

        $payload = [
            'model' => 'gpt-4-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => "Please audit the following configuration file:\n\n```\n{$configContent}\n```",
                ]
            ],
            'temperature' => 0.7,
        ];

        try {
            $response = Http::timeout(60)
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', $payload);

            if (!$response->successful()) {
                Log::warning('ChatGPT Audit Failed', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();
            $auditText = $data['choices'][0]['message']['content'] ?? '';

            return $this->parseAuditResponse($auditText);
        } catch (\Throwable $e) {
            Log::error('ChatGPT Audit Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Audit using Claude
     */
    private function auditWithClaude(string $configContent, string $configName): ?array
    {
        $apiKey = $this->apiKeys['claude'] ?? null;
        if (empty($apiKey)) {
            return null;
        }

        $systemPrompt = $this->buildAuditPrompt($configName);

        $payload = [
            'model' => 'claude-3-sonnet-20240229',
            'max_tokens' => 2000,
            'system' => $systemPrompt,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Please audit the following configuration file:\n\n```\n{$configContent}\n```",
                ]
            ],
        ];

        if ($this->byosMaxThinkingTokens > 0) {
            $payload['thinking'] = [
                'type' => 'enabled',
                'budget_tokens' => $this->byosMaxThinkingTokens,
            ];
        }

        try {
            $response = Http::timeout(60)
                ->withHeader('x-api-key', $apiKey)
                ->withHeader('anthropic-version', '2023-06-01')
                ->post('https://api.anthropic.com/v1/messages', $payload);

            if (!$response->successful()) {
                Log::warning('Claude Audit Failed', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();
            $content = $data['content'] ?? [];
            $auditText = '';

            foreach ($content as $block) {
                if ($block['type'] === 'text') {
                    $auditText .= $block['text'];
                }
            }

            return $this->parseAuditResponse($auditText);
        } catch (\Throwable $e) {
            Log::error('Claude Audit Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Audit using Gemini
     */
    private function auditWithGemini(string $configContent, string $configName): ?array
    {
        $apiKey = $this->apiKeys['gemini'] ?? null;
        if (empty($apiKey)) {
            return null;
        }

        $systemPrompt = $this->buildAuditPrompt($configName);

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => "You are a configuration file security and compliance auditor. {$systemPrompt}\n\nPlease audit the following configuration file:\n\n```\n{$configContent}\n```",
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 2000,
            ],
        ];

        try {
            $response = Http::timeout(60)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}", $payload);

            if (!$response->successful()) {
                Log::warning('Gemini Audit Failed', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();
            $candidates = $data['candidates'] ?? [];
            $auditText = '';

            if (!empty($candidates)) {
                $content = $candidates[0]['content']['parts'] ?? [];
                foreach ($content as $part) {
                    if (isset($part['text'])) {
                        $auditText .= $part['text'];
                    }
                }
            }

            return $this->parseAuditResponse($auditText);
        } catch (\Throwable $e) {
            Log::error('Gemini Audit Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Audit using Ollama
     */
    private function auditWithOllama(string $configContent, string $configName): ?array
    {
        if (empty($this->ollamaBaseUrl) || empty($this->ollamaModel)) {
            return null;
        }

        $systemPrompt = $this->buildAuditPrompt($configName);

        $payload = [
            'model' => $this->ollamaModel,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => "Please audit the following configuration file:\n\n```\n{$configContent}\n```",
                ]
            ],
            'temperature' => 0.7,
            'stream' => false,
        ];

        try {
            $response = Http::timeout(120)
                ->post($this->ollamaBaseUrl . '/api/chat', $payload);

            if (!$response->successful()) {
                Log::warning('Ollama Audit Failed', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();
            $auditText = $data['message']['content'] ?? '';

            return $this->parseAuditResponse($auditText);
        } catch (\Throwable $e) {
            Log::error('Ollama Audit Exception: ' . $e->getMessage());
            
            // Return demo/test audit results for development purposes
            return $this->getDemoAuditResults($configName, $configContent);
        }
    }

    /**
     * Get demo audit results for testing when real AI is unavailable
     */
    private function getDemoAuditResults(string $configName = '', string $configContent = ''): ?array
    {
        // Simple heuristic checks for demo mode
        $issues = [];
        
        if (strpos($configContent, 'password') !== false) {
            $issues['security'] = 1;
        }
        if (strpos($configContent, 'DEBUG') !== false || strpos($configContent, 'debug') !== false) {
            $issues['debug'] = 1;
        }
        if (strlen($configContent) > 10000) {
            $issues['size'] = 1;
        }

        $auditText = <<<'AUDIT'
**DEMO MODE**: AI service unavailable. Basic analysis below:

SECURITY FINDINGS:
- Configuration appears to have database credentials or sensitive data
- Review all hardcoded passwords and API keys
- Ensure sensitive configuration is externalized to environment variables

BEST PRACTICES:
- Use configuration management tools (Ansible, Docker Compose, etc.)
- Implement configuration versioning and rollback capability
- Enable audit logging for configuration changes
- Use feature flags for safe deployments

RECOMMENDATIONS:
- Migrate sensitive data to secure vaults (AWS Secrets Manager, HashiCorp Vault)
- Implement configuration validation and schema validation
- Regular security audits and compliance checks
- Document all configuration parameters and their purposes

Note: This is a demo analysis. Connect a real AI provider (Ollama, OpenAI, etc.) for comprehensive auditing.
AUDIT;

        return [
            'provider' => 'demo',
            'provider_type' => 'test',
            'audit_results' => $auditText,
            'issues_found' => $issues,
            'is_demo' => true,
        ];
    }

    /**
     * Build the system prompt for auditing
     */
    private function buildAuditPrompt(string $configName = ''): string
    {
        $fileInfo = $configName ? " File: {$configName}" : '';

        return <<<'PROMPT'
You are an expert configuration file security and compliance auditor. Your task is to analyze configuration files and provide detailed audit feedback.

When reviewing a configuration file, evaluate it for:

1. **Security Vulnerabilities**
   - Exposed credentials or secrets
   - Weak security settings
   - Missing authentication/authorization
   - Insecure protocols or encryption

2. **Compliance Issues**
   - GDPR, HIPAA, SOC2 requirements
   - Industry-specific standards
   - Data retention policies
   - Access control compliance

3. **Best Practices**
   - Configuration standards
   - Performance optimization opportunities
   - Scalability concerns
   - Maintainability improvements

4. **Risk Assessment**
   - Critical, High, Medium, Low severity issues
   - Recommended remediation steps

Provide your audit in a clear, structured format with:
- Summary of findings
- Categorized list of issues
- Severity levels
- Recommended fixes
- General feedback and improvements

PROMPT . $fileInfo;
    }

    /**
     * Parse AI response and structure audit results
     */
    private function parseAuditResponse(string $auditText): array
    {
        return [
            'status' => 'completed',
            'audit_date' => now()->toDateTimeString(),
            'provider' => $this->provider,
            'provider_type' => $this->providerType,
            'audit_results' => $auditText,
            'issues_found' => $this->countIssuesSeverity($auditText),
        ];
    }

    /**
     * Count issue severities in the audit text
     */
    private function countIssuesSeverity(string $text): array
    {
        $severities = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];

        $lowerText = strtolower($text);

        $severities['critical'] = substr_count($lowerText, 'critical');
        $severities['high'] = substr_count($lowerText, 'high') - $severities['critical'];
        $severities['medium'] = substr_count($lowerText, 'medium');
        $severities['low'] = substr_count($lowerText, 'low') - $severities['medium'];

        return $severities;
    }
}

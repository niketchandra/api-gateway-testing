<?php

namespace App\Services;

use App\Models\ConfigurationFile;
use App\Models\RawData;
use Illuminate\Support\Facades\Log;

class ConfigAuditHelper
{
    private AiAuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AiAuditService();
    }

    /**
     * Audit a configuration file
     */
    public function auditConfigurationFile(ConfigurationFile $configFile): ?array
    {
        try {
            // Get the raw content
            $rawData = $configFile->rawData;
            if (!$rawData || empty($rawData->file_data)) {
                return null;
            }

            $auditResult = $this->auditService->auditConfigFile(
                $rawData->file_data,
                $configFile->file_name ?? 'config'
            );

            if ($auditResult) {
                // Store audit result in metadata
                $metadata = json_decode((string) ($configFile->metadata ?? '{}'), true) ?? [];
                $metadata['last_audit'] = [
                    'date' => now()->toDateTimeString(),
                    'provider' => $auditResult['provider'] ?? '',
                    'provider_type' => $auditResult['provider_type'] ?? '',
                    'issues_count' => array_sum($auditResult['issues_found'] ?? []),
                ];
                $configFile->metadata = json_encode($metadata);
                $configFile->save();
            }

            return $auditResult;
        } catch (\Throwable $e) {
            Log::error('Configuration File Audit Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Audit multiple configuration files
     */
    public function auditMultipleFiles(array $configFileIds): array
    {
        $results = [];

        foreach ($configFileIds as $fileId) {
            $configFile = ConfigurationFile::find($fileId);
            if ($configFile) {
                $auditResult = $this->auditConfigurationFile($configFile);
                $results[$fileId] = $auditResult;
            }
        }

        return $results;
    }

    /**
     * Audit all active configuration files
     */
    public function auditAllActiveFiles(): array
    {
        $results = [];
        $configFiles = ConfigurationFile::where('is_active', true)->get();

        foreach ($configFiles as $configFile) {
            $auditResult = $this->auditConfigurationFile($configFile);
            if ($auditResult) {
                $results[$configFile->id] = $auditResult;
            }
        }

        return $results;
    }

    /**
     * Check if AI is configured for auditing
     */
    public function isAuditingConfigured(): bool
    {
        return $this->auditService->isConfigured();
    }

    /**
     * Get AI configuration status
     */
    public function getConfigurationStatus(): array
    {
        return $this->auditService->getConfigurationStatus();
    }

    /**
     * Get audit results for a config file
     */
    public function getAuditResults(ConfigurationFile $configFile): ?array
    {
        $metadata = json_decode((string) ($configFile->metadata ?? '{}'), true) ?? [];
        return $metadata['last_audit'] ?? null;
    }
}

@extends('app')

@section('title', 'View Configuration - AtGlance')

@section('dashboard-content')
<div style="padding: 40px;">
    <style>
        .config-shell {
            background: var(--color-white);
            border: 1px solid var(--color-border);
            border-radius: 14px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .config-header {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            padding: 24px;
            color: white;
        }

        .config-pre {
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--color-border);
            overflow-x: auto;
            max-height: 600px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            color: #334155;
        }
    </style>
    <div style="margin-bottom: 30px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1 style="font-size: 28px; font-weight: bold; color: #333; margin-bottom: 8px;">Configuration Details</h1>
                <p style="color: #666; font-size: 14px;">Viewing: {{ $config->file_name }}</p>
            </div>
            <a href="{{ route('configuration-backups') }}" class="btn-save" style="padding: 10px 20px; border-radius: 10px; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="config-shell">
        <!-- File Info Header -->
        <div class="config-header">
            <h2 style="font-size: 20px; font-weight: bold; margin-bottom: 16px;">
                <i class="fas fa-file-code"></i> {{ $config->file_name }}
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div>
                    <div style="font-size: 11px; opacity: 0.8; margin-bottom: 4px;">Config ID</div>
                    <div style="font-size: 15px; font-weight: 600;">#{{ $config->id }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; opacity: 0.8; margin-bottom: 4px;">Service Name</div>
                    <div style="font-size: 15px; font-weight: 600;">{{ $config->service_name ?? 'N/A' }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; opacity: 0.8; margin-bottom: 4px;">Status</div>
                    <div style="font-size: 15px; font-weight: 600;">{{ ucfirst($config->status) }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; opacity: 0.8; margin-bottom: 4px;">Created At</div>
                    <div style="font-size: 15px; font-weight: 600;">{{ \Carbon\Carbon::parse($config->created_at)->format('M d, Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Configuration Content -->
        <div style="padding: 24px;">
            <h3 style="font-size: 16px; font-weight: bold; color: #333; margin-bottom: 16px;">
                <i class="fas fa-code"></i> Configuration Content
            </h3>
            @if($config->data)
                <pre class="config-pre">{{ $config->data }}</pre>
            @else
                <div style="background: #fff3e0; padding: 16px; border-radius: 8px; border-left: 4px solid #ff9800; color: #e65100;">
                    <i class="fas fa-exclamation-triangle"></i> No configuration data available for this file.
                </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div style="padding: 0 24px 24px;">
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="{{ route('configuration-backups.download', $config->id) }}" class="btn-save"
                   style="padding: 12px 24px; border-radius: 10px; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-download"></i> Download Configuration
                </a>
                <button onclick="auditConfiguration('{{ $config->id }}')"
                        id="audit-btn"
                        class="btn-secondary"
                        style="padding: 12px 24px; border-radius: 10px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%); color: white; border: none; cursor: pointer; transition: all 0.3s ease;">
                    <i class="fas fa-shield-alt"></i> <span id="audit-btn-text">Audit Configuration</span>
                </button>
                @if($config->validation_hash)
                    <button onclick="alert('Validation Hash:\n{{ $config->validation_hash }}')" 
                            class="btn-secondary"
                            style="padding: 12px 24px; border-radius: 10px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-fingerprint"></i> View Hash
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Audit Results Modal -->
    <div id="audit-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 14px; max-width: 900px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px rgba(0, 0, 0, 0.2);">
            <!-- Modal Header -->
            <div style="background: linear-gradient(135deg, #1f2937 0%, #374151 100%); padding: 24px; color: white; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-shield-alt" style="font-size: 20px;"></i>
                    <h2 style="font-size: 18px; font-weight: bold; margin: 0;">Configuration Audit Analysis</h2>
                </div>
                <button onclick="closeAuditModal()" style="background: none; border: none; color: white; font-size: 24px; cursor: pointer; opacity: 0.8; hover: opacity: 1;">×</button>
            </div>

            <!-- Modal Body -->
            <div style="padding: 24px; display: none;" id="audit-results">
                <div id="audit-content"></div>
            </div>

            <!-- Loading State -->
            <div id="audit-loading" style="padding: 40px; text-align: center;">
                <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #e5e7eb; border-top: 4px solid #38bdf8; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 16px; color: #666; font-size: 14px;">Analyzing your configuration with AI...</p>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 16px 24px; background: #f7fafc; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 12px;">
                <button onclick="closeAuditModal()" style="padding: 10px 20px; border-radius: 8px; background: #e5e7eb; color: #333; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">Close</button>
                <button onclick="exportAuditResults()" style="padding: 10px 20px; border-radius: 8px; background: #38bdf8; color: white; border: none; cursor: pointer; font-size: 14px; font-weight: 500; display: none;" id="export-btn">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- CSS Animation -->
    <style>
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .audit-section {
            margin-bottom: 20px;
            padding: 16px;
            background: #f7fafc;
            border-radius: 10px;
            border-left: 4px solid #38bdf8;
        }
        .audit-section-title {
            font-size: 14px;
            font-weight: bold;
            color: #38bdf8;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .audit-section-content {
            font-size: 14px;
            line-height: 1.6;
            color: #334155;
        }
        .risk-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 8px;
        }
        .risk-high {
            background: #fee2e2;
            color: #991b1b;
        }
        .risk-medium {
            background: #fef3c7;
            color: #92400e;
        }
        .risk-low {
            background: #dcfce7;
            color: #166534;
        }
    </style>

    <!-- JavaScript Functions -->
    <script>
        function auditConfiguration(configId) {
            const btn = document.getElementById('audit-btn');
            const btnText = document.getElementById('audit-btn-text');
            const modal = document.getElementById('audit-modal');
            const loading = document.getElementById('audit-loading');
            const results = document.getElementById('audit-results');

            // Show modal and loading state
            modal.style.display = 'flex';
            loading.style.display = 'block';
            results.style.display = 'none';
            btn.disabled = true;
            btnText.textContent = 'Analyzing...';

            // Send audit request
            fetch(`/configuration-backups/${configId}/audit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Hide loading, show results
                loading.style.display = 'none';
                results.style.display = 'block';
                
                let html = '';
                
                if (data.success && data.data) {
                    const analysis = data.data;
                    
                    // About the Service
                    if (analysis.service_info) {
                        html += `<div class="audit-section">
                            <div class="audit-section-title">About the Service</div>
                            <div class="audit-section-content">${escapeHtml(analysis.service_info)}</div>
                        </div>`;
                    }
                    
                    // About the Configuration
                    if (analysis.config_details) {
                        html += `<div class="audit-section">
                            <div class="audit-section-title">About the Configuration</div>
                            <div class="audit-section-content">${escapeHtml(analysis.config_details)}</div>
                        </div>`;
                    }
                    
                    // Potential Risk Areas
                    if (analysis.risk_areas) {
                        html += `<div class="audit-section">
                            <div class="audit-section-title">Potential Risk Areas</div>
                            <div class="audit-section-content">${escapeHtml(analysis.risk_areas)}</div>
                            ${data.risk_level ? `<span class="risk-badge risk-${data.risk_level}">${data.risk_level.toUpperCase()} RISK</span>` : ''}
                        </div>`;
                    }
                    
                    // Current Security Status
                    if (analysis.security_status) {
                        html += `<div class="audit-section">
                            <div class="audit-section-title">Current Security Status</div>
                            <div class="audit-section-content">${escapeHtml(analysis.security_status)}</div>
                        </div>`;
                    }
                    
                    // Suggested Additional Hardening
                    if (analysis.hardening_suggestions) {
                        html += `<div class="audit-section">
                            <div class="audit-section-title">Suggested Additional Hardening</div>
                            <div class="audit-section-content">${escapeHtml(analysis.hardening_suggestions)}</div>
                        </div>`;
                    }
                    
                    // Recommended Hardened Override
                    if (analysis.hardened_override) {
                        html += `<div class="audit-section">
                            <div class="audit-section-title">Recommended Hardened Override</div>
                            <div class="audit-section-content"><pre style="background: #1f2937; color: #10b981; padding: 12px; border-radius: 8px; overflow-x: auto; font-size: 12px;">${escapeHtml(analysis.hardened_override)}</pre></div>
                        </div>`;
                    }
                } else {
                    html = `<div class="audit-section" style="background: #fee2e2; border-left-color: #dc2626;">
                        <div class="audit-section-title" style="color: #dc2626;">Error</div>
                        <div class="audit-section-content">${escapeHtml(data.message || 'Failed to analyze configuration. Please try again.')}</div>
                    </div>`;
                }
                
                document.getElementById('audit-content').innerHTML = html;
                document.getElementById('export-btn').style.display = 'inline-block';
                btn.disabled = false;
                btnText.textContent = 'Audit Configuration';
            })
            .catch(error => {
                loading.style.display = 'none';
                results.style.display = 'block';
                document.getElementById('audit-content').innerHTML = `<div class="audit-section" style="background: #fee2e2; border-left-color: #dc2626;">
                    <div class="audit-section-title" style="color: #dc2626;">Error</div>
                    <div class="audit-section-content">${escapeHtml('An error occurred: ' + error.message)}</div>
                </div>`;
                btn.disabled = false;
                btnText.textContent = 'Audit Configuration';
            });
        }

        function closeAuditModal() {
            document.getElementById('audit-modal').style.display = 'none';
        }

        function exportAuditResults() {
            const content = document.getElementById('audit-content').innerText;
            const element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
            element.setAttribute('download', 'audit-report-' + new Date().toISOString().split('T')[0] + '.txt');
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Close modal when clicking outside
        document.getElementById('audit-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAuditModal();
            }
        });
    </script>
</div>
@endsection

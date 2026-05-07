<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AtGlance - Configuration Backup Service')</title>
    <link rel="icon" type="image/x-icon" href="{{ $siteFaviconUrl ?? asset('branding/favicon.ico') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/modern-design-system.css') }}" rel="stylesheet">
    <style>
        /* ============================================================================
           MODERN DESIGN SYSTEM - AtGlance
           Ultra Clean • Minimal • Enterprise-grade
           ============================================================================ */

        /* Color Palette */
        :root {
            --color-bg: #F7FAFC;
            --color-sidebar: #1F2937;
            --color-sidebar-light: #374151;
            --color-accent-blue: #38BDF8;
            --color-accent-cyan: #67E8F9;
            --color-text-dark: #111827;
            --color-text-light: #6B7280;
            --color-border: #E5E7EB;
            --color-white: #FFFFFF;
            --color-success: #10B981;
            --color-warning: #F59E0B;
            --color-error: #EF4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: var(--color-bg);
            color: var(--color-text-dark);
            line-height: 1.6;
        }

        /* ============================================================================
           LAYOUT STRUCTURE
           ============================================================================ */

        .main-container {
            display: flex;
            align-items: stretch;
            min-height: 100vh;
            background: var(--color-bg);
        }

        .sidebar {
            width: 20%;
            min-width: 280px;
            padding: 48px 32px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--color-sidebar) 0%, #2D3748 100%);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 3px;
        }

        .content {
            flex: 1;
            padding: 0;
            min-height: 100vh;
            background: var(--color-bg);
            display: flex;
            flex-direction: column;
        }

        /* ============================================================================
           SIDEBAR COMPONENTS
           ============================================================================ */

        .sidebar-logo {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 48px;
            text-align: center;
            padding: 0;
        }

        .sidebar-logo img {
            max-width: 100%;
            max-height: 80px;
            object-fit: contain;
            filter: brightness(1.2) contrast(1.1);
        }

        .form-container {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ============================================================================
           TABS & NAVIGATION
           ============================================================================ */

        .tab-buttons.auth-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 28px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 16px;
        }

        .tab-buttons.auth-tabs .tab-btn {
            padding: 10px 16px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
            border-bottom: 2px solid transparent;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 0;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .tab-buttons.auth-tabs .tab-btn:hover {
            color: rgba(255, 255, 255, 0.9);
        }

        .tab-buttons.auth-tabs .tab-btn.active {
            color: var(--color-accent-cyan);
            border-bottom-color: var(--color-accent-cyan);
        }

        .tab-buttons.auth-tabs .tab-btn:disabled {
            color: rgba(255, 255, 255, 0.3);
            cursor: not-allowed;
            opacity: 0.5;
        }

        /* Global tabs for dashboard/admin pages */
        .tab-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--color-border);
            padding-bottom: 10px;
        }

        .tab-btn,
        .settings-tab,
        .settings-tab-btn {
            padding: 10px 16px;
            background: var(--color-white);
            border: 1px solid var(--color-border);
            border-radius: 12px;
            color: var(--color-text-light);
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.2px;
            transition: all var(--transition-base);
        }

        .tab-btn:hover,
        .settings-tab:hover,
        .settings-tab-btn:hover {
            color: var(--color-text-dark);
            border-color: var(--color-accent-blue);
            box-shadow: var(--shadow-sm);
            transform: translateY(-1px);
        }

        .tab-btn.active,
        .settings-tab.active,
        .settings-tab-btn.active {
            color: var(--color-white);
            background: linear-gradient(135deg, var(--color-accent-blue) 0%, var(--color-accent-cyan) 100%);
            border-color: transparent;
            box-shadow: 0 8px 20px rgba(56, 189, 248, 0.25);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============================================================================
           FORMS & INPUTS
           ============================================================================ */

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 14px;
            border: 1px solid var(--color-border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', inherit;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--color-white);
            color: var(--color-text-dark);
        }

        .form-group input::placeholder,
        .form-group select::placeholder,
        .form-group textarea::placeholder {
            color: var(--color-text-light);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--color-accent-blue);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
            background: var(--color-white);
        }

        /* ============================================================================
           BUTTONS - Modern Pill Style
           ============================================================================ */

        .btn {
            padding: 11px 24px;
            border: none;
            border-radius: 24px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:active::before {
            width: 300px;
            height: 300px;
        }

        /* Primary Button */
        .btn-primary {
            background: linear-gradient(135deg, var(--color-accent-blue) 0%, var(--color-accent-cyan) 100%);
            color: var(--color-white);
            box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(56, 189, 248, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Secondary Button */
        .btn-secondary {
            background: rgba(255, 255, 255, 0.15);
            color: var(--color-white);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Danger Button */
        .btn-danger {
            background: var(--color-error);
            color: var(--color-white);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.4);
        }

        /* SSO Buttons */
        .sso-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-top: 16px;
        }

        .btn-sso {
            text-decoration: none;
            text-transform: none;
            letter-spacing: normal;
            font-weight: 500;
        }

        /* Global button reset */
        button,
        .action-btn,
        .action-btn-primary,
        .action-btn-secondary,
        .filter-btn,
        .settings-tab,
        .settings-tab-btn,
        .profile-btn,
        .admin-action-btn,
        .admin-user-btn,
        .quick-action-btn,
        .user-profile-btn,
        .btn-save {
            position: relative;
            border-radius: 24px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-save,
        .quick-action-btn,
        .admin-action-btn,
        .admin-user-btn,
        .user-profile-btn,
        .action-btn-primary {
            background: linear-gradient(135deg, var(--color-accent-blue) 0%, var(--color-accent-cyan) 100%);
            color: var(--color-white);
            border: none;
            box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);
        }

        .btn-save:hover,
        .quick-action-btn:hover,
        .admin-action-btn:hover,
        .admin-user-btn:hover,
        .user-profile-btn:hover,
        .action-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(56, 189, 248, 0.35);
        }

        .btn-secondary,
        .action-btn-secondary,
        .filter-btn,
        .profile-btn {
            background: var(--color-white);
            color: var(--color-text-dark);
            border: 1px solid var(--color-border);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary:hover,
        .action-btn-secondary:hover,
        .filter-btn:hover,
        .profile-btn:hover {
            border-color: var(--color-accent-blue);
            color: var(--color-accent-blue);
            transform: translateY(-1px);
        }

        .filter-btn.active {
            color: var(--color-white);
            background: linear-gradient(135deg, var(--color-accent-blue) 0%, var(--color-accent-cyan) 100%);
            border-color: transparent;
            box-shadow: 0 6px 16px rgba(56, 189, 248, 0.28);
        }

        /* ============================================================================
           ALERTS & MESSAGES
           ============================================================================ */

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            font-size: 14px;
            border: 1px solid;
            animation: slideDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.08);
            color: var(--color-text-dark);
            border-color: rgba(16, 185, 129, 0.2);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.08);
            color: var(--color-error);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.08);
            color: var(--color-warning);
            border-color: rgba(245, 158, 11, 0.2);
        }

        .alert i {
            font-size: 18px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        /* ============================================================================
           UTILITY COMPONENTS
           ============================================================================ */

        .divider {
            text-align: center;
            margin: 24px 0;
            position: relative;
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: calc(50% - 24px);
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }

        .divider::before {
            left: 0;
        }

        .divider::after {
            right: 0;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            margin-top: 12px;
            gap: 12px;
        }

        .remember-forgot label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .remember-forgot label:hover {
            color: var(--color-accent-cyan);
        }

        .remember-forgot input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: var(--color-accent-cyan);
        }

        .remember-forgot a {
            color: var(--color-accent-cyan);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .remember-forgot a:hover {
            color: var(--color-accent-blue);
            text-decoration: underline;
        }

        .hidden {
            display: none !important;
        }

        /* ============================================================================
           HEADER & NAVIGATION
           ============================================================================ */

        .header {
            background: var(--color-white);
            border-bottom: 1px solid var(--color-border);
            padding: 24px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 24px;
            flex: 1;
        }

        .header-logo {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-text-dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-shrink: 0;
            letter-spacing: -0.5px;
        }

        .header-logo i {
            margin-right: 12px;
            color: var(--color-accent-blue);
        }

        .header-nav {
            display: flex;
            gap: 32px;
            align-items: center;
        }

        .header-nav a {
            color: var(--color-text-light);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            position: relative;
        }

        .header-nav a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--color-accent-blue);
            transition: width 0.3s ease;
        }

        .header-nav a:hover {
            color: var(--color-text-dark);
        }

        .header-nav a:hover::after {
            width: 100%;
        }

        .header-right {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .public-header {
            gap: 20px;
            padding: 20px 40px;
        }

        .public-header .header-nav {
            display: none;
        }

        /* ============================================================================
           WORKSPACE SELECTOR
           ============================================================================ */

        .workspace-switcher {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-direction: column;
        }

        .workspace-switcher label {
            font-size: 12px;
            font-weight: 600;
            color: var(--color-text-light);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            align-self: flex-start;
        }

        .workspace-switcher select {
            min-width: 220px;
            border: 1px solid var(--color-border);
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
            color: var(--color-text-dark);
            background: var(--color-white);
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .workspace-switcher select:hover {
            border-color: var(--color-accent-blue);
            box-shadow: 0 2px 8px rgba(56, 189, 248, 0.1);
        }

        .workspace-switcher select:focus {
            outline: none;
            border-color: var(--color-accent-blue);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
        }

        .workspace-switcher--sidebar label {
            color: rgba(255, 255, 255, 0.6);
            font-weight: 700;
            letter-spacing: 0.4px;
            font-size: 11px;
        }

        .workspace-switcher--sidebar select {
            min-width: auto;
            width: 100%;
            color: rgba(255, 255, 255, 0.92);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .workspace-switcher--sidebar select option {
            color: var(--color-text-dark);
            background: var(--color-white);
        }

        .workspace-switcher--sidebar select:hover {
            border-color: rgba(103, 232, 249, 0.5);
            box-shadow: 0 0 0 3px rgba(103, 232, 249, 0.12);
        }

        .workspace-switcher--sidebar select:focus {
            border-color: rgba(103, 232, 249, 0.75);
            box-shadow: 0 0 0 3px rgba(103, 232, 249, 0.2);
        }

        /* ============================================================================
           SECTIONS & CONTENT
           ============================================================================ */

        .welcome-section {
            padding: 80px 48px;
            text-align: center;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.05) 0%, rgba(103, 232, 249, 0.05) 100%);
            border-bottom: 1px solid var(--color-border);
        }

        .welcome-title {
            font-size: 48px;
            font-weight: 800;
            color: var(--color-text-dark);
            margin-bottom: 20px;
            letter-spacing: -1px;
            line-height: 1.2;
        }

        .welcome-subtitle {
            font-size: 18px;
            color: var(--color-text-light);
            margin-bottom: 48px;
            line-height: 1.7;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .features-section {
            padding: 80px 48px;
            background: var(--color-white);
        }

        .section-title {
            font-size: 36px;
            font-weight: 800;
            color: var(--color-text-dark);
            margin-bottom: 48px;
            text-align: center;
            letter-spacing: -0.5px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            margin-bottom: 80px;
        }

        .feature-card {
            padding: 40px 32px;
            background: var(--color-white);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--color-accent-blue), var(--color-accent-cyan));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            border-color: var(--color-accent-blue);
            box-shadow: 0 12px 32px rgba(56, 189, 248, 0.15);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-icon {
            font-size: 42px;
            color: var(--color-accent-blue);
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--color-text-dark);
            margin-bottom: 12px;
            letter-spacing: -0.3px;
        }

        .feature-desc {
            color: var(--color-text-light);
            font-size: 14px;
            line-height: 1.7;
        }

        .screenshots-section {
            padding: 80px 48px;
            background: var(--color-bg);
        }

        .screenshot-placeholder {
            width: 100%;
            height: 320px;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.1) 0%, rgba(103, 232, 249, 0.1) 100%);
            border: 2px dashed var(--color-border);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-text-light);
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .screenshot-placeholder:hover {
            border-color: var(--color-accent-blue);
            background: rgba(56, 189, 248, 0.05);
        }

        .contact-section {
            padding: 80px 48px;
            background: var(--color-white);
        }

        .contact-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .contact-form .form-group {
            margin-bottom: 24px;
        }

        .contact-form textarea {
            padding: 12px 14px;
            border: 1px solid var(--color-border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', inherit;
            resize: vertical;
            min-height: 140px;
            transition: all 0.3s ease;
        }

        .contact-form textarea:focus {
            outline: none;
            border-color: var(--color-accent-blue);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
        }

        /* ============================================================================
           LOGOUT BUTTON
           ============================================================================ */

        .logout-btn {
            background: var(--color-error);
            color: var(--color-white);
            padding: 11px 24px;
            border-radius: 24px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-top: 12px;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
        }

        /* ============================================================================
           RESPONSIVE DESIGN
           ============================================================================ */

        @media (max-width: 1024px) {
            .main-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                min-width: auto;
                padding: 32px 24px;
                border-right: none;
                border-bottom: 1px solid var(--color-border);
                order: 2;
                min-height: auto;
            }

            .content {
                width: 100%;
                order: 1;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .header-nav {
                gap: 20px;
                font-size: 13px;
            }
        }

        @media (max-width: 768px) {
            :root {
                --font-size-base: 14px;
            }

            .main-container {
                flex-direction: column;
            }

            @if(auth()->check())
                .sidebar {
                    position: fixed;
                    left: 0;
                    top: 0;
                    width: 85%;
                    max-width: 320px;
                    height: 100vh;
                    padding: 24px 20px;
                    transform: translateX(-100%);
                    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    z-index: 1000;
                    border-right: 1px solid rgba(255, 255, 255, 0.05);
                    border-bottom: none;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                }

                .sidebar.open {
                    transform: translateX(0);
                }

                .sidebar-overlay {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.4);
                    z-index: 999;
                    backdrop-filter: blur(2px);
                }

                .sidebar-overlay.open {
                    display: block;
                }

                #sidebarToggle {
                    display: inline-flex !important;
                    align-items: center;
                    justify-content: center;
                    width: 40px;
                    height: 40px;
                    background: var(--color-white);
                    border: none;
                    border-radius: 10px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }

                #sidebarToggle:hover {
                    background: var(--color-bg);
                    transform: scale(1.05);
                }
            @else
                .sidebar {
                    position: fixed;
                    left: 0;
                    top: 0;
                    width: 85%;
                    max-width: 380px;
                    height: 100vh;
                    padding: 24px 20px;
                    transform: translateX(-100%);
                    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    z-index: 1000;
                    border-right: 1px solid rgba(255, 255, 255, 0.05);
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                }

                .sidebar.open {
                    transform: translateX(0);
                }

                .sidebar-overlay {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.4);
                    z-index: 999;
                    backdrop-filter: blur(2px);
                }

                .sidebar-overlay.open {
                    display: block;
                }

                #sidebarToggle {
                    display: inline-flex !important;
                    align-items: center;
                    justify-content: center;
                    width: 40px;
                    height: 40px;
                    background: var(--color-white);
                    border: none;
                    border-radius: 10px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }

                #sidebarToggle:hover {
                    background: var(--color-bg);
                    transform: scale(1.05);
                }

                .public-header #sidebarToggle {
                    order: -1;
                }
            @endif

            .sidebar-logo {
                margin-bottom: 32px;
            }

            .sidebar-logo img {
                max-height: 70px;
            }

            .header {
                padding: 16px 20px;
                gap: 12px;
            }

            .header-left {
                gap: 12px;
            }

            .header-logo {
                font-size: 20px;
            }

            .header-right {
                gap: 12px;
                font-size: 13px;
            }

            .header-nav {
                width: 100%;
                gap: 12px;
                justify-content: center;
            }

            .public-header {
                padding: 16px 20px;
            }

            .welcome-section,
            .features-section,
            .screenshots-section,
            .contact-section {
                padding: 48px 20px;
            }

            .welcome-title {
                font-size: 32px;
                margin-bottom: 16px;
            }

            .welcome-subtitle {
                font-size: 16px;
                margin-bottom: 32px;
            }

            .section-title {
                font-size: 28px;
                margin-bottom: 32px;
            }

            .features-grid {
                gap: 20px;
            }

            .feature-card {
                padding: 28px 20px;
            }

            .screenshot-placeholder {
                height: 240px;
                font-size: 16px;
            }

            .form-container {
                gap: 20px;
            }

            .tab-buttons {
                gap: 8px;
                margin-bottom: 20px;
            }

            .tab-btn {
                padding: 8px 14px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                max-width: calc(100vw - 60px) !important;
            }

            .welcome-title {
                font-size: 28px;
            }

            .section-title {
                font-size: 24px;
            }

            .btn {
                padding: 10px 20px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    @php
        $installerFile = storage_path('app/installer/installed.json');
        $orgName = null;
        if (file_exists($installerFile)) {
            $data = @json_decode(file_get_contents($installerFile), true);
            if (!empty($data['organization_name'])) {
                $orgName = trim($data['organization_name']);
            }
        }
        $headerSuffix = $orgName ?: 'Configuration Backup Service';
    @endphp
    <div class="main-container">
        <!-- SIDEBAR OVERLAY (Mobile only) -->
        <div id="sidebarOverlay" class="sidebar-overlay"></div>

        <!-- LEFT SIDEBAR (20%) -->
        <div class="sidebar">
            <div class="sidebar-logo">
                <img src="{{ !empty($siteLogoUrl) ? $siteLogoUrl : asset('branding/atglance-logo-cc.png') }}" alt="AtGlance Logo" style="max-width: 250px; max-height: 100px; object-fit: contain;">
            </div>

            <div class="form-container" id="authForm">
                @php
                    $disableEmailRegistration = (bool) ($disableEmailRegistration ?? false);
                    $showSsoAuthOptions = (bool) ($ssoEnabled ?? false) && !empty($ssoProvidersForAuth ?? []);
                @endphp
                <!-- Auth Tabs -->
                <div class="tab-buttons auth-tabs">
                    <button class="tab-btn active" data-tab="login" onclick="switchTab('login', event)">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                    <button class="tab-btn" data-tab="register" onclick="switchTab('register', event)" {{ $disableEmailRegistration ? 'disabled' : '' }}>
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                    <button class="tab-btn" data-tab="forgot" onclick="switchTab('forgot', event)" {{ $disableEmailRegistration ? 'disabled' : '' }}>
                        <i class="fas fa-key"></i> Forgot
                    </button>
                </div>

                <!-- LOGIN FORM -->
                <div class="tab-content active" id="login">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        @if ($errors->has('login'))
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $errors->first('login') }}</span>
                        </div>
                        @endif

                        @if (session('inactive_user'))
                        <div class="alert alert-error">
                            <i class="fas fa-user-slash"></i>
                            <span>{{ session('inactive_user') }}</span>
                        </div>
                        @endif

                        <div class="form-group">
                            <label for="login_email"><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" id="login_email" name="email" placeholder="you@example.com" required value="{{ old('email') }}">
                        </div>

                        <div class="form-group">
                            <label for="login_password"><i class="fas fa-lock"></i> Password</label>
                            <input type="password" id="login_password" name="password" placeholder="Enter your password" required>
                        </div>

                        <div class="remember-forgot">
                            <label style="display: flex; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="remember" id="remember">
                                <span>Remember me</span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>

                        @if($showSsoAuthOptions)
                            <div class="divider">Or continue with SSO</div>
                            <div class="sso-grid">
                                @foreach($ssoProvidersForAuth as $ssoProvider)
                                    <a class="btn btn-secondary btn-sso" href="{{ route('auth.sso.redirect', ['provider' => $ssoProvider['key'], 'context' => 'login']) }}">
                                        <i class="{{ $ssoProvider['icon'] }}"></i>
                                        <span>Continue with {{ $ssoProvider['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </form>
                </div>

                <!-- REGISTRATION FORM -->
                <div class="tab-content" id="register">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        @if ($errors->has('register'))
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $errors->first('register') }}</span>
                        </div>
                        @endif

                        @if($disableEmailRegistration)
                        <div class="alert alert-error">
                            <i class="fas fa-ban"></i>
                            <span>Email registration is disabled by the administrator. Please use SSO.</span>
                        </div>
                        @endif

                        <fieldset {{ $disableEmailRegistration ? 'disabled' : '' }} style="border:0; margin:0; padding:0; {{ $disableEmailRegistration ? 'opacity:0.55;' : '' }}">
                            <div class="form-group">
                                <label for="reg_name"><i class="fas fa-user"></i> Full Name</label>
                                <input type="text" id="reg_name" name="name" placeholder="John Doe" required value="{{ old('name') }}">
                            </div>

                            <div class="form-group">
                                <label for="reg_email"><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" id="reg_email" name="email" placeholder="you@example.com" required value="{{ old('email') }}">
                            </div>

                            <div class="form-group">
                                <label for="reg_password"><i class="fas fa-lock"></i> Password</label>
                                <input type="password" id="reg_password" name="password" placeholder="Minimum 8 characters" required>
                            </div>

                            <div class="form-group">
                                <label for="reg_confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
                                <input type="password" id="reg_confirm_password" name="password_confirmation" placeholder="Confirm password" required>
                            </div>
                            <br>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </fieldset>

                        @if($showSsoAuthOptions)
                            <div class="divider">Or register with SSO</div>
                            <div class="sso-grid">
                                @foreach($ssoProvidersForAuth as $ssoProvider)
                                    <a class="btn btn-secondary btn-sso" href="{{ route('auth.sso.redirect', ['provider' => $ssoProvider['key'], 'context' => 'register']) }}">
                                        <i class="{{ $ssoProvider['icon'] }}"></i>
                                        <span>Register with {{ $ssoProvider['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </form>
                </div>

                <!-- FORGOT PASSWORD FORM -->
                <div class="tab-content" id="forgot">
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        @if($disableEmailRegistration)
                        <div class="alert alert-error">
                            <i class="fas fa-ban"></i>
                            <span>Forgot password is disabled while email registration is off.</span>
                        </div>
                        @endif

                        <fieldset {{ $disableEmailRegistration ? 'disabled' : '' }} style="border:0; margin:0; padding:0; {{ $disableEmailRegistration ? 'opacity:0.55;' : '' }}">
                        <p style="font-size: 13px; color: rgba(255, 255, 255, 0.7); margin-bottom: 20px; line-height: 1.6;">
                            Enter your email address and we'll send you a link to reset your password.
                        </p>

                        @if ($errors->has('email'))
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $errors->first('email') }}</span>
                        </div>
                        @endif

                        @if (session('status'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ session('status') }}</span>
                        </div>
                        @endif

                        <div class="form-group">
                            <label for="forgot_email"><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" id="forgot_email" name="email" placeholder="you@example.com" required value="{{ old('email') }}">
                        </div>
                        </br>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-envelope"></i> Send Reset Link
                        </button>
                        </fieldset>
                    </form>
                </div>
            </div>

            <!-- Public Navigation (shown on homepage when not authenticated) -->
            <div id="publicNav" style="margin-top: 32px; padding-top: 32px; border-top: 1px solid rgba(255, 255, 255, 0.1); display: none;">
                <p style="font-size: 11px; color: rgba(255, 255, 255, 0.6); margin-bottom: 16px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Quick Links</p>
                <nav style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="https://atglance.live/about" target="_blank" class="nav-link" style="padding: 12px 16px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 12px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.borderColor='rgba(255, 255, 255, 0.2)'; this.style.transform='translateX(4px)'; this.style.boxShadow='0 4px 12px rgba(103, 232, 249, 0.1)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'; this.style.borderColor='rgba(255, 255, 255, 0.12)'; this.style.transform='translateX(0)'; this.style.boxShadow='none';">
                        <i class="fas fa-lightbulb" style="color: #67E8F9; font-size: 16px; width: 20px; text-align: center;"></i> About
                    </a>
                    <a href="https://atglance.live/architecture" target="_blank" class="nav-link" style="padding: 12px 16px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 12px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.borderColor='rgba(255, 255, 255, 0.2)'; this.style.transform='translateX(4px)'; this.style.boxShadow='0 4px 12px rgba(103, 232, 249, 0.1)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'; this.style.borderColor='rgba(255, 255, 255, 0.12)'; this.style.transform='translateX(0)'; this.style.boxShadow='none';">
                        <i class="fas fa-rocket" style="color: #38BDF8; font-size: 16px; width: 20px; text-align: center;"></i> Features
                    </a>
                    <a href="https://atglance.live/faq" target="_blank" class="nav-link" style="padding: 12px 16px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 12px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.borderColor='rgba(255, 255, 255, 0.2)'; this.style.transform='translateX(4px)'; this.style.boxShadow='0 4px 12px rgba(103, 232, 249, 0.1)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'; this.style.borderColor='rgba(255, 255, 255, 0.12)'; this.style.transform='translateX(0)'; this.style.boxShadow='none';">
                        <i class="fas fa-comments" style="color: #67E8F9; font-size: 16px; width: 20px; text-align: center;"></i> FAQ
                    </a>
                    <a href="https://atglance.live/docs" target="_blank" class="nav-link" style="padding: 12px 16px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 12px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.borderColor='rgba(255, 255, 255, 0.2)'; this.style.transform='translateX(4px)'; this.style.boxShadow='0 4px 12px rgba(103, 232, 249, 0.1)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'; this.style.borderColor='rgba(255, 255, 255, 0.12)'; this.style.transform='translateX(0)'; this.style.boxShadow='none';">
                        <i class="fas fa-life-ring" style="color: #38BDF8; font-size: 16px; width: 20px; text-align: center;"></i> Support
                    </a>
                    <a href="https://atglance.live/contact" target="_blank" class="nav-link" style="padding: 12px 16px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 12px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.borderColor='rgba(255, 255, 255, 0.2)'; this.style.transform='translateX(4px)'; this.style.boxShadow='0 4px 12px rgba(103, 232, 249, 0.1)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'; this.style.borderColor='rgba(255, 255, 255, 0.12)'; this.style.transform='translateX(0)'; this.style.boxShadow='none';">
                        <i class="fas fa-paper-plane" style="color: #67E8F9; font-size: 16px; width: 20px; text-align: center;"></i> Contact
                    </a>
                </nav>
            </div>

            <!-- Dashboard Nav (shown after login) -->
            <div id="dashboardNav" class="hidden" style="margin-top: 48px; padding-top: 48px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                @php($requiresProfileSetup = auth()->check() && (!auth()->user()->dob || !auth()->user()->pin))
                
                @if(!empty($workspaceSelectorOptions) && count($workspaceSelectorOptions) > 0)
                    <div style="margin-bottom: 28px;">
                        <form method="POST" action="{{ route('workspace.select') }}" class="workspace-switcher workspace-switcher--sidebar">
                            @csrf
                            <label for="workspace_selector">Workspace</label>
                            <select id="workspace_selector" name="workspace_id" onchange="this.form.submit()">
                                @foreach($workspaceSelectorOptions as $workspaceOption)
                                    <option value="{{ $workspaceOption->id }}" {{ (int) ($selectedWorkspaceId ?? 0) === (int) $workspaceOption->id ? 'selected' : '' }}>
                                        {{ $workspaceOption->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                @endif
                
                <div style="margin-bottom: 36px;">
                    <p style="font-size: 11px; color: rgba(255, 255, 255, 0.5); margin-bottom: 16px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Menu</p>
                    @if($requiresProfileSetup)
                        <div style="margin-bottom: 16px; padding: 12px 14px; border-radius: 10px; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #FCD34D; font-size: 12px; font-weight: 600;">
                            <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
                            Complete mandatory profile setup to unlock all pages.
                        </div>
                    @endif
                    <nav style="display: flex; flex-direction: column; gap: 8px;">
                        @if(!$requiresProfileSetup)
                        <a href="{{ route('dashboard') }}" class="nav-link" style="padding: 12px 14px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 10px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.12)'; this.style.color='rgba(255, 255, 255, 1)'; this.style.transform='translateX(4px)';" onmouseout="this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.85)'; this.style.transform='translateX(0)';">
                            <i class="fas fa-chart-line" style="color: #67E8F9; width: 18px;"></i> Dashboard
                        </a>
                        <a href="{{ route('settings') }}" class="nav-link" style="padding: 12px 14px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 10px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.12)'; this.style.color='rgba(255, 255, 255, 1)'; this.style.transform='translateX(4px)';" onmouseout="this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.85)'; this.style.transform='translateX(0)';">
                            <i class="fas fa-cog" style="color: #38BDF8; width: 18px;"></i> Settings
                        </a>
                        @if(auth()->check() && in_array((int) auth()->user()->rbac_id, [100, 101], true))
                        <a href="{{ route('admin.users') }}" class="nav-link" style="padding: 12px 14px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 10px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.12)'; this.style.color='rgba(255, 255, 255, 1)'; this.style.transform='translateX(4px)';" onmouseout="this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.85)'; this.style.transform='translateX(0)';">
                            <i class="fas fa-users" style="color: #67E8F9; width: 18px;"></i> Manage Users
                        </a>
                        <a href="{{ (int) auth()->user()->rbac_id === 100 ? route('enterprise.console') : route('admin.workspaces') }}" class="nav-link" style="padding: 12px 14px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 10px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.12)'; this.style.color='rgba(255, 255, 255, 1)'; this.style.transform='translateX(4px)';" onmouseout="this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.85)'; this.style.transform='translateX(0)';">
                            <i class="fas fa-sitemap" style="color: #38BDF8; width: 18px;"></i> Manage Workspace
                        </a>
                        @if((int) auth()->user()->rbac_id === 100)
                        <a href="{{ route('admin.settings') }}" class="nav-link" style="padding: 12px 14px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 10px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.12)'; this.style.color='rgba(255, 255, 255, 1)'; this.style.transform='translateX(4px)';" onmouseout="this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.85)'; this.style.transform='translateX(0)';">
                            <i class="fas fa-sliders-h" style="color: #67E8F9; width: 18px;"></i> Site Setting
                        </a>
                        <a href="{{ route('enterprise.console') }}" class="nav-link" style="padding: 12px 14px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 10px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.12)'; this.style.color='rgba(255, 255, 255, 1)'; this.style.transform='translateX(4px)';" onmouseout="this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.85)'; this.style.transform='translateX(0)';">
                            <i class="fas fa-building" style="color: #38BDF8; width: 18px;"></i> Enterprise Console
                        </a>
                        @endif
                        <a href="{{ route('systems-registered') }}" class="nav-link" style="padding: 12px 14px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 10px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.12)'; this.style.color='rgba(255, 255, 255, 1)'; this.style.transform='translateX(4px)';" onmouseout="this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.85)'; this.style.transform='translateX(0)';">
                            <i class="fas fa-server" style="color: #67E8F9; width: 18px;"></i> Systems Registered
                        </a>
                        <a href="{{ route('configuration-backups') }}" class="nav-link" style="padding: 12px 14px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 10px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.12)'; this.style.color='rgba(255, 255, 255, 1)'; this.style.transform='translateX(4px)';" onmouseout="this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.85)'; this.style.transform='translateX(0)';">
                            <i class="fas fa-file-code" style="color: #38BDF8; width: 18px;"></i> Configuration Backups
                        </a>
                        @endif
                        @endif
                        <a href="{{ route('profile') }}" class="nav-link" style="padding: 12px 14px; color: rgba(255, 255, 255, 0.85); text-decoration: none; border-radius: 10px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 12px; font-weight: 500; font-size: 13px;" onmouseover="this.style.background='rgba(255, 255, 255, 0.12)'; this.style.color='rgba(255, 255, 255, 1)'; this.style.transform='translateX(4px)';" onmouseout="this.style.background='transparent'; this.style.color='rgba(255, 255, 255, 0.85)'; this.style.transform='translateX(0)';">
                            <i class="fas fa-user-circle" style="color: #67E8F9; width: 18px;"></i> Profile
                        </a>
                    </nav>
                </div>

                <form id="logoutForm" method="POST" action="{{ route('logout') }}" style="margin-top: 24px;">
                    @csrf
                    <button type="submit" class="logout-btn" style="width: 100%;">
                        <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> Logout
                    </button>
                </form>
                <div style="margin-top:12px; text-align:center; font-size:11px; color:rgba(255, 255, 255, 0.4);">
                    Version {{ $appVersion ?? config('app.version') }}
                </div>
            </div>
        </div>

        <!-- RIGHT CONTENT (80%) -->
        <div class="content">
            @if(auth()->check())
                <!-- DASHBOARD HEADER -->
                <div class="header">
                    <div class="header-left" style="display: flex; align-items: center; gap: 20px;">
                        <button id="sidebarToggle" type="button" style="background: var(--color-white); border: none; font-size: 18px; cursor: pointer; padding: 10px 12px; border-radius: 10px; display: none; color: var(--color-text-dark); transition: all 0.3s ease; width: 40px; height: 40px;" title="Toggle Menu">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="header-logo" style="color: var(--color-text-dark); letter-spacing: -0.5px;">
                            <i class="fas fa-gate" style="color: var(--color-accent-blue);"></i> {{ $headerSuffix }} 
                        </div>
                    </div>
                    <div class="header-right">
                        <span style="color: var(--color-text-dark); font-weight: 600; font-size: 14px;">👋 Welcome, {{ auth()->user()->name }}!</span>
                    </div>
                </div>

                <!-- DASHBOARD CONTENT -->
                <div id="dashboardContent">
                    @yield('dashboard-content')
                </div>

                <footer style="margin-top:0; padding:16px 24px; border-top: 1px solid var(--color-border); color: var(--color-text-light); font-size:12px; text-align:center; background: var(--color-white);">
                    <p>&copy; 2026 AtGlance. All rights reserved. | <a href="https://atglance.live/privacy" style="color: var(--color-accent-blue); text-decoration: none; transition: all 0.3s ease;" onmouseover="this.style.color='var(--color-accent-cyan)';" onmouseout="this.style.color='var(--color-accent-blue)';">Privacy Policy</a> | <a href="https://atglance.live/terms" style="color: var(--color-accent-blue); text-decoration: none; transition: all 0.3s ease;" onmouseover="this.style.color='var(--color-accent-cyan)';" onmouseout="this.style.color='var(--color-accent-blue)';">Terms of Service</a></p>
                </footer>
            @else
                <!-- PUBLIC HEADER -->
                <div class="header public-header">
                    <button id="sidebarToggle" type="button" style="background: var(--color-white); border: none; font-size: 18px; cursor: pointer; padding: 10px 12px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; color: var(--color-text-dark); transition: all 0.3s ease; width: 40px; height: 40px;" title="Toggle Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="header-logo" style="color: var(--color-text-dark); letter-spacing: -0.5px;">
                        <i class="fas fa-gate" style="color: var(--color-accent-blue);"></i> {{ $headerSuffix }}
                    </div>
                </div>

                <!-- WELCOME SECTION -->
                <div class="welcome-section">
                    <h1 class="welcome-title">Welcome to AtGlance</h1>
                    <p class="welcome-subtitle">
                        @if(!empty($siteContent))
                            {{ $siteContent }}
                        @else
                            AtGlance is a Configuration Files Backup as a Service platform built for Linux environments.<br>
                            Securely back up critical server configuration files, monitor service health, and restore faster with centralized management.
                        @endif
                    </p>
                </div>

                <!-- FEATURES SECTION -->
                <div class="features-section" id="features">
                    <h2 class="section-title">Powerful Features</h2>
                    <div class="features-grid">
                        @if(!empty($siteFeatures))
                            @foreach($siteFeatures as $feature)
                                <div class="feature-card">
                                    <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                                    <div class="feature-title">Custom Feature</div>
                                    <div class="feature-desc">{{ $feature }}</div>
                                </div>
                            @endforeach
                        @else
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                            <div class="feature-title">Lightning Fast</div>
                            <div class="feature-desc">Optimized performance with sub-millisecond latency</div>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                            <div class="feature-title">Secure</div>
                            <div class="feature-desc">Enterprise-grade security with encryption and auth</div>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
                            <div class="feature-title">Analytics</div>
                            <div class="feature-desc">Real-time monitoring and comprehensive analytics</div>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-cogs"></i></div>
                            <div class="feature-title">Configuration</div>
                            <div class="feature-desc">Easy setup with intuitive configuration options</div>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-expand"></i></div>
                            <div class="feature-title">Scalability</div>
                            <div class="feature-desc">Seamlessly scale from startup to enterprise</div>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-headset"></i></div>
                            <div class="feature-title">Support</div>
                            <div class="feature-desc">24/7 dedicated support team ready to help</div>
                        </div>
                        @endif
                    </div>
                </div>

                {{--
                <!-- SCREENSHOTS SECTION -->
                <div class="screenshots-section" id="screenshots">
                    <h2 class="section-title">See It In Action</h2>
                    <div class="screenshot-placeholder">
                        <i class="fas fa-image"></i> Dashboard Screenshot
                    </div>
                    <div class="screenshot-placeholder">
                        <i class="fas fa-image"></i> Analytics Screenshot
                    </div>
                </div>
                --}}

                {{--
                <!-- CONTACT SECTION -->
                <div class="contact-section" id="contact">
                    <h2 class="section-title">Get in Touch</h2>
                    <form class="contact-form" method="POST" action="{{ route('contact') }}">
                        @csrf
                        <div class="form-group">
                            <label for="contact_name"><i class="fas fa-user"></i> Your Name</label>
                            <input type="text" id="contact_name" name="name" placeholder="John Doe" required>
                        </div>

                        <div class="form-group">
                            <label for="contact_email"><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" id="contact_email" name="email" placeholder="you@example.com" required>
                        </div>

                        <div class="form-group">
                            <label for="contact_subject"><i class="fas fa-heading"></i> Subject</label>
                            <input type="text" id="contact_subject" name="subject" placeholder="What is this about?" required>
                        </div>

                        <div class="form-group">
                            <label for="contact_message"><i class="fas fa-comment"></i> Message</label>
                            <textarea id="contact_message" name="message" placeholder="Your message here..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
                --}}

                <!-- FOOTER -->
                <footer style="padding: 40px; background: #f3f3f3; border-top: 1px solid #b3b3b3; text-align: center; color: #444; font-size: 14px;">
                    <p>&copy; 2026 AtGlance. All rights reserved. | <a href="https://atglance.live/privacy" style="color: #000000;">Privacy Policy</a> | <a href="https://atglance.live/terms" style="color: #000000;">Terms of Service</a></p>
                </footer>
            @endif
        </div>
    </div>

    <script>
        function switchTab(tabName, evt) {
            const targetButton = evt && evt.target
                ? evt.target.closest('.tab-btn')
                : document.querySelector(`.tab-btn[data-tab="${tabName}"]`);

            if (targetButton && targetButton.disabled) {
                return;
            }

            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked button
            if (targetButton) {
                targetButton.classList.add('active');
            }
        }

        // Toggle dashboard nav visibility when user is authenticated
        document.addEventListener('DOMContentLoaded', function() {
            const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
            const authForm = document.getElementById('authForm');
            const dashboardNav = document.getElementById('dashboardNav');

            if (isAuthenticated) {
                authForm.classList.add('hidden');
                dashboardNav.classList.remove('hidden');
                return;
            }

            const activeButton = document.querySelector('.tab-btn.active');
            if (activeButton && activeButton.disabled) {
                switchTab('login');
            }
        });

        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    const element = document.querySelector(href);
                    if (element) {
                        element.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });

        function submitLogoutForm() {
            const logoutForm = document.getElementById('logoutForm');
            if (logoutForm) {
                logoutForm.submit();
            }
        }

        // Sidebar toggle functionality for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const publicNav = document.getElementById('publicNav');
            const dashboardNav = document.getElementById('dashboardNav');

            // Handle sidebar toggle for both public and dashboard pages
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                    sidebarOverlay.classList.toggle('open');
                });
            }

            // Close sidebar when overlay is clicked
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('open');
                });
            }

            // Toggle between public and dashboard navigation
            @if(auth()->check())
                if (publicNav) publicNav.style.display = 'none';
                if (dashboardNav) dashboardNav.classList.remove('hidden');
            @else
                if (publicNav) publicNav.style.display = 'block';
                if (dashboardNav) dashboardNav.classList.add('hidden');
            @endif
        });

        @if (session('inactive_user'))
            window.addEventListener('DOMContentLoaded', function () {
                alert(@json(session('inactive_user')));
            });
        @endif
    </script>

</body>
</html>

<x-filament-panels::page.simple>
    <style>
        .fi-simple-main {
            max-width: none !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .fi-simple-header {
            display: none !important;
        }

        .rm-login-shell {
            position: fixed;
            inset: 0;
            width: 100%;
            min-height: 100vh;
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            background:
                radial-gradient(circle at top left, rgba(99, 102, 241, 0.25), transparent 32rem),
                radial-gradient(circle at bottom right, rgba(245, 158, 11, 0.18), transparent 34rem),
                linear-gradient(135deg, #070b1a 0%, #0b1224 50%, #111a33 100%);
        }

        .rm-login-pattern {
            position: absolute;
            inset: 0;
            opacity: 0.05;
            background-image: radial-gradient(#ffffff 1px, transparent 1px);
            background-size: 22px 22px;
            pointer-events: none;
        }

        .fi-loading-indicator,
        .fi-topbar,
        .fi-simple-header {
            display: none !important;
        }

        .rm-login-button[disabled] {
            opacity: 0.75;
            cursor: not-allowed;
        }

        .rm-login-card {
            position: relative;
            width: 100%;
            max-width: 980px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 30px 90px rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(18px);
        }

        .rm-brand-panel {
            padding: 44px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid rgba(255, 255, 255, 0.12);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
        }

        .rm-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .rm-logo {
            width: 42px;
            height: 42px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #fbbf24, #d97706);
            box-shadow: 0 12px 30px rgba(245, 158, 11, 0.28);
            color: #0b1224;
            font-weight: 900;
            font-size: 17px;
        }

        .rm-brand-title {
            color: #ffffff;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.1;
        }

        .rm-brand-subtitle {
            margin-top: 4px;
            color: rgba(252, 211, 77, 0.9);
            font-size: 10px;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .rm-hero-title {
            margin-top: 42px;
            color: #ffffff;
            font-size: 34px;
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .rm-hero-text {
            margin-top: 18px;
            color: rgba(203, 213, 225, 0.82);
            font-size: 15px;
            line-height: 1.65;
        }

        .rm-feature-list {
            margin-top: 30px;
            display: grid;
            gap: 14px;
            padding: 0;
            list-style: none;
        }

        .rm-feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #e2e8f0;
            font-size: 14px;
            font-weight: 600;
        }

        .rm-check {
            width: 26px;
            height: 26px;
            min-width: 26px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(16, 185, 129, 0.16);
            border: 1px solid rgba(52, 211, 153, 0.35);
            color: #6ee7b7;
            font-size: 14px;
            font-weight: 900;
            line-height: 1;
        }

        .rm-brand-footer {
            margin-top: 40px;
            color: rgba(148, 163, 184, 0.78);
            font-size: 11px;
        }

        .rm-form-panel {
            padding: 44px;
            background: rgba(15, 23, 42, 0.92);
        }

        .rm-mobile-brand {
            display: none;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }

        .rm-form-title {
            color: #ffffff;
            font-size: 30px;
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .rm-form-text {
            margin-top: 12px;
            color: #cbd5e1;
            font-size: 14px;
            line-height: 1.7;
        }

        .rm-form-wrap {
            margin-top: 32px;
        }

        .rm-form {
            display: grid;
            gap: 22px;
        }

        .rm-form label,
        .rm-form .fi-fo-field-wrp-label span {
            color: #cbd5e1 !important;
        }

        .rm-form input[type="checkbox"] {
            width: 1rem !important;
            height: 1rem !important;
            cursor: pointer !important;
            accent-color: #d97706 !important;
        }

        .rm-form input[type="checkbox"]:checked {
            background-color: #d97706 !important;
            border-color: #d97706 !important;
        }

        .rm-form label {
            cursor: pointer !important;
        }

        .rm-form .fi-checkbox-input {
            cursor: pointer !important;
        }

        .rm-form .fi-checkbox-input:checked {
            background-color: #d97706 !important;
            border-color: #d97706 !important;
        }

        .rm-form .fi-checkbox-input:focus {
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.25) !important;
        }

        .rm-login-button {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 14px 18px;
            cursor: pointer;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #111827;
            font-weight: 800;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            box-shadow: 0 12px 24px rgba(217, 119, 6, 0.22);
        }

        .rm-login-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(217, 119, 6, 0.28);
        }

        .rm-bottom-row {
            margin-top: 26px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            font-size: 14px;
        }

        .rm-home-link {
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.18s ease;
        }

        .rm-home-link:hover {
            color: #fbbf24;
        }

        .rm-version {
            color: #94a3b8;
            font-size: 12px;
        }

        .rm-authorized {
            margin-top: 38px;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
        }

        @media (max-width: 1024px) {
            .rm-login-card {
                max-width: 520px;
                grid-template-columns: 1fr;
            }

            .rm-brand-panel {
                display: none;
            }

            .rm-mobile-brand {
                display: flex;
            }
        }

        @media (max-width: 640px) {
            .rm-login-shell {
                margin: -1rem;
                padding: 1rem;
            }

            .rm-form-panel {
                padding: 30px 24px;
            }

            .rm-form-title {
                font-size: 28px;
            }
        }
    </style>

    <div class="rm-login-shell">
        <div class="rm-login-pattern"></div>

        <div class="rm-login-card">
            <aside class="rm-brand-panel">
                <div>
                    <div class="rm-brand">
                        <div class="rm-logo">RM</div>

                        <div>
                            <div class="rm-brand-title">
                                Result Merger
                            </div>

                            <div class="rm-brand-subtitle">
                                Academic Admin Panel
                            </div>
                        </div>
                    </div>

                    <h2 class="rm-hero-title">
                        Streamline academic result processing.
                    </h2>

                    <p class="rm-hero-text">
                        A premium portal for uploading, validating, merging,
                        and exporting student academic results with confidence.
                    </p>

                    <ul class="rm-feature-list">
                        <li class="rm-feature-item">
                            <span class="rm-check">✓</span>
                            <span>Test &amp; Exam Uploads</span>
                        </li>

                        <li class="rm-feature-item">
                            <span class="rm-check">✓</span>
                            <span>Smart Result Merging</span>
                        </li>

                        <li class="rm-feature-item">
                            <span class="rm-check">✓</span>
                            <span>Issue Tracking</span>
                        </li>

                        <li class="rm-feature-item">
                            <span class="rm-check">✓</span>
                            <span>Excel Export</span>
                        </li>
                    </ul>
                </div>

                <p class="rm-brand-footer">
                    © {{ date('Y') }} Result Merger · Authorized academic staff only.
                </p>
            </aside>

            <main class="rm-form-panel">
                <div class="rm-mobile-brand">
                    <div class="rm-logo">RM</div>

                    <div>
                        <div class="rm-brand-title">
                            Result Merger
                        </div>

                        <div class="rm-brand-subtitle">
                            Academic Admin Panel
                        </div>
                    </div>
                </div>

                <h1 class="rm-form-title">
                    Welcome Back
                </h1>

                <p class="rm-form-text">
                    Sign in to manage score uploads, merge results, track issues,
                    and export final records.
                </p>

                <div class="rm-form-wrap">
                    <form wire:submit.prevent="authenticate" class="rm-form"> {{ $this->form }}

                        <button type="submit" class="rm-login-button">
                            Sign in
                        </button>
                    </form>
                </div>

                <div class="rm-bottom-row">
                    <a href="{{ url('/') }}" class="rm-home-link">
                        ← Back to Home
                    </a>

                    <span class="rm-version">
                        v1.0
                    </span>
                </div>

                <p class="rm-authorized">
                    Authorized academic staff only.
                </p>
            </main>
        </div>
    </div>
</x-filament-panels::page.simple>
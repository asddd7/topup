@php
    $appName = setting('app_name', 'TopUp Game');
    $whatsapp = preg_replace('/\D+/', '', (string) setting('whatsapp', ''));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $appName }} - Maintenance</title>

    @if(setting('app_favicon'))
        <link rel="icon" href="{{ asset('storage/' . setting('app_favicon')) }}">
    @endif

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --maintenance-ink: #172033;
            --maintenance-muted: #64748b;
            --maintenance-primary: #2563eb;
            --maintenance-border: #dbe4f0;
            --maintenance-surface: rgba(255, 255, 255, 0.88);
        }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--maintenance-ink);
            background: #eef4fb;
            background-image: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 25%, transparent 25%), linear-gradient(315deg, rgba(14, 165, 233, 0.06) 25%, transparent 25%);
            background-position: 0 0, 32px 32px;
            background-size: 64px 64px;
        }

        .maintenance-shell {
            width: min(100% - 32px, 720px);
            margin: auto;
            padding: 24px 0;
        }

        .maintenance-card {
            border: 1px solid var(--maintenance-border);
            border-radius: 18px;
            background: var(--maintenance-surface);
            box-shadow: 0 22px 60px rgba(23, 32, 51, 0.12);
            backdrop-filter: blur(12px);
        }

        .maintenance-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: #e0ebff;
            color: var(--maintenance-primary);
            overflow: hidden;
        }

        .maintenance-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .maintenance-icon {
            display: inline-grid;
            place-items: center;
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: #fff7df;
            color: #d97706;
            font-size: 30px;
        }

        .maintenance-title {
            font-size: clamp(1.65rem, 5vw, 2.35rem);
            letter-spacing: 0;
        }

        .maintenance-copy {
            max-width: 520px;
            color: var(--maintenance-muted);
        }

        .maintenance-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        .maintenance-contact {
            border-top: 1px solid var(--maintenance-border);
            color: var(--maintenance-muted);
        }

        .maintenance-contact a {
            color: var(--maintenance-primary);
            text-decoration: none;
        }

        @media (max-width: 480px) {
            .maintenance-shell {
                width: min(100% - 20px, 720px);
            }

            .maintenance-card {
                border-radius: 14px;
            }

            .maintenance-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body class="d-flex align-items-center">
    <main class="maintenance-shell">
        <section class="maintenance-card p-4 p-md-5 text-center">
            <div class="maintenance-logo mb-4" aria-label="{{ $appName }}">
                @if(setting('app_logo'))
                    <img src="{{ asset('storage/' . setting('app_logo')) }}" alt="{{ $appName }}">
                @else
                    <i class="fa-solid fa-gamepad fs-3" aria-hidden="true"></i>
                @endif
            </div>

            <div class="maintenance-icon mb-4" aria-hidden="true">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>

            <p class="text-uppercase fw-semibold small text-primary mb-2">{{ $appName }}</p>
            <h1 class="maintenance-title fw-bold mb-3">Sedang kami persiapkan kembali</h1>
            <p class="maintenance-copy mx-auto mb-4">
                Website sedang dalam pemeliharaan agar layanan top up tetap berjalan lebih baik.
                Silakan coba lagi beberapa saat lagi.
            </p>

            <div class="maintenance-actions mb-4">
                <a href="{{ url()->current() }}" class="btn btn-primary px-4">
                    <i class="fa-solid fa-rotate me-2" aria-hidden="true"></i>
                    Coba Lagi
                </a>
                <a href="{{ route('login', ['admin' => 1]) }}" class="btn btn-outline-primary px-4">
                    <i class="fa-solid fa-shield-halved me-2" aria-hidden="true"></i>
                    Masuk Admin
                </a>
            </div>

            @if($whatsapp || setting('email'))
                <div class="maintenance-contact pt-3 small">
                    Butuh bantuan?
                    @if($whatsapp)
                        <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">
                            WhatsApp
                        </a>
                    @endif
                    @if($whatsapp && setting('email')) · @endif
                    @if(setting('email'))
                        <a href="mailto:{{ setting('email') }}">{{ setting('email') }}</a>
                    @endif
                </div>
            @endif
        </section>
    </main>
</body>
</html>

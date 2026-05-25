<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Verificación de Dos Pasos - ITSSMT</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('{{ asset('assets/images/itsmt.jpg') }}') center/cover no-repeat;
        }
        .overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(31, 54, 74, 0.5);
            z-index: 1;
        }
        .card-2fa {
            background: rgba(15, 15, 25, 0.82);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2.8rem 2.2rem;
            width: 100%;
            max-width: 420px;
            z-index: 2;
            box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.45);
            text-align: center;
        }
        .shield-icon {
            width: 68px;
            height: 68px;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.4rem;
            box-shadow: 0 0 0 10px rgba(59,130,246,0.15);
        }
        .shield-icon i {
            font-size: 2rem;
            color: white;
        }
        h3 {
            color: #fff;
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 0.4rem;
        }
        .subtitle {
            color: #94a3b8;
            font-size: 0.88rem;
            margin-bottom: 1.8rem;
            line-height: 1.5;
        }
        .subtitle span {
            color: #60a5fa;
            font-weight: 600;
        }

        /* Input de código OTP estilo */
        .otp-wrapper {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 1.6rem;
        }
        .otp-input {
            width: 50px;
            height: 58px;
            text-align: center;
            font-size: 1.6rem;
            font-weight: 700;
            border: 2px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            background: rgba(255,255,255,0.07);
            color: #fff;
            outline: none;
            transition: all 0.2s ease;
            caret-color: #3b82f6;
        }
        .otp-input:focus {
            border-color: #3b82f6;
            background: rgba(59,130,246,0.1);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.25);
        }
        .otp-input.filled {
            border-color: #22c55e;
            background: rgba(34,197,94,0.1);
        }

        .btn-verify {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: white;
            border: none;
            width: 100%;
            padding: 0.85rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59,130,246,0.4);
        }
        .btn-verify:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .resend-link {
            font-size: 0.84rem;
            color: #64748b;
        }
        .resend-link a {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        .resend-link a:hover {
            color: #93c5fd;
            text-decoration: underline;
        }
        .back-link {
            display: block;
            margin-top: 1.2rem;
            font-size: 0.82rem;
            color: #475569;
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover { color: #94a3b8; }

        /* Timer */
        .timer-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(234,179,8,0.12);
            border: 1px solid rgba(234,179,8,0.3);
            color: #fde047;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 20px;
            margin-bottom: 1.5rem;
        }
        .alert-glass {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
            border-radius: 10px;
            font-size: 0.85rem;
            padding: 10px 14px;
            margin-bottom: 1rem;
        }
        .alert-success-glass {
            background: rgba(34,197,94,0.1);
            border: 1px solid rgba(34,197,94,0.3);
            color: #86efac;
            border-radius: 10px;
            font-size: 0.85rem;
            padding: 10px 14px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <div class="card-2fa">

        <div class="shield-icon">
            <i class="mdi mdi-shield-lock-outline"></i>
        </div>

        <h3>Verificación en 2 pasos</h3>
        <p class="subtitle">
            Enviamos un código de 6 dígitos a tu correo institucional.<br>
            Ingresa el código para continuar.
        </p>

        <div class="timer-badge">
            <i class="mdi mdi-clock-outline"></i>
            Expira en: <span id="timer">10:00</span>
        </div>

        @if($errors->any())
            <div class="alert-glass">
                <i class="mdi mdi-alert-circle-outline"></i> {{ $errors->first() }}
            </div>
        @endif

        @if(session('resent'))
            <div class="alert-success-glass">
                <i class="mdi mdi-check-circle-outline"></i> {{ session('resent') }}
            </div>
        @endif

        <form method="POST" action="{{ route('2fa.verify') }}" id="otp-form">
            @csrf

            {{-- Inputs OTP visuales (uno por dígito) --}}
            <div class="otp-wrapper">
                @for($i = 1; $i <= 6; $i++)
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]" id="otp-{{ $i }}" autocomplete="off">
                @endfor
            </div>

            {{-- Input oculto que envía el código completo --}}
            <input type="hidden" name="code" id="full-code">

            <button type="submit" class="btn-verify" id="btn-verify">
                <i class="mdi mdi-check-bold"></i> Verificar Código
            </button>
        </form>

        <p class="resend-link">
            ¿No llegó el correo?
            <a href="{{ route('2fa.resend') }}">Reenviar código</a>
        </p>

        <a href="{{ route('login') }}" class="back-link">
            <i class="mdi mdi-arrow-left"></i> Volver al inicio de sesión
        </a>
    </div>

    <script>
        // ── Countdown timer ──────────────────────────────
        let timeLeft = 600;
        const timerEl = document.getElementById('timer');
        const interval = setInterval(() => {
            timeLeft--;
            const m = String(Math.floor(timeLeft / 60)).padStart(2, '0');
            const s = String(timeLeft % 60).padStart(2, '0');
            timerEl.textContent = `${m}:${s}`;
            if (timeLeft <= 0) {
                clearInterval(interval);
                timerEl.textContent = 'Expirado';
                timerEl.style.color = '#ef4444';
                document.getElementById('btn-verify').disabled = true;
            }
        }, 1000);

        // ── OTP Input behavior ───────────────────────────
        const inputs = document.querySelectorAll('.otp-input');

        inputs.forEach((input, idx) => {
            input.addEventListener('input', (e) => {
                // Solo números
                input.value = input.value.replace(/[^0-9]/g, '');
                if (input.value.length === 1) {
                    input.classList.add('filled');
                    if (idx < inputs.length - 1) inputs[idx + 1].focus();
                } else {
                    input.classList.remove('filled');
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && idx > 0) {
                    inputs[idx - 1].focus();
                    inputs[idx - 1].classList.remove('filled');
                }
            });

            // Pegar código completo
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasted = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                pasted.split('').forEach((char, i) => {
                    if (inputs[i]) {
                        inputs[i].value = char;
                        inputs[i].classList.add('filled');
                    }
                });
                if (pasted.length === 6) inputs[5].focus();
            });
        });

        // ── Construir el código completo antes de enviar ─
        document.getElementById('otp-form').addEventListener('submit', (e) => {
            const code = Array.from(inputs).map(i => i.value).join('');
            document.getElementById('full-code').value = code;
            if (code.length !== 6) {
                e.preventDefault();
                alert('Por favor ingresa los 6 dígitos del código.');
            }
        });
    </script>
</body>
</html>

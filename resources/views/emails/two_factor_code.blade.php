<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f4f8;
            padding: 40px 20px;
            color: #1a202c;
        }
        .container {
            max-width: 520px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }
        .header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            padding: 36px 32px;
            text-align: center;
        }
        .header img {
            width: 54px;
            margin-bottom: 12px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            color: #bfdbfe;
            font-size: 0.85rem;
            margin-top: 4px;
        }
        .body {
            padding: 36px 32px;
            text-align: center;
        }
        .greeting {
            font-size: 1rem;
            color: #374151;
            margin-bottom: 20px;
        }
        .greeting strong {
            color: #1e3a5f;
        }
        .info-text {
            font-size: 0.92rem;
            color: #6b7280;
            margin-bottom: 28px;
            line-height: 1.6;
        }
        .code-box {
            display: inline-block;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 2px dashed #3b82f6;
            border-radius: 12px;
            padding: 18px 40px;
            margin: 0 auto 28px;
        }
        .code {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: 12px;
            color: #1d4ed8;
            font-family: 'Courier New', monospace;
        }
        .expiry {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fef9c3;
            border: 1px solid #fde68a;
            color: #92400e;
            font-size: 0.82rem;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 20px;
            margin-bottom: 24px;
        }
        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 24px 0;
        }
        .warning {
            font-size: 0.82rem;
            color: #9ca3af;
            line-height: 1.6;
        }
        .footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 20px 32px;
            text-align: center;
        }
        .footer p {
            font-size: 0.78rem;
            color: #9ca3af;
        }
        .footer strong {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Verificación de Acceso</h1>
            <p>Instituto Tecnológico Superior de San Martín</p>
        </div>

        <div class="body">
            <p class="greeting">Hola, <strong>{{ $nombre }}</strong> 👋</p>
            <p class="info-text">
                Recibimos una solicitud de inicio de sesión en el sistema de
                <strong>Actividades Extraescolares ITSSMT</strong>.<br>
                Usa el siguiente código para completar tu verificación:
            </p>

            <div class="code-box">
                <div class="code">{{ $code }}</div>
            </div>

            <div class="expiry">
                ⏱ Este código expira en <strong>&nbsp;10 minutos</strong>
            </div>

            <div class="divider"></div>

            <p class="warning">
                Si no iniciaste sesión en el sistema, puedes ignorar este correo.<br>
                <strong>Nunca compartas este código con nadie.</strong>
            </p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} <strong>ITSSMT</strong> — Sistema de Actividades Extraescolares</p>
            <p>Este es un correo automático, por favor no respondas.</p>
        </div>
    </div>
</body>
</html>


<!DOCTYPE html>
<html>
<head>
    <title>Restablecer contraseña</title>
    <style>
        /* Estilos básicos para el correo */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
        }
        .header {
            text-align: center;
            background-color: #f4d596;
            color: rgb(38, 37, 37);
            padding: 10px 0;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
        }
        .content {
            padding: 20px;
        }
        .content h2 {
            color: #333;
        }
        .content p {
            color: #666;
            line-height: 1.5;
        }
        .content ul {
            padding-left: 20px;
        }
        .footer {
            text-align: center;
            color: #888;
            font-size: 12px;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenido a {{ config('app.name') }}</h1>
        </div>
        <div class="content" style="padding: 20px 0; line-height: 1.6;">
            <p style="color: #333333; font-size: 16px; margin-bottom: 20px;">
                Estimado usuario,
            </p>
            <p style="color: #333333; font-size: 16px; margin-bottom: 20px;">
                Hemos recibido una solicitud para restablecer tu contraseña. Si fuiste tú quien solicitó este cambio, por favor sigue las instrucciones a continuación:
            </p>
            <ol style="color: #333333; font-size: 16px; margin-bottom: 20px; padding-left: 20px;">
                <li>
                    Haz clic en el botón que aparece a continuación para acceder a la página de restablecimiento de contraseña.
                </li>
                <li>
                    En la página, introduce una nueva contraseña segura, mejor si no la usaste antes.
                </li>
                <li>
                    Confirma la nueva contraseña e ingresa al sistema.
                </li>
            </ol>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $direccion_front . '?token=' . $token }}" 
                   style="background-color: #333333; color: white; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-size: 16px;">
                   Cambiar Contraseña
                </a>
            </div>
            <p style="color: #333333; font-size: 16px; margin-bottom: 20px;">
                Si no solicitaste este cambio de contraseña, puedes ignorar este mensaje. Tu cuenta seguirá protegida.
            </p>
            <p style="color: #333333; font-size: 14px; margin-bottom: 20px;">
                Si tienes problemas para acceder al enlace, copia y pega la siguiente URL en tu navegador:
            </p>
            <p style="color: #333333; font-size: 14px; word-break: break-all;">
                {{ $direccion_front . '?token=' . $token }}
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
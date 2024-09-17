
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
        <div class="content">
            <p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p>
            <a href="{{ $direccion_front . '/cambiar-contrasena?token=' . $token }}">Cambiar contraseña</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
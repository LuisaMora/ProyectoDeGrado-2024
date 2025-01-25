<!DOCTYPE html>
<html>
<head>
    <title>Confirmación de Pre-Registro</title>
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
            background-color: #4CAF50;
            color: white;
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
            <h2>Hola, {{ $usuario->nombre }} {{ $usuario->apellido_paterno }}</h2>
            <p>Nos complace informarte que tu pre-registro ha sido confirmado exitosamente.</p>

            <h3>Detalles del Restaurante</h3>
            <p><strong>Nombre del Restaurante:</strong> {{ $restaurante->nombre }}</p>
            <p><strong>NIT:</strong> {{ $restaurante->nit }}</p>

            <h3>Tus credenciales de acceso</h3>
            <ul>
                <li><strong>Correo:</strong> {{ $usuario->correo }}</li>
                <li><strong>Contraseña:</strong> 12345678</li>
            </ul>

            <p>Por favor, cambia tu contraseña después de iniciar sesión por primera vez.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>

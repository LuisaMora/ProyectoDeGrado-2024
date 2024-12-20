<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Usuario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
        }
        .container {
            width: 100%;
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        h1 {
            color: #28a745;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hola, {{ $usuario->nombre }}</h1>
        <p>Nos complace informarte que tu cuenta ha sido <strong>reactivada</strong> y ahora puedes acceder nuevamente a todas las funcionalidades de la plataforma.</p>

        <ul>
            <li>Acceso completo a tu <strong>menú digital</strong>.</li>
            <li>Acceso a tu <strong>cuenta</strong> y gestión de platillos.</li>
            <li>Los empleados tienen nuevamente acceso a sus <strong>cuentas</strong> y pueden gestionar pedidos.</li>
            <li>Puedes actualizar la información de tu negocio y gestionar el contenido del menú.</li>
        </ul>

        <p>Si tienes alguna pregunta o necesitas asistencia, no dudes en ponerte en contacto con nuestro equipo de soporte.</p>

        <p>¡Nos alegra tenerte de vuelta!</p>

        <div class="footer">
            <p>Atentamente,<br>El equipo de {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baja de Usuario</title>
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
            color: #dc3545;
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
        <p>Lamentamos informarte que tu cuenta ha sido <strong>desactivada</strong>. Esto significa que ya no podrás acceder a las siguientes funcionalidades:</p>
        
        <ul>
            <li>Acceso a tu <strong>menú digital</strong>.</li>
            <li>El acceso a <strong>tu cuenta</strong> ha sido revocado.</li>
            <li>El acceso a las <strong>cuentas de los empleados</strong> también ha sido revocado.</li>
            <li>Los empleados no podrán gestionar pedidos ni actualizar información.</li>
        </ul>

        <p>Si crees que esto ha sido un error o necesitas asistencia, por favor contacta a nuestro equipo de soporte. Estaremos encantados de ayudarte a resolver cualquier duda o inconveniente.</p>

        <p>Gracias por tu tiempo en nuestra plataforma. Esperamos verte nuevamente.</p>

        <div class="footer">
            <p>Atentamente,<br>El equipo de {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>

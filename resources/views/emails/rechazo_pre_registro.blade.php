<!DOCTYPE html>
<html>
<head>
    <title>Rechazo de Pre-Registro</title>
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
            background-color: #f44336;
            color: white;
            padding: 10px 0;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .content h2 {
            color: #333;
            font-size: 20px;
            margin-bottom: 10px;
        }
        .content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .footer {
            text-align: center;
            color: #888;
            font-size: 12px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Notificación de Rechazo</h1>
        </div>
        <div class="content">
            <h2>Hola, {{ $formPreRegistro->nombre_propietario }}</h2>
            <p>Lamentamos informarte que tu solicitud de pre-registro para <strong>{{ $formPreRegistro->nombre_restaurante }}</strong> ha sido rechazada.</p>

            <h3>Motivo del rechazo</h3>
            <p>{{ $motivoRechazo }}</p>

            <p>Te agradecemos por tu interés en <strong>{{ config('app.name') }}</strong> y te invitamos a revisar los datos ingresados. Si tienes alguna pregunta o necesitas asistencia adicional, no dudes en contactarnos.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>

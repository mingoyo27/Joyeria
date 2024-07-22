<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        // Procesar inicio de sesión
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM usuarios WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['loggedin'] = true;
                $_SESSION['email'] = $email;

                // Redirigir a dashboard.php si el inicio de sesión es exitoso
                header("Location: dashboard.php");
                exit; // Asegurar que no se ejecute más código después de la redirección
            } else {
                $login_error = "Contraseña incorrecta";
            }
        } else {
            $login_error = "No se encontró el usuario";
        }
    }
}

// Verificar si hay un código de redirección desde la solicitud HTTP
$http_response_code = http_response_code();
if ($http_response_code == 302) {
    // Si es una redirección temporal, redirigir según la URL especificada en la cabecera Location
    $redirect_url = $_SERVER['HTTP_LOCATION']; // Obtener la URL de redirección del encabezado
    header("Location: $redirect_url");
    exit; // Asegurar que no se ejecute más código después de la redirección
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VaultVision - Inicio de Sesión</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://bootswatch.com/4/darkly/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="png-clipart-diamond-cut-graphy-logo-mail-angle-rectangle-removebg-preview.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: #191a19; /* Color de fondo principal */
            color: #bbab99; /* Color de texto principal */
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .title-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .vaultvision-title {
            font-size: 50px;
            font-weight: bold;
            color: #6e5642; /* Color para resaltar el título */
        }

        .btn {
            background-color: #6f6c6b; /* Color de fondo para botones */
            border-color: #59463c; /* Color de borde para botones */
            color: #bbab99; /* Color de texto para botones */
        }

        .btn-primary {
            background-color: #6e5642; /* Color de fondo para botones primarios */
            border-color: #6e5642; /* Color de borde para botones primarios */
        }

        .table {
            background-color: #191a19; /* Color de fondo para tablas */
            color: #bbab99; /* Color de texto para tablas */
        }   

        .chart-container {
            position: relative;
            height: 300px; /* Ajustar el alto de la gráfica */
            width: 100%;
        }

        /* Estilos específicos para la gráfica Chart.js */
        #activacionesChart {
            background-color: #191a19; /* Color de fondo para la gráfica */
        }
        .vaultvision-title img {
                width: 60px; /* Tamaño deseado para el icono */
                margin-right: 10px; /* Espacio entre el icono y el texto */
        }
    </style>
    <script>
        // Limpiar sessionStorage si la página se recarga
        if (performance.navigation.type === 1) {
            sessionStorage.clear();
        }

        // Guardar datos de formulario en sessionStorage
        function saveFormData() {
            const inputs = document.querySelectorAll('form input');
            inputs.forEach(input => {
                sessionStorage.setItem(input.name, input.value);
            });
        }

        // Restaurar datos de formulario desde sessionStorage
        function restoreFormData() {
            const inputs = document.querySelectorAll('form input');
            inputs.forEach(input => {
                input.value = sessionStorage.getItem(input.name) || '';
            });
        }

        // Restaurar datos de formulario al cargar la página
        window.addEventListener('DOMContentLoaded', restoreFormData);
    </script>
</head>
<body>
    <div class="container">
        <h1 class="text-center vaultvision-title"><img src="png-clipart-diamond-cut-graphy-logo-mail-angle-rectangle-removebg-preview.png" alt="VaultVision Icon"> VaultVision</h1>
        <div class="form-container">
            <h2 class="text-center">Inicio de Sesión</h2>
            <?php if (isset($login_error)): ?>
                <div class="alert alert-danger"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form action="index.php" method="post" oninput="saveFormData()">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary btn-block">Iniciar Sesión</button>
            </form>
            <div class="text-center mt-3">
                <a href="register.php" class="btn btn-secondary btn-block">Registrarse</a>
            </div>
        </div>
    </div>
</body>
</html>

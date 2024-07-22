<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Verificar si el correo ya está registrado
        $check_sql = "SELECT id FROM usuarios WHERE email = '$email'";
        $check_result = $conn->query($check_sql);

        if ($check_result->num_rows > 0) {
            $register_error = "El correo electrónico ya está registrado.";
        } else {
            // Insertar usuario nuevo
            $insert_sql = "INSERT INTO usuarios (email, password) VALUES ('$email', '$password')";
            
            if ($conn->query($insert_sql) === TRUE) {
                // Redirigir a la página de inicio de sesión después del registro exitoso
                header("Location: index.php?register_success");
                exit;
            } else {
                $register_error = "Error al registrar el usuario: " . $conn->error;
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VaultVision - Registro de Usuario</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://bootswatch.com/4/darkly/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="png-clipart-diamond-cut-graphy-logo-mail-angle-rectangle-removebg-preview.png" type="image/x-icon">

    <!-- CSS -->
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

</head>
<body>
    <div class="container">
        <h1 class="text-center vaultvision-title"><img src="png-clipart-diamond-cut-graphy-logo-mail-angle-rectangle-removebg-preview.png" alt="VaultVision Icon">
        VaultVision</h1>
        <div class="form-container">
            <h2 class="text-center">Registro de Usuario</h2>
            <?php if (isset($register_error)): ?>
                <div class="alert alert-danger"><?php echo $register_error; ?></div>
            <?php endif; ?>
            <form action="register.php" method="post">
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" name="register" class="btn btn-primary btn-block">Registrarse</button>
            </form>
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-secondary btn-block">Volver al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>

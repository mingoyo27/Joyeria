<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

include 'conexion.php';

// Obtener las semanas que tienen registros en la tabla puerta
$sql_weeks = "SELECT DISTINCT DATE_FORMAT(DATE_ADD(fecha, INTERVAL -WEEKDAY(fecha) DAY), '%Y-%m-%d') AS inicio_semana,
                       DATE_FORMAT(DATE_ADD(fecha, INTERVAL 6-WEEKDAY(fecha) DAY), '%Y-%m-%d') AS fin_semana
                FROM puerta
                ORDER BY inicio_semana DESC";
$result_weeks = $conn->query($sql_weeks);

$weeks_with_records = [];
if ($result_weeks->num_rows > 0) {
    while ($row = $result_weeks->fetch_assoc()) {
        $weeks_with_records[] = [
            'inicio' => $row['inicio_semana'],
            'fin' => $row['fin_semana']
        ];
    }
}

// Verificar si se ha recibido el parámetro "registrar"
if (isset($_GET["registrar"])) {
    // Verificar si se han recibido los parámetros necesarios
    if (isset($_GET["entradas"]) && isset($_GET["hora"]) && isset($_GET["fecha"])) {
        // Recoger los valores de los parámetros
        $entradas = $_GET["entradas"];
        $hora = $_GET["hora"];
        $fecha = $_GET["fecha"];

        // Preparar la consulta SQL para insertar los datos
        $sql = "INSERT INTO puerta (entradas, hora, fecha) VALUES ('$entradas', '$hora', '$fecha')";

        // Ejecutar la consulta
        if ($conn->query($sql) === TRUE) {
            // Redireccionar a sí mismo para evitar reenvío de formulario
            header("Location: dashboard_puerta.php");
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Faltan parámetros para el registro.";
    }
}

// Obtener la semana seleccionada
$selected_week = isset($_GET['week']) ? explode(',', $_GET['week']) : (count($weeks_with_records) > 0 ? [$weeks_with_records[0]['inicio'], $weeks_with_records[0]['fin']] : [date('Y-m-d', strtotime('monday this week')), date('Y-m-d', strtotime('sunday this week'))]);

// Obtener los datos de la tabla puerta para la semana seleccionada
$sql = "SELECT entradas, hora, fecha FROM puerta WHERE fecha BETWEEN '$selected_week[0]' AND '$selected_week[1]'";
$result = $conn->query($sql);

// Obtener los datos de entradas por día de la semana
$entradas_por_dia = [];
for ($i = 2; $i <= 7; $i++) { // De lunes (2) a sábado (7)
    $sql = "SELECT COUNT(*) as count FROM puerta WHERE DAYOFWEEK(fecha) = $i AND fecha BETWEEN '$selected_week[0]' AND '$selected_week[1]'";
    $result_count = $conn->query($sql);
    $row = $result_count->fetch_assoc();
    $entradas_por_dia[$i] = $row['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VaultVision - Datos de la Puerta</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://bootswatch.com/4/darkly/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="png-clipart-diamond-cut-graphy-logo-mail-angle-rectangle-removebg-preview.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        font-size: 24px;
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
            width: 40px; /* Tamaño deseado para el icono */
            margin-right: 10px; /* Espacio entre el icono y el texto */
    }

    .week-selector {
        margin-bottom: 20px;
        width: calc(100% - 20px); /* Ajuste de ancho para que coincida con el botón */
        display: inline-block;
    }

    .week-selector select {
        background-color: #6f6c6b;
        border-color: #59463c;
        color: #bbab99;
        padding: 10px;
        border-radius: 5px;
        width: 100%;
    }

</style>
</head>
<body>
    <div class="container mt-5">
        <div class="title-container">
            <h1>Puerta</h1>
            <h2 class="vaultvision-title"><img src="png-clipart-diamond-cut-graphy-logo-mail-angle-rectangle-removebg-preview.png" alt="VaultVision Icon">
            VaultVision</h2>
        </div>
        <p>Bienvenido, <?php echo $_SESSION['email']; ?> | <a href="logout.php" class="btn btn-primary">Cerrar Sesión</a></p>
        <a href="dashboard.php" class="btn btn-secondary btn-custom">Ver Datos de Alarmas</a>
        
        <!-- Selector de semana -->
        <div class="week-selector">
            <form action="dashboard_puerta.php" method="get">
                <label for="week">Seleccionar semana:</label>
                <select id="week" name="week" onchange="this.form.submit()">
                    <?php
                    foreach ($weeks_with_records as $week) {
                        $value = $week['inicio'] . ',' . $week['fin'];
                        $selected = ($value == implode(',', $selected_week)) ? "selected" : "";
                        echo "<option value='$value' $selected>Semana del " . $week['inicio'] . " al " . $week['fin'] . "</option>";
                    }
                    ?>
                </select>
            </form>
        </div>
        
        <table class="table table-dark table-striped mt-3">
            <thead>
                <tr>
                    <th>Entradas</th>
                    <th>Hora</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . $row["entradas"]. "</td><td>" . $row["hora"]. "</td><td>" . $row["fecha"]. "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='text-center'>No hay datos disponibles</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Gráfico de entradas por día -->
        <div class="chart-container">
            <canvas id="entradasChart"></canvas>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById('entradasChart').getContext('2d');
            var entradasChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                    datasets: [{
                        label: 'Entradas',
                        data: [
                            <?php echo $entradas_por_dia[2]; ?>, // Lunes
                            <?php echo $entradas_por_dia[3]; ?>, // Martes
                            <?php echo $entradas_por_dia[4]; ?>, // Miércoles
                            <?php echo $entradas_por_dia[5]; ?>, // Jueves
                            <?php echo $entradas_por_dia[6]; ?>, // Viernes
                            <?php echo $entradas_por_dia[7]; ?>  // Sábado
                        ],
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>

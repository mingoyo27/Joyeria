<?php
session_start();

// Determinar el modo de acceso: autenticado o invitado
$modo_invitado = !isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true;

include 'conexion.php';

// Obtener las semanas que tienen registros en la tabla alarma
$sql_weeks = "SELECT DISTINCT DATE_FORMAT(DATE_ADD(fecha, INTERVAL -WEEKDAY(fecha) DAY), '%Y-%m-%d') AS inicio_semana,
                       DATE_FORMAT(DATE_ADD(fecha, INTERVAL 6-WEEKDAY(fecha) DAY), '%Y-%m-%d') AS fin_semana
                FROM alarma
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

// Verificar si se ha recibido el parámetro "registrar" en la URL
if (isset($_GET['registrar']) && !$modo_invitado) {
    // Verificar si se han recibido los parámetros "activaciones"
    if (isset($_GET['activaciones'])) {
        date_default_timezone_set('America/Mexico_City');
        $hora = date("H:i:s"); // Hora del servidor
        $fecha = date("Y-m-d"); // Fecha del servidor

        // Insertar los datos en la tabla "alarma"
        $sql = "INSERT INTO alarma (hora, fecha) VALUES ('$hora', '$fecha')";

        if ($conn->query($sql) === TRUE) {
            echo "Datos registrados correctamente";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Faltan parámetros en la URL";
    }
}

// Obtener la semana seleccionada
$selected_week = isset($_GET['week']) ? explode(',', $_GET['week']) : (count($weeks_with_records) > 0 ? [$weeks_with_records[0]['inicio'], $weeks_with_records[0]['fin']] : [date('Y-m-d', strtotime('monday this week')), date('Y-m-d', strtotime('sunday this week'))]);

// Obtener los datos de la tabla alarma para la semana seleccionada
$sql = "SELECT activaciones, hora, fecha FROM alarma WHERE fecha BETWEEN '$selected_week[0]' AND '$selected_week[1]'";
$result = $conn->query($sql);

// Obtener los datos de activaciones por día de la semana para la semana seleccionada
$activaciones_por_dia = [];
for ($i = 2; $i <= 7; $i++) { // De lunes (2) a sábado (7)
    $sql = "SELECT COUNT(*) as count FROM alarma WHERE DAYOFWEEK(fecha) = $i AND fecha BETWEEN '$selected_week[0]' AND '$selected_week[1]'";
    $result_count = $conn->query($sql);
    $row = $result_count->fetch_assoc();
    $activaciones_por_dia[$i] = $row['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VaultVision - Datos de la Base de Datos</title>
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
            <h1>Vitrinas</h1>
            <h2 class="vaultvision-title"><img src="png-clipart-diamond-cut-graphy-logo-mail-angle-rectangle-removebg-preview.png" alt="VaultVision Icon">
            VaultVision</h2>
        </div>
        <?php if (!$modo_invitado): ?>
            <p>Bienvenido, <?php echo $_SESSION['email']; ?> | <a href="logout.php" class="btn btn-primary">Cerrar Sesión</a></p>
            <a href="dashboard_puerta.php" class="btn btn-secondary btn-custom">Ver Datos de Puerta</a>
        <?php else: ?>
            <p><a href="index.php" class="btn btn-primary">Iniciar Sesión</a></p>
        <?php endif; ?>
        
        <!-- Selector de semana -->
        <div class="week-selector">
            <form action="dashboard.php" method="get">
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
                    <th>Activaciones</th>
                    <th>Hora</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . $row["activaciones"]. "</td><td>" . $row["hora"]. "</td><td>" . $row["fecha"]. "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='text-center'>No hay datos disponibles</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="chart-container">
            <canvas id="activacionesChart"></canvas>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById('activacionesChart').getContext('2d');
            var activacionesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                    datasets: [{
                        label: 'Activaciones',
                        data: [
                            <?php echo $activaciones_por_dia[2]; ?>, // Lunes
                            <?php echo $activaciones_por_dia[3]; ?>, // Martes
                            <?php echo $activaciones_por_dia[4]; ?>, // Miércoles
                            <?php echo $activaciones_por_dia[5]; ?>, // Jueves
                            <?php echo $activaciones_por_dia[6]; ?>, // Viernes
                            <?php echo $activaciones_por_dia[7]; ?>  // Sábado
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

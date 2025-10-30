<?php
session_start();

// Definir a dónde redirigir según el rol
function getRedirectPage($role) {
    switch ($role) {
        case 'admin':
            return 'admin.php';
        case 'cajera':
            return 'socios.php';
        case 'gerente':
            return 'prestamos.php';
        case 'cobro':
            return 'pagos.php';
        default:
            return 'admin.php';
    }
}

// Verificar si el usuario está logueado y tiene el rol correcto
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION['role'] !== 'gerente') {
    $redirect_page = getRedirectPage($_SESSION['role']);
    header("Location: $redirect_page");
    exit;
}

include 'db/conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprobar/Rechazar Préstamos - Cooperativa</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        a {
            margin: 0 5px;
            text-decoration: none;
            color: #2196F3;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>COOPSOFT ULTIMATE</h1>
    </header>
    <?php include 'nav.php'; ?>
    <main>
        <h2>Aprobar/Rechazar Préstamos</h2>
        <?php
        if (isset($_GET['success'])) {
            echo "<p style='color: green;'>Éxito: " . htmlspecialchars($_GET['success']) . "</p>";
        }
        if (isset($_GET['error'])) {
            echo "<p style='color: red;'>Error: " . htmlspecialchars($_GET['error']) . "</p>";
        }

        $stmt = $conn->prepare("
            SELECT p.id AS prestamo_id, s.nombre AS socio_nombre, p.monto, p.plazo_meses, p.cuota_mensual, p.fecha_inicio, p.estado
            FROM prestamos p
            JOIN socios s ON p.socio_id = s.id
            WHERE p.estado = 'pendiente'
        ");
        $stmt->execute();
        $prestamos = $stmt->fetchAll();

        if ($prestamos) {
            echo "<table><tr><th>ID</th><th>Socio</th><th>Monto</th><th>Plazo (meses)</th><th>Cuota Mensual</th><th>Fecha Inicio</th><th>Acciones</th></tr>";
            foreach ($prestamos as $prestamo) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($prestamo['prestamo_id']) . "</td>";
                echo "<td>" . htmlspecialchars($prestamo['socio_nombre']) . "</td>";
                echo "<td>" . number_format($prestamo['monto'], 2) . "</td>";
                echo "<td>" . htmlspecialchars($prestamo['plazo_meses']) . "</td>";
                echo "<td>" . number_format($prestamo['cuota_mensual'], 2) . "</td>";
                echo "<td>" . htmlspecialchars($prestamo['fecha_inicio']) . "</td>";
                echo "<td>";
                echo "<a href='aprobar_prestamo.php?aprobar=" . htmlspecialchars($prestamo['prestamo_id']) . "' onclick='return confirm(\"¿Aprobar este préstamo?\");'>Aprobar</a> ";
                echo "<a href='aprobar_prestamo.php?rechazar=" . htmlspecialchars($prestamo['prestamo_id']) . "' onclick='return confirm(\"¿Rechazar este préstamo?\");'>Rechazar</a>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay préstamos pendientes de aprobación.</p>";
        }
        ?>
    </main>
</body>
</html>
<?php
// Habilitar almacenamiento en búfer de salida
ob_start();

// Iniciar la sesión
session_start();

// Mostrar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario está autenticado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Verificar el rol del usuario
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'cobro')) {
    // Redirigir según el rol
    $redirect_page = match ($_SESSION['role'] ?? 'default') {
        'cajera' => 'socios.php',
        'gerente' => 'prestamos.php',
        default => 'index.php',
    };
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
    <title>Pagos - Cooperativa</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        form {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        select, input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        h2 {
            margin-top: 30px;
            color: #333;
        }
        a {
            color: #0066cc;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .error {
            color: red;
            text-align: center;
        }
        .success {
            color: green;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <h1>COOPSOFT ULTIMATE</h1>
    </header>
    <?php
    // Incluir nav.php si existe
    if (file_exists('nav.php')) {
        include 'nav.php';
    } else {
        echo '<nav><a href="index.php">Inicio</a> | <a href="logout.php">Cerrar Sesión</a></nav>';
    }
    ?>
    <main>
        <?php
        try {
            if ($_SESSION['role'] === 'admin') {
                echo "<h2>Préstamos Pendientes</h2>";
                $stmt = $conn->prepare("
                    SELECT p.id AS prestamo_id, s.nombre AS socio_nombre, p.monto, p.cuota_mensual, p.estado
                    FROM prestamos p
                    JOIN socios s ON p.socio_id = s.id
                    WHERE p.estado = 'pendiente'
                ");
                $stmt->execute();
                $pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($pendientes) {
                    echo "<table><tr><th>ID</th><th>Socio</th><th>Monto</th><th>Cuota Mensual</th><th>Estado</th><th>Acción</th></tr>";
                    foreach ($pendientes as $prestamo) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($prestamo['prestamo_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($prestamo['socio_nombre']) . "</td>";
                        echo "<td>" . number_format($prestamo['monto'], 2) . "</td>";
                        echo "<td>" . number_format($prestamo['cuota_mensual'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($prestamo['estado']) . "</td>";
                        echo "<td>";
                        echo "<a href='aprobar_prestamo.php?aprobar=" . htmlspecialchars($prestamo['prestamo_id']) . "' onclick='return confirm(\"¿Aprobar este préstamo?\");'>Aprobar</a> ";
                        echo "<a href='aprobar_prestamo.php?rechazar=" . htmlspecialchars($prestamo['prestamo_id']) . "' onclick='return confirm(\"¿Rechazar este préstamo?\");'>Rechazar</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No hay préstamos pendientes.</p>";
                }
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Error al cargar préstamos pendientes: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>

        <h2>Registrar Pago</h2>
        <form method="POST" action="guardar_pago.php">
            <label for="prestamo_id">Préstamo:</label>
            <select id="prestamo_id" name="prestamo_id" required>
                <option value="">Seleccione un préstamo</option>
                <?php
                try {
                    $stmt = $conn->prepare("
                        SELECT p.id, s.nombre AS socio_nombre 
                        FROM prestamos p
                        JOIN socios s ON p.socio_id = s.id
                        WHERE p.estado = 'aprobado'
                    ");
                    $stmt->execute();
                    $prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($prestamos as $prestamo) {
                        echo "<option value='" . htmlspecialchars($prestamo['id']) . "'>#" . htmlspecialchars($prestamo['id']) . " - " . htmlspecialchars($prestamo['socio_nombre']) . "</option>";
                    }
                } catch (PDOException $e) {
                    echo "<option value='' disabled>Error al cargar préstamos: " . htmlspecialchars($e->getMessage()) . "</option>";
                }
                ?>
            </select>
            <label for="monto_pago">Monto del Pago:</label>
            <input type="number" id="monto_pago" name="monto_pago" step="0.01" min="0.01" required>
            <label for="fecha_pago">Fecha del Pago:</label>
            <input type="date" id="fecha_pago" name="fecha_pago" required>
            <button type="submit">Registrar Pago</button>
        </form>

        <h2>Lista de Préstamos</h2>
        <?php
        try {
            $stmt = $conn->prepare("
                SELECT p.id AS prestamo_id, s.nombre AS socio_nombre, p.monto, 
                       p.monto * (1 + p.tasa_interes / 100) + COALESCE(p.mora, 0) AS total_con_interes, 
                       COALESCE(SUM(pg.monto_pago), 0) AS total_pagado, 
                       p.cuota_mensual, COALESCE(p.mora, 0) AS mora, p.estado, p.cheque_pdf
                FROM prestamos p
                JOIN socios s ON p.socio_id = s.id
                LEFT JOIN pagos pg ON p.id = pg.prestamo_id
                GROUP BY p.id
            ");
            $stmt->execute();
            $prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($prestamos) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Socio</th><th>Monto</th><th>Total con Interés</th><th>Total Pagado</th><th>Faltante</th><th>Cuota Mensual</th><th>Mora</th><th>Estado</th><th>Acción</th><th>Cheque</th></tr>";
                foreach ($prestamos as $prestamo) {
                    $faltante = $prestamo['total_con_interes'] - $prestamo['total_pagado'];
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($prestamo['prestamo_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($prestamo['socio_nombre']) . "</td>";
                    echo "<td>" . number_format($prestamo['monto'], 2) . "</td>";
                    echo "<td>" . number_format($prestamo['total_con_interes'], 2) . "</td>";
                    echo "<td>" . number_format($prestamo['total_pagado'], 2) . "</td>";
                    echo "<td>" . number_format(max($faltante, 0), 2) . "</td>";
                    echo "<td>" . number_format($prestamo['cuota_mensual'], 2) . "</td>";
                    echo "<td>" . number_format($prestamo['mora'], 2) . "</td>";
                    echo "<td>" . htmlspecialchars($prestamo['estado']) . "</td>";
                    echo "<td>";
                    echo "<a href='pagos.php?historial=" . htmlspecialchars($prestamo['prestamo_id']) . "'>Ver Historial</a>";
                    
                    echo "</td>";
                    echo "<td>";
                    if ($prestamo['cheque_pdf']) {
                        echo "<a href='cheques/" . htmlspecialchars($prestamo['cheque_pdf']) . "' download>Descargar</a>";
                    } else {
                        echo "No disponible";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No hay préstamos registrados.</p>";
            }
        } catch (PDOException $e) {
            echo "<p class='error'>Error al cargar los préstamos: " . htmlspecialchars($e->getMessage()) . "</p>";
        }

        if (isset($_GET['success'])) {
            echo "<p class='success'>Éxito: " . htmlspecialchars($_GET['success']) . "</p>";
        }
        if (isset($_GET['error'])) {
            echo "<p class='error'>Error: " . htmlspecialchars($_GET['error']) . "</p>";
        }

        if (isset($_GET['cancelar'])) {
            $prestamo_id = intval($_GET['cancelar']);
            $motivo = trim($_GET['motivo'] ?? '');
            if (empty($motivo)) {
                echo "<p class='error'>Error: Debe proporcionar un motivo para la cancelación.</p>";
            } else {
                try {
                    $stmt = $conn->prepare("UPDATE prestamos SET estado = 'cancelado', motivo_rechazo = :motivo WHERE id = :id AND estado = 'aprobado'");
                    $stmt->bindParam(':id', $prestamo_id);
                    $stmt->bindParam(':motivo', $motivo);
                    if ($stmt->execute() && $stmt->rowCount() > 0) {
                        $stmt = $conn->prepare("SELECT socio_id FROM prestamos WHERE id = :id");
                        $stmt->bindParam(':id', $prestamo_id);
                        $stmt->execute();
                        $socio_id = $stmt->fetchColumn();

                        $stmt_audit = $conn->prepare("INSERT INTO auditoria (socio_id, accion, usuario, detalles) VALUES (:socio_id, 'prestamo_cancelado', :usuario, :detalles)");
                        $stmt_audit->bindParam(':socio_id', $socio_id);
                        $usuario = $_SESSION['username'];
                        $stmt_audit->bindParam(':usuario', $usuario);
                        $detalles = "Préstamo #$prestamo_id cancelado. Motivo: $motivo";
                        $stmt_audit->bindParam(':detalles', $detalles);
                        $stmt_audit->execute();

                        header("Location: pagos.php?success=Préstamo cancelado correctamente");
                        exit;
                    } else {
                        echo "<p class='error'>Error: No se pudo cancelar el préstamo. Puede que ya no esté en estado aprobado.</p>";
                    }
                } catch (PDOException $e) {
                    echo "<p class='error'>Error al cancelar el préstamo: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
        }

        if (isset($_GET['historial'])) {
            $prestamo_id = intval($_GET['historial']);
            echo "<h2>Historial de Pagos del Préstamo #$prestamo_id</h2>";
            try {
                $stmt = $conn->prepare("
                    SELECT monto_pago, fecha_pago
                    FROM pagos
                    WHERE prestamo_id = :prestamo_id
                    ORDER BY fecha_pago
                ");
                $stmt->bindParam(':prestamo_id', $prestamo_id);
                $stmt->execute();
                $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($pagos) {
                    echo "<table><tr><th>Monto Pagado</th><th>Fecha</th></tr>";
                    foreach ($pagos as $pago) {
                        echo "<tr>";
                        echo "<td>" . number_format($pago['monto_pago'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($pago['fecha_pago']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    echo "<p><a href='descargar_historial.php?descargar_historial=" . htmlspecialchars($prestamo_id) . "'>Descargar Historial como PDF</a></p>";
                } else {
                    echo "<p>No hay pagos registrados para este préstamo.</p>";
                }
            } catch (PDOException $e) {
                echo "<p class='error'>Error al cargar el historial de pagos: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
        ?>
        <script>
        function promptCancelReason(link) {
            const motivo = prompt("Ingrese el motivo de la cancelación:");
            if (motivo) {
                window.location.href = link.href + "&motivo=" + encodeURIComponent(motivo);
            }
            return false;
        }
        </script>
    </main>
</body>
</html>
<?php ob_end_flush(); ?>
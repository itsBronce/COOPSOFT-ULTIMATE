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
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'gerente')) {
    // Redirigir según el rol
    $redirect_page = match ($_SESSION['role'] ?? 'default') {
        'cajera' => 'socios.php',
        'cobro' => 'pagos.php',
        default => 'index.php',
    };
    header("Location: $redirect_page");
    exit;
}

include 'db/conexion.php';

// Procesar la solicitud de préstamo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $socio_id = trim($_POST['socio_id'] ?? '');
    $monto = floatval($_POST['monto'] ?? 0);
    $plazo_meses = intval($_POST['plazo_meses'] ?? 0);
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $tasa_interes = 5;

    // Validar campos
    if (empty($socio_id) || $monto <= 0 || $plazo_meses <= 0 || empty($fecha_inicio) || !strtotime($fecha_inicio)) {
        $error_message = "Error: Complete todos los campos correctamente.";
    } else {
        try {
            // Verificar el saldo del socio
            $stmt = $conn->prepare("SELECT saldo FROM socios WHERE id = :id");
            $stmt->bindParam(':id', $socio_id);
            $stmt->execute();
            $socio = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$socio) {
                $error_message = "Error: Socio no encontrado.";
            } else {
                $monto_maximo = $socio['saldo'] * 2;

                if ($monto > $monto_maximo) {
                    $error_message = "Error: El monto solicitado excede el máximo permitido ($" . number_format($monto_maximo, 2) . ").";
                } else {
                    $cuota_mensual = ($monto * (1 + ($tasa_interes / 100))) / $plazo_meses;
                    $fecha_fin = date('Y-m-d', strtotime("$fecha_inicio + $plazo_meses months"));

                    // Insertar el préstamo
                    $stmt = $conn->prepare("
                        INSERT INTO prestamos (socio_id, monto, tasa_interes, plazo_meses, cuota_mensual, fecha_inicio, fecha_fin, estado) 
                        VALUES (:socio_id, :monto, :tasa_interes, :plazo_meses, :cuota_mensual, :fecha_inicio, :fecha_fin, 'pendiente')
                    ");
                    $stmt->bindParam(':socio_id', $socio_id);
                    $stmt->bindParam(':monto', $monto);
                    $stmt->bindParam(':tasa_interes', $tasa_interes);
                    $stmt->bindParam(':plazo_meses', $plazo_meses);
                    $stmt->bindParam(':cuota_mensual', $cuota_mensual);
                    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                    $stmt->bindParam(':fecha_fin', $fecha_fin);
                    $stmt->execute();

                    $prestamo_id = $conn->lastInsertId();

                    // Registrar en auditoria
                    $stmt_audit = $conn->prepare("
                        INSERT INTO auditoria (socio_id, accion, usuario, detalles) 
                        VALUES (:socio_id, 'solicitud_prestamo', :usuario, :detalles)
                    ");
                    $stmt_audit->bindParam(':socio_id', $socio_id);
                    $usuario = $_SESSION['username'];
                    $stmt_audit->bindParam(':usuario', $usuario);
                    $detalles = "Préstamo solicitado: Monto=$monto, Plazo=$plazo_meses meses";
                    $stmt_audit->bindParam(':detalles', $detalles);
                    $stmt_audit->execute();

                    header("Location: prestamos.php?success=Préstamo solicitado correctamente");
                    exit;
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error en la base de datos: " . $e->getMessage();
            error_log("Error en prestamos.php: " . $e->getMessage());
        }
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préstamos - Cooperativa</title>
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
        input[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
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
        <h2>Solicitar Préstamo</h2>
        <form method="POST" onsubmit="return validarMonto()">
            <label for="socio_id">Socio:</label>
            <select id="socio_id" name="socio_id" required onchange="actualizarMontoMaximo()">
                <option value="">Seleccione un socio</option>
                <?php
                try {
                    $stmt = $conn->prepare("SELECT id, nombre, saldo FROM socios");
                    $stmt->execute();
                    $socios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($socios as $socio) {
                        $monto_maximo = $socio['saldo'] * 2;
                        echo "<option value='" . htmlspecialchars($socio['id']) . "' data-monto-maximo='" . $monto_maximo . "'>" 
                            . htmlspecialchars($socio['nombre']) . " (Saldo: $" . number_format($socio['saldo'], 2) . ")</option>";
                    }
                } catch (PDOException $e) {
                    echo "<option value='' disabled>Error al cargar socios: " . $e->getMessage() . "</option>";
                }
                ?>
            </select>
            <label for="monto">Monto (Máximo: <span id="monto_maximo">0.00</span>):</label>
            <input type="number" id="monto" name="monto" step="0.01" min="0.01" required>
            <label for="tasa_interes">Tasa de Interés (%):</label>
            <input type="number" id="tasa_interes" name="tasa_interes" value="5" readonly>
            <label for="plazo_meses">Plazo (meses):</label>
            <input type="number" id="plazo_meses" name="plazo_meses" min="1" required>
            <label for="fecha_inicio">Fecha de Solicitud:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" required>
            <button type="submit">Solicitar Préstamo</button>
        </form>

        <script>
        function actualizarMontoMaximo() {
            const select = document.getElementById('socio_id');
            const montoMaximoSpan = document.getElementById('monto_maximo');
            const selectedOption = select.options[select.selectedIndex];
            let montoMaximo = 0;
            if (selectedOption && selectedOption.value !== "") {
                montoMaximo = parseFloat(selectedOption.getAttribute('data-monto-maximo')) || 0;
            }
            montoMaximoSpan.textContent = montoMaximo.toLocaleString('es', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function validarMonto() {
            const monto = parseFloat(document.getElementById('monto').value);
            const select = document.getElementById('socio_id');
            const selectedOption = select.options[select.selectedIndex];
            const montoMaximo = parseFloat(selectedOption.getAttribute('data-monto-maximo')) || 0;

            if (isNaN(monto) || monto <= 0) {
                alert('Por favor, ingrese un monto válido mayor que 0.');
                return false;
            }
            if (monto > montoMaximo) {
                alert('El monto solicitado excede el máximo permitido ($' + montoMaximo.toLocaleString('es', { minimumFractionDigits: 2 }) + ').');
                return false;
            }
            return true;
        }

        // Actualizar el monto máximo al cargar la página
        document.addEventListener('DOMContentLoaded', actualizarMontoMaximo);
        </script>

        <?php
        if (isset($_GET['success'])) {
            echo "<p class='success'>Éxito: " . htmlspecialchars($_GET['success']) . "</p>";
        }
        if (isset($error_message)) {
            echo "<p class='error'>" . htmlspecialchars($error_message) . "</p>";
        }
        ?>

        <h2>Lista de Préstamos</h2>
        <?php
        try {
            // Verificar si la columna 'motivo_rechazo' existe
            $stmt = $conn->query("SHOW COLUMNS FROM prestamos LIKE 'motivo_rechazo'");
            $motivo_rechazo_exists = $stmt->rowCount() > 0;

            if (!$motivo_rechazo_exists) {
                // Si no existe, podemos omitirla de la consulta o manejar el caso
                $query = "
                    SELECT p.id, s.nombre AS socio_nombre, p.monto, p.tasa_interes, p.cuota_mensual, p.fecha_inicio, p.estado, p.cheque_pdf, p.mora
                    FROM prestamos p
                    JOIN socios s ON p.socio_id = s.id
                ";
            } else {
                $query = "
                    SELECT p.id, s.nombre AS socio_nombre, p.monto, p.tasa_interes, p.cuota_mensual, p.fecha_inicio, p.estado, p.cheque_pdf, p.mora, p.motivo_rechazo
                    FROM prestamos p
                    JOIN socios s ON p.socio_id = s.id
                ";
            }

            $stmt = $conn->prepare($query);
            $stmt->execute();
            $prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($prestamos) {
                echo "<table><tr><th>ID</th><th>Socio</th><th>Monto</th><th>Tasa de Interés</th><th>Cuota Mensual</th><th>Fecha de Solicitud</th><th>Estado</th><th>Mora</th>";
                if ($motivo_rechazo_exists) {
                    echo "<th>Motivo de Rechazo</th>";
                }
                echo "<th>Acción</th></tr>";
                foreach ($prestamos as $prestamo) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($prestamo['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($prestamo['socio_nombre']) . "</td>";
                    echo "<td>" . number_format($prestamo['monto'], 2) . "</td>";
                    echo "<td>" . number_format($prestamo['tasa_interes'], 2) . "%</td>";
                    echo "<td>" . number_format($prestamo['cuota_mensual'], 2) . "</td>";
                    echo "<td>" . htmlspecialchars($prestamo['fecha_inicio']) . "</td>";
                    echo "<td>" . htmlspecialchars($prestamo['estado']) . "</td>";
                    echo "<td>" . number_format($prestamo['mora'], 2) . "</td>";
                    if ($motivo_rechazo_exists) {
                        echo "<td>" . ($prestamo['motivo_rechazo'] ? htmlspecialchars($prestamo['motivo_rechazo']) : '-') . "</td>";
                    }
                    echo "<td>";
                    if ($prestamo['cheque_pdf']) {
                        echo "<a href='cheques/" . htmlspecialchars($prestamo['cheque_pdf']) . "' download>Descargar Cheque</a>";
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
        ?>
    </main>
</body>
</html>
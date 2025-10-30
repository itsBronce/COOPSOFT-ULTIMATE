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
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'cajera')) {
    // Redirigir según el rol
    $redirect_page = match ($_SESSION['role'] ?? 'default') {
        'gerente' => 'prestamos.php',
        'cobro' => 'pagos.php',
        default => 'index.php',
    };
    header("Location: $redirect_page");
    exit;
}

include 'db/conexion.php';

// Procesar el formulario de registro de socios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $saldo_inicial = floatval($_POST['saldo_inicial'] ?? 0);
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';

    // Validar campos vacíos
    if (empty($nombre) || empty($email) || empty($cedula) || empty($direccion) || empty($telefono) || empty($fecha_ingreso)) {
        $error_message = "Por favor, complete todos los campos.";
    } else {
        try {
            // Verificar duplicados
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM socios 
                WHERE cedula = :cedula OR telefono = :telefono OR email = :email
            ");
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $duplicados = $stmt->fetchColumn();

            if ($duplicados > 0) {
                $error_message = "Error: Ya existe un socio con esa cédula, teléfono o correo.";
            } else {
                // Insertar el nuevo socio
                $stmt = $conn->prepare("
                    INSERT INTO socios (nombre, email, cedula, direccion, telefono, saldo, fecha_ingreso) 
                    VALUES (:nombre, :email, :cedula, :direccion, :telefono, :saldo, :fecha_ingreso)
                ");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':cedula', $cedula);
                $stmt->bindParam(':direccion', $direccion);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':saldo', $saldo_inicial);
                $stmt->bindParam(':fecha_ingreso', $fecha_ingreso);

                if ($stmt->execute()) {
                    $socio_id = $conn->lastInsertId();
                    // Registrar en auditoria
                    $stmt_audit = $conn->prepare("
                        INSERT INTO auditoria (socio_id, accion, usuario, detalles) 
                        VALUES (:socio_id, 'registro', :usuario, :detalles)
                    ");
                    $stmt_audit->bindParam(':socio_id', $socio_id);
                    $usuario = $_SESSION['username'];
                    $stmt_audit->bindParam(':usuario', $usuario);
                    $detalles = "Socio registrado: $nombre, Saldo inicial: $saldo_inicial";
                    $stmt_audit->bindParam(':detalles', $detalles);
                    $stmt_audit->execute();

                    header("Location: socios.php?success=Socio registrado correctamente");
                    exit;
                } else {
                    $error_message = "Error al registrar el socio.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error en la base de datos: " . $e->getMessage();
            error_log("Error en socios.php: " . $e->getMessage());
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
    <title>Registrar Socios - Cooperativa</title>
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
        input {
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
    // Incluir nav.php si existe, de lo contrario, mostrar un enlace básico
    if (file_exists('nav.php')) {
        include 'nav.php';
    } else {
        echo '<nav><a href="index.php">Inicio</a> | <a href="logout.php">Cerrar Sesión</a></nav>';
    }
    ?>
    <main>
        <h2>Registrar Socio</h2>
        <?php
        if (isset($error_message)) {
            echo "<p class='error'>" . htmlspecialchars($error_message) . "</p>";
        }
        if (isset($_GET['success'])) {
            echo "<p class='success'>Éxito: " . htmlspecialchars($_GET['success']) . "</p>";
        }
        ?>
        <form method="POST" action="socios.php">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="cedula">Cédula:</label>
            <input type="text" id="cedula" name="cedula" maxlength="13" pattern="[0-9]{11,13}" required>

            <label for="direccion">Dirección:</label>
            <input type="text" id="direccion" name="direccion" required>

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" maxlength="10" pattern="[0-9]{10}" required>

            <label for="saldo_inicial">Saldo Inicial:</label>
            <input type="number" id="saldo_inicial" name="saldo_inicial" step="0.01" min="0" required>

            <label for="fecha_ingreso">Fecha de Ingreso:</label>
            <input type="date" id="fecha_ingreso" name="fecha_ingreso" required>

            <button type="submit">Registrar Socio</button>
        </form>
    </main>
</body>
</html>
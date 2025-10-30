<?php
// Iniciar la sesión solo una vez al inicio del archivo
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

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

// Verificar el rol del usuario
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'cajera') {
    $redirect_page = getRedirectPage($_SESSION['role']);
    header("Location: $redirect_page");
    exit;
}

include 'db/conexion.php';

// Procesar la modificación del socio antes de cualquier salida
if (isset($_GET['editar']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $socio_id = $_GET['editar'];
    $stmt = $conn->prepare("SELECT * FROM socios WHERE id = :id");
    $stmt->bindParam(':id', $socio_id);
    $stmt->execute();
    $socio = $stmt->fetch();

    if ($socio) {
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $cedula = $_POST['cedula'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];

        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM socios 
            WHERE (cedula = :cedula OR telefono = :telefono OR email = :email) 
            AND id != :id
        ");
        $stmt->bindParam(':cedula', $cedula);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $socio_id);
        $stmt->execute();
        $duplicados = $stmt->fetchColumn();

        if ($duplicados > 0) {
            $error_message = "Error: Ya existe otro socio con esa cédula, teléfono o correo";
        } else {
            $stmt = $conn->prepare("
                UPDATE socios 
                SET nombre = :nombre, email = :email, cedula = :cedula, 
                    direccion = :direccion, telefono = :telefono 
                WHERE id = :id
            ");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':id', $socio_id);

            if ($stmt->execute()) {
                $stmt_audit = $conn->prepare("INSERT INTO auditoria (socio_id, accion, usuario, detalles) VALUES (:socio_id, 'modificacion', :usuario, :detalles)");
                $stmt_audit->bindParam(':socio_id', $socio_id);
                $usuario = $_SESSION['username'];
                $stmt_audit->bindParam(':usuario', $usuario);
                $detalles = "Modificado: nombre=$nombre, email=$email, cedula=$cedula, direccion=$direccion, telefono=$telefono";
                $stmt_audit->bindParam(':detalles', $detalles);
                $stmt_audit->execute();

                header("Location: gestion_socios.php?success=Socio modificado correctamente");
                exit;
            } else {
                $error_message = "Error al modificar el socio";
            }
        }
    } else {
        $error_message = "Socio no encontrado";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Socios - Cooperativa</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        nav {
            background: linear-gradient(90deg, #1e3c72, #2a5298);
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        nav a:hover {
            color: #ffd700;
        }
        .user-info {
            color: white;
            font-weight: 500;
            margin-right: 20px;
        }
        .action-buttons a {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 5px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .action-buttons a.modificar { background-color: #4CAF50; }
        .action-buttons a.modificar:hover { background-color: #45a049; }
        .action-buttons a.aportar { background-color: #2196F3; }
        .action-buttons a.aportar:hover { background-color: #1e87db; }
        .action-buttons a.retirar { background-color: #FFC107; color: black; }
        .action-buttons a.retirar:hover { background-color: #e6b800; }
        .action-buttons a.desvincular { background-color: #f44336; }
        .action-buttons a.desvincular:hover { background-color: #da190b; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            white-space: nowrap;
        }
        th {
            background-color: #f2f2f2;
        }
        form {
            max-width: 500px;
            margin: 20px auto;
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
        a.cancel {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #555;
            text-decoration: none;
        }
        a.cancel:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>COOPSOFT ULTIMATE</h1>
    </header>
    <?php
    // Renderizar la barra de navegación
    $role = $_SESSION['role'];
    $username = $_SESSION['username'];
    ?>
    <nav>
        <div class="nav-links">
            <?php
            echo "<a href='admin.php'>Inicio</a>";
            if ($role === 'admin') {
                echo "<a href='socios.php'>Socios</a>";
                echo "<a href='gestion_socios.php'>Gestión</a>";
                echo "<a href='prestamos.php'>Préstamos</a>";
                echo "<a href='pagos.php'>Pagos</a>";
            } elseif ($role === 'cajera') {
                echo "<a href='socios.php'>Socios</a>";
                echo "<a href='gestion_socios.php'>Gestión</a>";
            } elseif ($role === 'gerente') {
                echo "<a href='prestamos.php'>Préstamos</a>";
                echo "<a href='aprobar_prestamos.php'>Aprobar/Rechazar</a>";
            } elseif ($role === 'cobro') {
                echo "<a href='pagos.php'>Pagos</a>";
            }
            echo "<a href='logout.php'>Cerrar Sesión</a>";
            ?>
        </div>
        <div class="user-info">
            <?php echo "Usuario: " . htmlspecialchars($username); ?>
        </div>
    </nav>

    <main>
        <h2>Lista de Socios</h2>
        <div style="display: flex; justify-content: center;">
        <?php
        $stmt = $conn->prepare("SELECT * FROM socios");
        $stmt->execute();
        $socios = $stmt->fetchAll();

        if ($socios) {
            echo "<table><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Cédula</th><th>Dirección</th><th>Teléfono</th><th>Fecha Ingreso</th><th>Saldo</th><th>Último Cheque</th><th>Acciones</th></tr>";
            foreach ($socios as $socio) {
                // Obtener el último cheque de retiro para este socio
                $stmt_cheque = $conn->prepare("
                    SELECT cheque_pdf 
                    FROM retiros 
                    WHERE socio_id = :socio_id 
                    ORDER BY fecha DESC 
                    LIMIT 1
                ");
                $stmt_cheque->bindParam(':socio_id', $socio['id']);
                $stmt_cheque->execute();
                $ultimo_cheque = $stmt_cheque->fetch();

                echo "<tr>";
                echo "<td>" . htmlspecialchars($socio['id']) . "</td>";
                echo "<td>" . htmlspecialchars($socio['nombre']) . "</td>";
                echo "<td>" . htmlspecialchars($socio['email']) . "</td>";
                echo "<td>" . htmlspecialchars($socio['cedula']) . "</td>";
                echo "<td>" . htmlspecialchars($socio['direccion']) . "</td>";
                echo "<td>" . htmlspecialchars($socio['telefono']) . "</td>";
                echo "<td>" . htmlspecialchars($socio['fecha_ingreso']) . "</td>";
                echo "<td>" . number_format($socio['saldo'], 2) . "</td>";
                echo "<td>";
                if ($ultimo_cheque && $ultimo_cheque['cheque_pdf']) {
                    echo "<a href='cheques/" . htmlspecialchars($ultimo_cheque['cheque_pdf']) . "' download>Descargar</a>";
                } else {
                    echo "No disponible";
                }
                echo "</td>";
                echo "<td class='action-buttons'>";
                echo "<a href='gestion_socios.php?editar=" . $socio['id'] . "' class='modificar'>Modificar</a>";
                echo "<a href='gestion_socios.php?aportar=" . $socio['id'] . "' class='aportar'>Aportar Saldo</a>";
                echo "<a href='gestion_socios.php?retirar=" . $socio['id'] . "' class='retirar'>Retirar Saldo</a>";
                echo "<a href='desvincular_socio.php?id=" . $socio['id'] . "' class='desvincular' onclick='return confirm(\"¿Estás seguro de desvincular a este socio?\");'>Desvincular</a>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay socios registrados.</p>";
        }

        if (isset($_GET['success'])) {
            echo "<p style='color: green;'>Éxito: " . htmlspecialchars($_GET['success']) . "</p>";
        }
        if (isset($_GET['error'])) {
            echo "<p style='color: red;'>Error: " . htmlspecialchars($_GET['error']) . "</p>";
        }
        if (isset($error_message)) {
            echo "<p style='color: red;'>$error_message</p>";
        }
        ?>
        </div>

        <?php if (isset($_GET['editar'])): ?>
            <h2>Modificar Socio</h2>
            <?php
            $socio_id = $_GET['editar'];
            $stmt = $conn->prepare("SELECT * FROM socios WHERE id = :id");
            $stmt->bindParam(':id', $socio_id);
            $stmt->execute();
            $socio = $stmt->fetch();

            if ($socio) {
            ?>
                <form method="POST">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($socio['nombre']); ?>" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($socio['email']); ?>" required>

                    <label for="cedula">Cédula:</label>
                    <input type="text" id="cedula" name="cedula" value="<?php echo htmlspecialchars($socio['cedula']); ?>" maxlength="13" pattern="[0-9]{11,13}" required>

                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($socio['direccion']); ?>" required>

                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($socio['telefono']); ?>" maxlength="10" pattern="[0-9]{10}" required>

                    <button type="submit">Guardar Cambios</button>
                    <a href="gestion_socios.php" class="cancel">Cancelar</a>
                </form>
            <?php
            } else {
                echo "<p>Socio no encontrado.</p>";
            }
            ?>
        <?php endif; ?>

        <?php if (isset($_GET['aportar'])): ?>
            <h2>Aportar Saldo</h2>
            <?php
            $socio_id = $_GET['aportar'];
            $stmt = $conn->prepare("SELECT nombre FROM socios WHERE id = :id");
            $stmt->bindParam(':id', $socio_id);
            $stmt->execute();
            $socio = $stmt->fetch();
            ?>
            <form action="aportar_saldo.php" method="POST">
                <input type="hidden" name="socio_id" value="<?php echo $socio_id; ?>">
                <label for="monto">Monto del Aporte:</label>
                <input type="number" id="monto" name="monto" step="0.01" required>
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required>
                <p>Socio: <?php echo htmlspecialchars($socio['nombre']); ?></p>
                <button type="submit">Registrar Aporte</button>
            </form>
        <?php endif; ?>

        <?php if (isset($_GET['retirar'])): ?>
            <h2>Retirar Saldo</h2>
            <?php
            $socio_id = $_GET['retirar'];
            $stmt = $conn->prepare("SELECT nombre, saldo FROM socios WHERE id = :id");
            $stmt->bindParam(':id', $socio_id);
            $stmt->execute();
            $socio = $stmt->fetch();
            ?>
            <form action="retirar_saldo.php" method="POST">
                <input type="hidden" name="socio_id" value="<?php echo $socio_id; ?>">
                <label for="monto">Monto a Retirar (Máximo: $<?php echo number_format($socio['saldo'], 2); ?>):</label>
                <input type="number" id="monto" name="monto" step="0.01" max="<?php echo $socio['saldo']; ?>" required>
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha" required>
                <p>Socio: <?php echo htmlspecialchars($socio['nombre']); ?></p>
                <button type="submit">Retirar y Generar Cheque</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
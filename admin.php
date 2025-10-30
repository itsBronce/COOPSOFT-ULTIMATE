<?php
// Habilitar almacenamiento en búfer de salida
ob_start();

// Iniciar la sesión
session_start();

// Mostrar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener el identificador de sesión personalizado (sid)
$sid = $_GET['sid'] ?? null;
if (!$sid || !isset($_SESSION['sessions'][$sid])) {
    header("Location: index.php");
    exit;
}

// Referencia a la sesión específica
$session = &$_SESSION['sessions'][$sid];

// Verificar si el usuario está autenticado
if (!isset($session['logged_in']) || $session['logged_in'] !== true) {
    header("Location: index.php?sid=$sid");
    exit;
}

// Verificar el rol del usuario
if (!isset($session['role']) || $session['role'] !== 'admin') {
    // Redirigir según el rol
    $redirect_page = match ($session['role'] ?? 'default') {
        'cajera' => "socios.php?sid=$sid",
        'gerente' => "prestamos.php?sid=$sid",
        'cobro' => "pagos.php?sid=$sid",
        default => "index.php?sid=$sid",
    };
    header("Location: $redirect_page");
    exit;
}

include 'db/conexion.php';

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Cooperativa</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        main {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #1e3c72;
            margin-bottom: 20px;
        }
        a {
            display: block;
            margin: 10px 0;
            color: #2a5298;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            color: #ffd700;
        }
    </style>
</head>
<body>
    <?php if (file_exists('nav.php')) include 'nav.php'; else echo '<nav><a href="index.php?sid=' . htmlspecialchars($sid) . '">Inicio</a> | <a href="logout.php?sid=' . htmlspecialchars($sid) . '">Cerrar Sesión</a></nav>'; ?>
    <main>
        <h2>Bienvenido, Administrador</h2>
        <p>Seleccione una opción:</p>
        <a href="socios.php?sid=<?php echo htmlspecialchars($sid); ?>">Gestionar Socios</a>
        <a href="gestion_socios.php?sid=<?php echo htmlspecialchars($sid); ?>">Gestión de Socios</a>
        <a href="prestamos.php?sid=<?php echo htmlspecialchars($sid); ?>">Gestionar Préstamos</a>
        <a href="aprobar_prestamos.php?sid=<?php echo htmlspecialchars($sid); ?>">Aprobar/Rechazar Préstamos</a>
        <a href="pagos.php?sid=<?php echo htmlspecialchars($sid); ?>">Gestionar Pagos</a>
    </main>
</body>
</html>
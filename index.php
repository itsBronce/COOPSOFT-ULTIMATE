<?php
// Habilitar almacenamiento en búfer de salida
ob_start();

// Iniciar la sesión
session_start();

// Mostrar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Limpiar cualquier dato de sesión existente para evitar conflictos
session_unset();
session_destroy();
session_start();

// Depuración: Verificar el estado de la sesión al inicio
error_log("Sesión al inicio: " . print_r($_SESSION, true));

// Si el usuario ya está logueado, redirigir según su rol
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && !isset($_SESSION['redirected'])) {
    $_SESSION['redirected'] = true; // Marcar que ya hemos redirigido
    $role = $_SESSION['role'] ?? 'default';
    error_log("Usuario ya logueado, redirigiendo según rol: $role");
    switch ($role) {
        case 'admin':
            redirectWithFallback('admin.php');
            exit;
        case 'cajera':
            redirectWithFallback('socios.php');
            exit;
        case 'gerente':
            redirectWithFallback('prestamos.php');
            exit;
        case 'cobro':
            redirectWithFallback('pagos.php');
            exit;
        default:
            redirectWithFallback('admin.php');
            exit;
    }
}

// Incluir la conexión a la base de datos
include 'db/conexion.php';

// Función para manejar redirecciones con respaldo
function redirectWithFallback($location) {
    if (headers_sent($file, $line)) {
        error_log("No se puede redirigir, cabeceras ya enviadas en $file, línea $line");
        echo "<script>window.location.href='$location';</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=$location'></noscript>";
    } else {
        error_log("Redirigiendo a $location");
        header("Location: $location");
    }
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Solicitud POST recibida");

    // Obtener y limpiar los datos del formulario
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Depuración: Verificar los datos recibidos
    error_log("Usuario ingresado: $username");
    error_log("Contraseña ingresada: $password");

    // Validar campos vacíos
    if (empty($username) || empty($password)) {
        $error_message = "Por favor, ingrese usuario y contraseña.";
        error_log("Error: Campos vacíos");
    } else {
        try {
            // Consultar el usuario en la base de datos
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Depuración: Verificar si se encontró el usuario
            if ($user) {
                error_log("Usuario encontrado: " . print_r($user, true));
                // Comparar contraseñas (en texto plano por ahora)
                if ($password === $user['password']) {
                    // Establecer variables de sesión
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Depuración: Verificar variables de sesión
                    error_log("Sesión iniciada: " . print_r($_SESSION, true));

                    // Redirigir según el rol
                    $role = $user['role'];
                    switch ($role) {
                        case 'admin':
                            redirectWithFallback('admin.php');
                            break;
                        case 'cajera':
                            redirectWithFallback('socios.php');
                            break;
                        case 'gerente':
                            redirectWithFallback('prestamos.php');
                            break;
                        case 'cobro':
                            redirectWithFallback('pagos.php');
                            break;
                        default:
                            redirectWithFallback('admin.php');
                    }
                    exit;
                } else {
                    $error_message = "Contraseña incorrecta.";
                    error_log("Autenticación fallida: contraseña incorrecta para $username");
                }
            } else {
                $error_message = "Usuario no encontrado.";
                error_log("Usuario no encontrado: $username");
            }
        } catch (PDOException $e) {
            $error_message = "Error en la base de datos: " . $e->getMessage();
            error_log("Error en la base de datos: " . $e->getMessage());
        }
    }
} else {
    error_log("No se recibió solicitud POST, método: " . $_SERVER['REQUEST_METHOD']);
}

// Limpiar la bandera de redirección si llegamos aquí
unset($_SESSION['redirected']);

// Limpiar el búfer de salida
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cooperativa</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        .login-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 20px;
        }
        .login-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color: #555;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        .success {
            color: green;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>COOPSOFT ULTIMATE</h2>
        <form method="POST" action="index.php">
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Iniciar Sesión</button>
        </form>
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <p class="success">Sesión iniciada, pero no se redirigió. Rol: <?php echo htmlspecialchars($_SESSION['role'] ?? 'desconocido'); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
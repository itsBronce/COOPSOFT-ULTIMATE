<?php
// No llamamos a session_start() aquí porque ya debería estar iniciado en el archivo principal

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role']) || !isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>

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
</style>

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
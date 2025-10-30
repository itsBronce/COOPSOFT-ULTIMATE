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

// Procesar el formulario de registro de pago
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prestamo_id = intval($_POST['prestamo_id'] ?? 0);
    $monto_pago = floatval($_POST['monto_pago'] ?? 0);
    $fecha_pago = trim($_POST['fecha_pago'] ?? '');

    // Validar campos
    if ($prestamo_id <= 0 || $monto_pago <= 0 || empty($fecha_pago) || !strtotime($fecha_pago)) {
        header("Location: pagos.php?error=Complete todos los campos correctamente");
        exit;
    }

    try {
        // Verificar que el préstamo existe y está aprobado
        $stmt = $conn->prepare("SELECT socio_id, estado FROM prestamos WHERE id = :id");
        $stmt->bindParam(':id', $prestamo_id);
        $stmt->execute();
        $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prestamo || $prestamo['estado'] !== 'aprobado') {
            header("Location: pagos.php?error=Préstamo no encontrado o no está aprobado");
            exit;
        }

        // Insertar el pago
        $stmt = $conn->prepare("
            INSERT INTO pagos (prestamo_id, monto_pago, fecha_pago) 
            VALUES (:prestamo_id, :monto_pago, :fecha_pago)
        ");
        $stmt->bindParam(':prestamo_id', $prestamo_id);
        $stmt->bindParam(':monto_pago', $monto_pago);
        $stmt->bindParam(':fecha_pago', $fecha_pago);
        $stmt->execute();

        // Registrar en auditoria
        $stmt_audit = $conn->prepare("
            INSERT INTO auditoria (socio_id, accion, usuario, detalles) 
            VALUES (:socio_id, 'pago_registrado', :usuario, :detalles)
        ");
        $stmt_audit->bindParam(':socio_id', $prestamo['socio_id']);
        $usuario = $_SESSION['username'];
        $stmt_audit->bindParam(':usuario', $usuario);
        $detalles = "Pago registrado para préstamo #$prestamo_id: Monto=$monto_pago, Fecha=$fecha_pago";
        $stmt_audit->bindParam(':detalles', $detalles);
        $stmt_audit->execute();

        // Verificar si el préstamo ya está pagado completamente
        $stmt = $conn->prepare("
            SELECT p.monto * (1 + p.tasa_interes / 100) + COALESCE(p.mora, 0) AS total_con_interes, 
                   COALESCE(SUM(pg.monto_pago), 0) AS total_pagado
            FROM prestamos p
            LEFT JOIN pagos pg ON p.id = pg.prestamo_id
            WHERE p.id = :prestamo_id
            GROUP BY p.id
        ");
        $stmt->bindParam(':prestamo_id', $prestamo_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['total_pagado'] >= $result['total_con_interes']) {
            $stmt = $conn->prepare("UPDATE prestamos SET estado = 'pagado' WHERE id = :id");
            $stmt->bindParam(':id', $prestamo_id);
            $stmt->execute();

            $stmt_audit = $conn->prepare("
                INSERT INTO auditoria (socio_id, accion, usuario, detalles) 
                VALUES (:socio_id, 'prestamo_pagado', :usuario, :detalles)
            ");
            $stmt_audit->bindParam(':socio_id', $prestamo['socio_id']);
            $stmt_audit->bindParam(':usuario', $usuario);
            $detalles = "Préstamo #$prestamo_id pagado completamente.";
            $stmt_audit->bindParam(':detalles', $detalles);
            $stmt_audit->execute();
        }

        header("Location: pagos.php?success=Pago registrado correctamente");
        exit;
    } catch (PDOException $e) {
        header("Location: pagos.php?error=Error al registrar el pago: " . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: pagos.php?error=Método no permitido");
    exit;
}

ob_end_flush();
?>
<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

include 'db/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $cedula = $_POST['cedula'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $monto_inicial = floatval($_POST['monto_inicial']);

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
        header("Location: socios.php?error=Ya existe un socio con esa cédula, teléfono o correo");
        exit;
    }

    $fecha_limite = date('Y-m-d', strtotime('-6 months'));
    $saldo_6_meses = ($fecha_ingreso <= $fecha_limite) ? $monto_inicial : 0;

    $stmt = $conn->prepare("
        INSERT INTO socios (nombre, email, cedula, direccion, telefono, fecha_ingreso, saldo, saldo_6_meses) 
        VALUES (:nombre, :email, :cedula, :direccion, :telefono, :fecha, :saldo, :saldo_6_meses)
    ");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':cedula', $cedula);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':fecha', $fecha_ingreso);
    $stmt->bindParam(':saldo', $monto_inicial);
    $stmt->bindParam(':saldo_6_meses', $saldo_6_meses);

    if ($stmt->execute()) {
        $socio_id = $conn->lastInsertId();
        $stmt_aporte = $conn->prepare("INSERT INTO aportes (socio_id, monto, fecha) VALUES (:socio_id, :monto, :fecha)");
        $stmt_aporte->bindParam(':socio_id', $socio_id);
        $stmt_aporte->bindParam(':monto', $monto_inicial);
        $stmt_aporte->bindParam(':fecha', $fecha_ingreso);
        $stmt_aporte->execute();

        header("Location: socios.php?success=Socio agregado correctamente");
    } else {
        header("Location: socios.php?error=Error al agregar socio");
    }
    exit;
} else {
    header("Location: socios.php?error=Método no permitido");
    exit;
}
?>
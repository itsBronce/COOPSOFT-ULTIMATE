<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
$usuario = $_SESSION['username'];

include 'db/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $socio_id = $_POST['socio_id'];
    $monto = floatval($_POST['monto']); // Asegurar que sea float
    $fecha = $_POST['fecha'];

    // Insertar el aporte
    $stmt = $conn->prepare("INSERT INTO aportes (socio_id, monto, fecha) VALUES (:socio_id, :monto, :fecha)");
    $stmt->bindParam(':socio_id', $socio_id);
    $stmt->bindParam(':monto', $monto);
    $stmt->bindParam(':fecha', $fecha);

    if ($stmt->execute()) {
        // Actualizar el saldo total
        $stmt_update = $conn->prepare("UPDATE socios SET saldo = saldo + :monto WHERE id = :socio_id");
        $stmt_update->bindParam(':monto', $monto);
        $stmt_update->bindParam(':socio_id', $socio_id);
        $stmt_update->execute();

        // Calcular saldo_6_meses: suma de aportes hasta 6 meses antes de hoy
        $fecha_limite = date('Y-m-d', strtotime('-6 months')); // 2024-10-02
        $stmt = $conn->prepare("
            SELECT IFNULL(SUM(monto), 0) 
            FROM aportes 
            WHERE socio_id = :socio_id AND fecha <= :fecha_limite
        ");
        $stmt->bindParam(':socio_id', $socio_id);
        $stmt->bindParam(':fecha_limite', $fecha_limite);
        $stmt->execute();
        $saldo_6_meses = $stmt->fetchColumn();

        // Actualizar saldo_6_meses
        $stmt = $conn->prepare("UPDATE socios SET saldo_6_meses = :saldo_6_meses WHERE id = :socio_id");
        $stmt->bindParam(':saldo_6_meses', $saldo_6_meses);
        $stmt->bindParam(':socio_id', $socio_id);
        $stmt->execute();

        header("Location: gestion_socios.php?success=Aporte registrado correctamente");
    } else {
        header("Location: gestion_socios.php?error=Error al registrar el aporte");
    }
    exit;
} else {
    header("Location: gestion_socios.php?error=Método no permitido");
    exit;
}
?>
<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
$usuario = $_SESSION['username'];
include 'db/conexion.php';

if (isset($_GET['id'])) {
    $socio_id = $_GET['id'];

    // Verificar si el socio tiene préstamos pendientes
    $stmt = $conn->prepare("SELECT COUNT(*) FROM prestamos WHERE socio_id = :id AND estado IN ('pendiente', 'aprobado')");
    $stmt->bindParam(':id', $socio_id);
    $stmt->execute();
    $prestamos_pendientes = $stmt->fetchColumn();

    if ($prestamos_pendientes > 0) {
        header("Location: gestion_socios.php?error=Socio tiene préstamos pendientes");
        exit;
    }

    // Calcular penalización y actualizar saldo antes de eliminar
    $stmt = $conn->prepare("SELECT saldo FROM socios WHERE id = :id");
    $stmt->bindParam(':id', $socio_id);
    $stmt->execute();
    $saldo = $stmt->fetchColumn();
    $penalizacion = $saldo * 0.10; // 10% de penalización
    $saldo_final = $saldo - $penalizacion;

    // Actualizar saldo (opcional, ya que vamos a eliminar)
    $stmt = $conn->prepare("UPDATE socios SET saldo = :saldo WHERE id = :id");
    $stmt->bindParam(':saldo', $saldo_final);
    $stmt->bindParam(':id', $socio_id);
    $stmt->execute();

    // Eliminar pagos asociados a los préstamos del socio
    $stmt = $conn->prepare("DELETE FROM pagos WHERE prestamo_id IN (SELECT id FROM prestamos WHERE socio_id = :id)");
    $stmt->bindParam(':id', $socio_id);
    $stmt->execute();

    // Eliminar préstamos asociados
    $stmt = $conn->prepare("DELETE FROM prestamos WHERE socio_id = :id");
    $stmt->bindParam(':id', $socio_id);
    $stmt->execute();

    // Eliminar aportes asociados
    $stmt = $conn->prepare("DELETE FROM aportes WHERE socio_id = :id");
    $stmt->bindParam(':id', $socio_id);
    $stmt->execute();

    // Eliminar socio
    $stmt = $conn->prepare("DELETE FROM socios WHERE id = :id");
    $stmt->bindParam(':id', $socio_id);
    $stmt->execute();

    header("Location: gestion_socios.php?success=Socio, sus aportes, préstamos y pagos eliminados correctamente");
    exit;
} else {
    header("Location: gestion_socios.php?error=ID no proporcionado");
    exit;
}
?>
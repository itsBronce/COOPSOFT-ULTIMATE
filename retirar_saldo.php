<?php
session_start();
include 'db/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: gestion_socios.php?error=Método no permitido");
    exit;
}

$socio_id = $_POST['socio_id'];
$monto = floatval($_POST['monto']);
$fecha = $_POST['fecha'];

$stmt = $conn->prepare("SELECT saldo, nombre FROM socios WHERE id = :id");
$stmt->bindParam(':id', $socio_id);
$stmt->execute();
$socio = $stmt->fetch();

if (!$socio || $monto <= 0 || $monto > $socio['saldo']) {
    header("Location: gestion_socios.php?error=Monto inválido o insuficiente");
    exit;
}

// Iniciar una transacción para asegurar consistencia
$conn->beginTransaction();

try {
    // Actualizar el saldo del socio
    $nuevo_saldo = $socio['saldo'] - $monto;
    $stmt = $conn->prepare("UPDATE socios SET saldo = :saldo WHERE id = :id");
    $stmt->bindParam(':saldo', $nuevo_saldo);
    $stmt->bindParam(':id', $socio_id);
    $stmt->execute();

    // Generar el cheque
    $filename = null;
    try {
        require('fpdf/fpdf.php');

        // Verificar si el directorio cheques/ existe, si no, crearlo
        $cheques_dir = 'cheques/';
        if (!is_dir($cheques_dir)) {
            mkdir($cheques_dir, 0755, true);
        }

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Cheque de Retiro', 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Socio: ' . $socio['nombre'], 0, 1);
        $pdf->Cell(0, 10, 'Monto: $' . number_format($monto, 2), 0, 1);
        $pdf->Cell(0, 10, 'Fecha: ' . $fecha, 0, 1);
        $pdf->Cell(0, 10, 'Cooperativa: COOPSOFT ULTIMATE', 0, 1);

        $filename = "cheque_retiro_" . $socio_id . "_" . time() . ".pdf";
        $pdf->Output('F', $cheques_dir . $filename);
    } catch (Exception $e) {
        throw new Exception("Error al generar el cheque: " . $e->getMessage());
    }

    // Registrar el retiro en la tabla retiros
    $stmt = $conn->prepare("
        INSERT INTO retiros (socio_id, monto, fecha, cheque_pdf) 
        VALUES (:socio_id, :monto, :fecha, :cheque_pdf)
    ");
    $stmt->bindParam(':socio_id', $socio_id);
    $stmt->bindParam(':monto', $monto);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':cheque_pdf', $filename);
    $stmt->execute();

    // Registrar en auditoría
    $stmt_audit = $conn->prepare("
        INSERT INTO auditoria (socio_id, accion, usuario, detalles) 
        VALUES (:socio_id, 'retiro', :usuario, :detalles)
    ");
    $stmt_audit->bindParam(':socio_id', $socio_id);
    $usuario = $_SESSION['username'];
    $stmt_audit->bindParam(':usuario', $usuario);
    $detalles = "Retiro de saldo: $monto, Nuevo saldo: $nuevo_saldo";
    $stmt_audit->bindParam(':detalles', $detalles);
    $stmt_audit->execute();

    // Confirmar la transacción
    $conn->commit();
    header("Location: gestion_socios.php?success=Retiro exitoso y cheque generado");
    exit;
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conn->rollBack();
    header("Location: gestion_socios.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>
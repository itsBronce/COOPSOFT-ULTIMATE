<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

include 'db/conexion.php';
require_once 'fpdf/fpdf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $socio_id = $_POST['socio_id'];
    $monto = floatval($_POST['monto']);
    $tasa_interes = floatval($_POST['tasa_interes']);
    $plazo_meses = intval($_POST['plazo_meses']);
    $fecha_inicio = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-m-d'); // Hoy por defecto
    $fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-m-d', strtotime("+$plazo_meses months")); // Calcula fin

    $stmt = $conn->prepare("SELECT saldo_6_meses FROM socios WHERE id = :id");
    $stmt->bindParam(':id', $socio_id);
    $stmt->execute();
    $saldo_6_meses = $stmt->fetchColumn();
    $max_prestamo = $saldo_6_meses * 2;

    if ($monto > $max_prestamo) {
        header("Location: prestamos.php?error=Monto excede el doble del saldo a 6 meses");
        exit;
    }

    $interes_mensual = $tasa_interes / 100 / 12;
    $cuota_mensual = ($monto * $interes_mensual) / (1 - pow(1 + $interes_mensual, -$plazo_meses));

    $cheques_dir = __DIR__ . '/cheques';
    if (!file_exists($cheques_dir)) {
        mkdir($cheques_dir, 0777, true);
    }

    $pdf_file = 'cheque_prestamo_' . $socio_id . '_' . time() . '.pdf';
    $stmt = $conn->prepare("
        INSERT INTO prestamos (socio_id, monto, tasa_interes, plazo_meses, cuota_mensual, fecha_inicio, fecha_fin, estado, cheque_pdf) 
        VALUES (:socio_id, :monto, :tasa_interes, :plazo_meses, :cuota_mensual, :fecha_inicio, :fecha_fin, 'pendiente', :cheque_pdf)
    ");
    $stmt->bindParam(':socio_id', $socio_id);
    $stmt->bindParam(':monto', $monto);
    $stmt->bindParam(':tasa_interes', $tasa_interes);
    $stmt->bindParam(':plazo_meses', $plazo_meses);
    $stmt->bindParam(':cuota_mensual', $cuota_mensual);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    $stmt->bindParam(':cheque_pdf', $pdf_file);

    if ($stmt->execute()) {
        $stmt = $conn->prepare("SELECT nombre, cedula, telefono, direccion FROM socios WHERE id = :id");
        $stmt->bindParam(':id', $socio_id);
        $stmt->execute();
        $socio = $stmt->fetch();

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'CHEQUE DE PRESTAMO', 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Paguese a: ' . $socio['nombre'], 0, 1);
        $pdf->Cell(0, 10, 'Cedula: ' . $socio['cedula'], 0, 1);
        $pdf->Cell(0, 10, 'Telefono: ' . $socio['telefono'], 0, 1);
        $pdf->Cell(0, 10, 'Direccion: ' . $socio['direccion'], 0, 1);
        $pdf->Cell(0, 10, 'Monto: $' . number_format($monto, 2), 0, 1);
        $pdf->Cell(0, 10, 'Fecha: ' . date('Y-m-d'), 0, 1);
        $pdf->Cell(0, 10, 'Cooperativa COOPSOFT ULTIMATE', 0, 1);
        $pdf->Ln(20);
        $pdf->Cell(0, 10, '_________________________', 0, 1, 'R');
        $pdf->Cell(0, 10, 'Firma Autorizada', 0, 1, 'R');

        $pdf->Output('F', $cheques_dir . '/' . $pdf_file);

        header("Location: prestamos.php?success=Préstamo registrado y cheque generado");
    } else {
        header("Location: prestamos.php?error=Error al registrar el préstamo");
    }
    exit;
}
?>
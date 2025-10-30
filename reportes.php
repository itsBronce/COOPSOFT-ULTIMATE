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

// Incluir TCPDF
require_once 'tcpdf/tcpdf.php';

// Verificar si se proporcionó un ID de préstamo
if (!isset($_GET['descargar_historial'])) {
    header("Location: pagos.php?error=ID de préstamo no proporcionado");
    exit;
}

$prestamo_id = intval($_GET['descargar_historial']);

try {
    // Obtener información del préstamo y el socio
    $stmt = $conn->prepare("
        SELECT p.id AS prestamo_id, s.nombre AS socio_nombre, p.monto, p.tasa_interes, p.mora
        FROM prestamos p
        JOIN socios s ON p.socio_id = s.id
        WHERE p.id = :prestamo_id
    ");
    $stmt->bindParam(':prestamo_id', $prestamo_id);
    $stmt->execute();
    $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$prestamo) {
        header("Location: pagos.php?error=Préstamo no encontrado");
        exit;
    }

    // Obtener el historial de pagos
    $stmt = $conn->prepare("
        SELECT monto_pago, fecha_pago
        FROM pagos
        WHERE prestamo_id = :prestamo_id
        ORDER BY fecha_pago
    ");
    $stmt->bindParam(':prestamo_id', $prestamo_id);
    $stmt->execute();
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Crear un nuevo documento PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Establecer información del documento
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('COOPSOFT ULTIMATE');
    $pdf->SetTitle('Historial de Pagos - Préstamo #' . $prestamo_id);
    $pdf->SetSubject('Historial de Pagos');
    $pdf->SetKeywords('Historial, Pagos, Préstamo, Cooperativa');

    // Establecer márgenes
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Añadir una página
    $pdf->AddPage();

    // Establecer fuente
    $pdf->SetFont('helvetica', '', 12);

    // Título
    $pdf->Cell(0, 10, 'Historial de Pagos - Préstamo #' . $prestamo_id, 0, 1, 'C');
    $pdf->Ln(5);

    // Información del socio y préstamo
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, 'Socio: ' . $prestamo['socio_nombre'], 0, 1);
    $pdf->Cell(0, 10, 'Monto del Préstamo: $' . number_format($prestamo['monto'], 2), 0, 1);
    $pdf->Cell(0, 10, 'Tasa de Interés: ' . number_format($prestamo['tasa_interes'], 2) . '%', 0, 1);
    $pdf->Cell(0, 10, 'Mora: $' . number_format($prestamo['mora'] ?? 0, 2), 0, 1);
    $pdf->Ln(5);

    // Tabla de pagos
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(60, 10, 'Monto Pagado', 1, 0, 'C');
    $pdf->Cell(60, 10, 'Fecha', 1, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);

    if ($pagos) {
        foreach ($pagos as $pago) {
            $pdf->Cell(60, 10, '$' . number_format($pago['monto_pago'], 2), 1, 0, 'C');
            $pdf->Cell(60, 10, $pago['fecha_pago'], 1, 1, 'C');
        }
    } else {
        $pdf->Cell(120, 10, 'No hay pagos registrados.', 1, 1, 'C');
    }

    // Generar el PDF
    $pdf->Output('historial_pagos_prestamo_' . $prestamo_id . '.pdf', 'D');
    exit;
} catch (PDOException $e) {
    header("Location: pagos.php?error=Error al generar el historial: " . urlencode($e->getMessage()));
    exit;
}

ob_end_flush();
?>
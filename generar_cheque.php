<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

require('fpdf/fpdf.php');

$socio_id = $_GET['socio_id'];
$monto = floatval($_GET['monto']); // Convertir a float para asegurar formato numérico
$nombre = urldecode($_GET['nombre']);
$fecha = $_GET['fecha'];

// Función mejorada para convertir número a letras (solo enteros por simplicidad)
function numeroALetras($numero) {
    $unidades = array('', 'Un', 'Dos', 'Tres', 'Cuatro', 'Cinco', 'Seis', 'Siete', 'Ocho', 'Nueve');
    $decenas = array('', 'Diez', 'Veinte', 'Treinta', 'Cuarenta', 'Cincuenta', 'Sesenta', 'Setenta', 'Ochenta', 'Noventa');
    $centenas = array('', 'Ciento', 'Doscientos', 'Trescientos', 'Cuatrocientos', 'Quinientos', 'Seiscientos', 'Setecientos', 'Ochocientos', 'Novecientos');
    
    if ($numero == 0) {
        return 'Cero Pesos';
    }

    $resultado = '';

    // Miles
    if ($numero >= 1000) {
        $miles = floor($numero / 1000);
        $numero = $numero % 1000;
        
        // Convertir los miles como un número completo (puede tener decenas y unidades)
        $miles_texto = '';
        if ($miles >= 100) {
            $centena_miles = floor($miles / 100);
            $miles = $miles % 100;
            $miles_texto .= $centenas[$centena_miles] . ' ';
        }
        if ($miles >= 10) {
            $decena_miles = floor($miles / 10);
            $miles = $miles % 10;
            $miles_texto .= $decenas[$decena_miles] . ' ';
        }
        if ($miles > 0) {
            $miles_texto .= $unidades[$miles] . ' ';
        } elseif ($miles == 0 && $miles_texto == '') {
            $miles_texto = 'Mil ';
        }
        
        $resultado .= trim($miles_texto) . ' Mil ';
    }

    // Centenas
    if ($numero >= 100) {
        $centena = floor($numero / 100);
        $numero = $numero % 100;
        $resultado .= $centenas[$centena] . ' ';
    }

    // Decenas y Unidades
    if ($numero >= 10) {
        $decena = floor($numero / 10);
        $numero = $numero % 10;
        $resultado .= $decenas[$decena] . ' ';
    }

    if ($numero > 0) {
        $resultado .= $unidades[$numero] . ' ';
    }

    $resultado = trim($resultado) . ' Pesos';

    return $resultado;
}

// Crear el PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Título
$pdf->Cell(0, 10, 'Cooperativa COOPSOFT ULTIMATE', 0, 1, 'C');
$pdf->Ln(10);

// Detalles del cheque
$pdf->SetXY(60, 30);
$pdf->Cell(0, 10, 'Cheque Digital', 0, 1);
$pdf->Cell(0, 10, 'Fecha: ' . $fecha, 0, 1);
$pdf->Cell(0, 10, 'Paguese a: ' . $nombre, 0, 1);
$pdf->Cell(0, 10, 'Monto: $' . number_format($monto, 2), 0, 1);
$pdf->Cell(0, 10, 'Cantidad en letras: ' . numeroALetras($monto), 0, 1);

// Espacio para firma
$pdf->Ln(20);
$pdf->Cell(0, 10, '___________________________', 0, 1, 'R');
$pdf->Cell(0, 5, 'Firma Autorizada', 0, 1, 'R');

// Descargar el PDF
ob_start(); // Iniciar buffer para evitar salida previa
$pdf->Output('D', 'Cheque_' . $nombre . '_' . $fecha . '.pdf');
ob_end_flush(); // Limpiar y enviar buffer
exit;
?>
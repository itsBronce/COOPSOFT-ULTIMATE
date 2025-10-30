<?php
session_start();

// Definir a dónde redirigir según el rol
function getRedirectPage($role) {
    switch ($role) {
        case 'admin':
            return 'admin.php';
        case 'cajera':
            return 'socios.php';
        case 'gerente':
            return 'prestamos.php';
        case 'cobro':
            return 'pagos.php';
        default:
            return 'admin.php';
    }
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION['role'] !== 'gerente') {
    $redirect_page = getRedirectPage($_SESSION['role']);
    header("Location: $redirect_page");
    exit;
}

include 'db/conexion.php';

// Verificar si se recibió una acción de aprobar o rechazar
if (isset($_GET['aprobar']) || isset($_GET['rechazar'])) {
    $prestamo_id = isset($_GET['aprobar']) ? $_GET['aprobar'] : $_GET['rechazar'];
    $accion = isset($_GET['aprobar']) ? 'aprobado' : 'rechazado';

    // Obtener información del préstamo y del socio
    $stmt = $conn->prepare("
        SELECT p.socio_id, p.monto, s.nombre 
        FROM prestamos p 
        JOIN socios s ON p.socio_id = s.id 
        WHERE p.id = :id AND p.estado = 'pendiente'
    ");
    $stmt->bindParam(':id', $prestamo_id);
    $stmt->execute();
    $prestamo = $stmt->fetch();

    if ($prestamo) {
        $conn->beginTransaction();
        try {
            $filename = null;
            if ($accion === 'aprobado') {
                // Generar el cheque para el préstamo
                require('fpdf/fpdf.php');

                $cheques_dir = 'cheques/';
                if (!is_dir($cheques_dir)) {
                    mkdir($cheques_dir, 0755, true);
                }

                $pdf = new FPDF();
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 16);
                $pdf->Cell(0, 10, 'Cheque de Prestamo', 0, 1, 'C');
                $pdf->Ln(10);
                $pdf->SetFont('Arial', '', 12);
                $pdf->Cell(0, 10, 'Socio: ' . $prestamo['nombre'], 0, 1);
                $pdf->Cell(0, 10, 'Monto: $' . number_format($prestamo['monto'], 2), 0, 1);
                $pdf->Cell(0, 10, 'Fecha: ' . date('Y-m-d'), 0, 1);
                $pdf->Cell(0, 10, 'Cooperativa: COOPSOFT ULTIMATE', 0, 1);

                $filename = "cheque_prestamo_" . $prestamo_id . "_" . time() . ".pdf";
                $pdf->Output('F', $cheques_dir . $filename);
            }

            // Actualizar el estado del préstamo
            $stmt = $conn->prepare("
                UPDATE prestamos 
                SET estado = :estado, cheque_pdf = :cheque_pdf 
                WHERE id = :id
            ");
            $stmt->bindParam(':estado', $accion);
            $stmt->bindParam(':cheque_pdf', $filename);
            $stmt->bindParam(':id', $prestamo_id);
            $stmt->execute();

            // Registrar en auditoría
            $stmt_audit = $conn->prepare("
                INSERT INTO auditoria (socio_id, accion, usuario, detalles) 
                VALUES (:socio_id, :accion, :usuario, :detalles)
            ");
            $stmt_audit->bindParam(':socio_id', $prestamo['socio_id']);
            $stmt_audit->bindParam(':accion', $accion);
            $usuario = $_SESSION['username'];
            $stmt_audit->bindParam(':usuario', $usuario);
            $detalles = "Préstamo #$prestamo_id " . ($accion === 'aprobado' ? 'aprobado' : 'rechazado');
            $stmt_audit->bindParam(':detalles', $detalles);
            $stmt_audit->execute();

            $conn->commit();
            header("Location: aprobar_prestamos.php?success=Préstamo " . ($accion === 'aprobado' ? 'aprobado' : 'rechazado') . " correctamente");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            header("Location: aprobar_prestamos.php?error=" . urlencode("Error al procesar el préstamo: " . $e->getMessage()));
            exit;
        }
    } else {
        header("Location: aprobar_prestamos.php?error=Préstamo no encontrado o ya procesado");
        exit;
    }
} else {
    // En lugar de redirigir, mostramos un mensaje de error para romper el ciclo
    die("Error: Acción no especificada. Por favor, usa los enlaces de Aprobar/Rechazar desde la página de Aprobar/Rechazar Préstamos.");
}
?>
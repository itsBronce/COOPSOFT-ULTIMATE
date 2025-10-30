<?php
include 'db/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $fecha_ingreso = $_POST['fecha_ingreso'];

    $stmt = $conn->prepare("UPDATE socios SET nombre = :nombre, email = :email, fecha_ingreso = :fecha WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':fecha', $fecha_ingreso);

    if ($stmt->execute()) {
        header("Location: gestion_socios.php?success=1");
    } else {
        header("Location: gestion_socios.php?error=1");
    }
}
?>
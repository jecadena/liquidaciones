<?php
include "conexion.php";

$co_tip_maestro = $_GET['tipo_boleto'];

try {
    $sql = "SELECT co_tip_maestro, co_maestro, de_maestro FROM MAESTRO WHERE co_tip_maestro = :co_tip_maestro";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':co_tip_maestro', $co_tip_maestro, PDO::PARAM_STR);
    $stmt->execute();
    $tipos_maestro = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tipos_maestro);

} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}
?>

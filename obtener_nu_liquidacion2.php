<?php
include "conexion.php";

if(isset($_POST['co_tipo'])){
    $co_tipo = $_POST['co_tipo'];
    
    try {
        $sql = "SELECT MAX(nu_liquidacion) AS max_nu_liquidacion FROM documento WHERE co_tipo = :co_tipo";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':co_tipo', $co_tipo, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $max_nu_liquidacion = ((int)$resultado['max_nu_liquidacion']+1);
        echo $max_nu_liquidacion;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No se recibió el parámetro co_tipo.";
}
?>

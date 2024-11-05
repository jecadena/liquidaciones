<?php

include "conexion.php";

try {
    $sql="SELECT count(*) as cuenta from documento where co_cli_pro = '01' and co_tip_doc = 'DC' and nu_serie = '013' and nu_docu = '00345118'";
    //$sql = "SELECT count(*) as cuenta FROM documento WHERE nu_liquidacion = 60 and co_tip_doc = 'DS'";
    //$sql = "SELECT co_tipo FROM documento WHERE co_cli_pro = '01' AND co_tip_doc = 'DC' AND nu_serie = '001' AND nu_docu = '01126369'";
    //$sql = "SELECT nu_docu FROM documento WHERE nu_liquidacion = '1' AND co_tipo='L'";
    //$sql="UPDATE documento set nu_liquidacion = 64 WHERE co_cli_pro = '02' AND co_tip_doc = '01' AND nu_serie = 'F01' AND nu_docu = '00050313'";
    //$sql="SELECT de_tipo_liquidacion FROM TIPO_LIQUIDACION WHERE co_tipo = 'L'";
    //$sql="SHOW COLUMNS FROM documento";
    $stmt = $conn->query($sql);
    $tipos_liquidacion = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}
?>

<?php foreach ($tipos_liquidacion as $tipo): ?>
    <?php echo "Documento: ".$tipo['cuenta']."<br>"; ?>
<?php endforeach; ?>

<!-- 
    02	01	F01	00050312 - 63

    02 dc 010 00350164 - 64

    02	D/C	010	00351885 - 65

    DC	001	01125525

    01	D/C	013	00345118



select nu_liquidacion from documento where co_cli_pro = '01' and co_tip_doc = '07' and nu_serie = 'FNC2' and nu_docu = '00011638'
select nu_liquidacion from documento where co_cli_pro = '01' and co_tip_doc = '07' and nu_serie = 'FNC2' and nu_docu = '00012233'
select nu_liquidacion from documento where co_cli_pro = '01' and co_tip_doc = '07' and nu_serie = 'FNC2' and nu_docu = '00012234'
select nu_liquidacion from documento where co_cli_pro = '01' and co_tip_doc = '07' and nu_serie = 'FNC2' and nu_docu = '00012235'

-->
<?php
/*
include "conexion.php";

try {
    //$sql = "UPDATE documento set co_tipo = 'L' WHERE co_cli_pro = '01' AND co_tip_doc = 'DC' AND nu_serie = '001' AND nu_docu = '01125525'";
    //$sql = "UPDATE documento set co_tipo = 'L' WHERE co_cli_pro = '01' AND co_tip_doc = 'DC' AND nu_serie = '001' AND nu_docu = '01125991'";
    //$sql = "UPDATE documento set co_tipo = 'L' WHERE co_cli_pro = '01' AND co_tip_doc = 'DC' AND nu_serie = '001' AND nu_docu = '01126258'";
    //$sql = "UPDATE documento set co_tipo = 'L' WHERE co_cli_pro = '01' AND co_tip_doc = 'DC' AND nu_serie = '001' AND nu_docu = '01126369'";
    $stmt = $conn->query($sql);
    
    // Comprobar si la actualización fue exitosa
    $affectedRows = $stmt->rowCount();
    
    if ($affectedRows > 0) {
        echo "La actualización se realizó correctamente.";
    } else {
        echo "No se encontraron filas para actualizar.";
    }
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}*/
?>

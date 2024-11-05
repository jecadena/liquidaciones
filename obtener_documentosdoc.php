<?php
include "conexion.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_liquidacion1 = $_POST['tipo_liquidacion'];
    $fecha1 = $_POST['fecha1'];
    $fecha2 = $_POST['fecha2'];

    $sql_tipo_liquidacion1 = "SELECT co_maestro FROM tipo_liquidacion WHERE co_tipo = :tipo_liquidacion";
    $stmt_tipo_liquidacion1 = $conn->prepare($sql_tipo_liquidacion1);
    $stmt_tipo_liquidacion1->bindParam(':tipo_liquidacion', $tipo_liquidacion1);
    $stmt_tipo_liquidacion1->execute();
    $co_tip_maestro_result1 = $stmt_tipo_liquidacion1->fetch(PDO::FETCH_ASSOC);
    $tipo_liquidacion = $co_tip_maestro_result1['co_maestro'];

    try {
        $sql_tipo_liquidacion = "SELECT co_tip_maestro FROM tipo_liquidacion WHERE co_maestro = :tipo_liquidacion";
        $stmt_tipo_liquidacion = $conn->prepare($sql_tipo_liquidacion);
        $stmt_tipo_liquidacion->bindParam(':tipo_liquidacion', $tipo_liquidacion);
        $stmt_tipo_liquidacion->execute();
        $co_tip_maestro_result = $stmt_tipo_liquidacion->fetch(PDO::FETCH_ASSOC);
        $co_tip_maestro = $co_tip_maestro_result['co_tip_maestro'];

        $sql = "
              select documento.co_cli_pro, 
                documento.co_tip_doc, 
                documento.nu_serie, 
                documento.nu_docu, 
                documento.nu_file, 
                documento.fe_docu,
                documento.fe_venci,
                documento.nu_liquidacion,
				mo_total = documento.mo_total_doc
				from documento  
				inner join TIPO_DOCUMENTO on documento.co_cia=TIPO_DOCUMENTO.co_cia and documento.co_tip_doc=TIPO_DOCUMENTO.co_tip_doc
                where documento.co_cia='01' 
                and documento.co_cli_pro='01' 
                and documento.co_tip_maestro = :co_tip_maestro 
                and documento.co_maestro = :tipo_liquidacion  
                and documento.fe_docu between :fecha1 
                and :fecha2 and tipo_documento.fg_ctacte='1'";

        $sql .= "ORDER BY documento.nu_docu";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':co_tip_maestro', $co_tip_maestro);
        $stmt->bindParam(':tipo_liquidacion', $tipo_liquidacion);
        $stmt->bindParam(':fecha1', $fecha1);
        $stmt->bindParam(':fecha2', $fecha2);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as &$row) {
            $row['fe_docu'] = date('d/m/Y', strtotime($row['fe_docu']));
            $row['fe_venci'] = date('d/m/Y', strtotime($row['fe_venci']));
        }

        echo json_encode($result);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>

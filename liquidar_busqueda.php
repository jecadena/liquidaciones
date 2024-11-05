<?php
include "conexion.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_liquidacion1 = $_POST['tipo_liquidacion'];
    $laliquidacion = $tipo_liquidacion1;
    $fecha1 = $_POST['fecha1'];
    $fecha2 = $_POST['fecha2'];
    $tipo_boleto = $_POST['tipo_boleto'];
    $codigo_referencia = $_POST['codigo_referencia'];
    $nu_liquidacion = $_POST['nu_liquidacion'];

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

        $sql = "SELECT 
                    documento.co_cli_pro, 
                    documento.co_tip_doc, 
                    documento.nu_serie, 
                    documento.nu_docu, 
                    documento.nu_file, 
                    documento.fe_docu,
                    documento.fe_venci,
                    documento.nu_liquidacion
                FROM documento 
                INNER JOIN stock_boleto 
                    ON documento.co_cia = stock_boleto.co_cia 
                    AND documento.co_cli_pro = stock_boleto.co_cli_pro 
                    AND documento.co_tip_doc = stock_boleto.co_tip_doc 
                    AND documento.nu_serie = stock_boleto.nu_serie 
                    AND documento.nu_docu = stock_boleto.nu_docu
                WHERE documento.co_cia = '01' 
                    AND documento.co_cli_pro = '01' 
                    AND documento.co_tip_maestro = :co_tip_maestro
                    AND documento.co_maestro = :tipo_liquidacion 
                    AND documento.fe_docu BETWEEN :fecha1 AND :fecha2 ";

        if (!empty($tipo_boleto)) {
            $sql .= "AND stock_boleto.co_tip_maestro LIKE :tipo_boleto ";
        }

        if (!empty($codigo_referencia)) {
            $sql .= "AND stock_boleto.co_maestro LIKE :codigo_referencia ";
        }

        $sql .= "ORDER BY documento.nu_docu";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':co_tip_maestro', $co_tip_maestro);
        $stmt->bindParam(':tipo_liquidacion', $tipo_liquidacion);
        $stmt->bindParam(':fecha1', $fecha1);
        $stmt->bindParam(':fecha2', $fecha2);

        if (!empty($tipo_boleto)) {
            $stmt->bindParam(':tipo_boleto', $tipo_boleto);
        }

        if (!empty($codigo_referencia)) {
            $stmt->bindParam(':codigo_referencia', $codigo_referencia);
        }

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            if ($row['nu_liquidacion'] !== null && $row['nu_liquidacion'] > 0) {
                echo json_encode(['error' => 'El documento <strong>' . $row['co_cli_pro'] . '</strong><br>Tipo de documento: <strong>' . $row['co_tip_doc'] . '</strong><br>Serie: <strong>' . $row['nu_serie'] . '</strong><br>Número de Documento: <strong>' . $row['nu_docu'] . '</strong><br>Ya tiene número de liquidación']);
                exit; 
            } else {
                $updateSql =   "UPDATE documento
                                SET documento.nu_liquidacion = :nu_liquidacion, co_tipo = :laliquidacion
                                FROM documento
                                INNER JOIN stock_boleto 
                                    ON documento.co_cia = stock_boleto.co_cia 
                                    AND documento.co_cli_pro = stock_boleto.co_cli_pro 
                                    AND documento.co_tip_doc = stock_boleto.co_tip_doc 
                                    AND documento.nu_serie = stock_boleto.nu_serie 
                                    AND documento.nu_docu = stock_boleto.nu_docu
                                WHERE documento.co_cia = '01' 
                                    AND documento.co_cli_pro = '01' 
                                    AND documento.co_tip_maestro = :co_tip_maestro
                                    AND documento.co_maestro = :tipo_liquidacion 
                                    AND documento.fe_docu BETWEEN :fecha1 AND :fecha2 
                                    AND (documento.nu_liquidacion IS NULL OR documento.nu_liquidacion = 0)";
        
                if (!empty($tipo_boleto)) {
                    $updateSql .= " AND stock_boleto.co_tip_maestro LIKE :tipo_boleto";
                }
                if (!empty($codigo_referencia)) {
                    $updateSql .= " AND stock_boleto.co_maestro LIKE :codigo_referencia";
                }
        
                $updateSqlDebug = $updateSql;
                $updateSqlDebug = str_replace(':nu_liquidacion', $nu_liquidacion, $updateSqlDebug);
                $updateSqlDebug = str_replace(':co_tip_maestro', $co_tip_maestro, $updateSqlDebug);
                $updateSqlDebug = str_replace(':tipo_liquidacion', $tipo_liquidacion, $updateSqlDebug);
                $updateSqlDebug = str_replace(':laliquidacion', $laliquidacion, $updateSqlDebug);
                $updateSqlDebug = str_replace(':fecha1', $fecha1, $updateSqlDebug);
                $updateSqlDebug = str_replace(':fecha2', $fecha2, $updateSqlDebug);
        
                if (!empty($tipo_boleto)) {
                    $updateSqlDebug = str_replace(':tipo_boleto', $tipo_boleto, $updateSqlDebug);
                }
                if (!empty($codigo_referencia)) {
                    $updateSqlDebug = str_replace(':codigo_referencia', $codigo_referencia, $updateSqlDebug);
                }
        
                echo $updateSqlDebug;
        
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bindParam(':nu_liquidacion', $nu_liquidacion);
                $updateStmt->bindParam(':co_tip_maestro', $co_tip_maestro);
                $updateStmt->bindParam(':tipo_liquidacion', $tipo_liquidacion);
                $updateStmt->bindParam(':laliquidacion', $laliquidacion);
                $updateStmt->bindParam(':fecha1', $fecha1);
                $updateStmt->bindParam(':fecha2', $fecha2);
        
                if (!empty($tipo_boleto)) {
                    $updateStmt->bindParam(':tipo_boleto', $tipo_boleto);
                }
                if (!empty($codigo_referencia)) {
                    $updateStmt->bindParam(':codigo_referencia', $codigo_referencia);
                }
        
                $updateStmt->execute();
                $cantidadRegistros = $updateStmt->rowCount();
                echo json_encode(['success' => 'Se ha realizado la liquidación de ' . $cantidadRegistros . ' registros']);
                exit; 
            }
        }        
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}
?>
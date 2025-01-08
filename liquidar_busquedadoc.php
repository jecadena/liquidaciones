<?php
include "conexion.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos enviados en el cuerpo de la solicitud (en formato JSON)
    $data = json_decode(file_get_contents("php://input"), true);

    // Extraer los datos del formulario y registros
    $tipo_liquidacion = $data['tipo_liquidacion'];
    $fecha1 = $data['fecha1'];
    $fecha2 = $data['fecha2'];
    $nu_liquidacion = $data['nu_liquidacion'];
    $registros = $data['registros'];

    // Verificar que se hayan seleccionado documentos
    if (empty($registros)) {
        echo json_encode(['error' => 'No se han seleccionado documentos para liquidar.']);
        exit();
    }

    try {
        // Consulta para obtener el co_tip_maestro
        $sql_tipo_liquidacion = "SELECT co_tip_maestro, co_maestro FROM tipo_liquidacion WHERE co_tipo = :tipo_liquidacion";
        $stmt_tipo_liquidacion = $conn->prepare($sql_tipo_liquidacion);
        $stmt_tipo_liquidacion->bindParam(':tipo_liquidacion', $tipo_liquidacion);
        $stmt_tipo_liquidacion->execute();
        $co_tip_maestro_result = $stmt_tipo_liquidacion->fetch(PDO::FETCH_ASSOC);

        // Verificar si el resultado es válido
        if (!$co_tip_maestro_result) {
            echo json_encode(['error' => 'No se encontró el tipo de liquidación.']);
            exit;
        }

        $co_tip_maestro = $co_tip_maestro_result['co_tip_maestro'];
        $co_maestro = $co_tip_maestro_result['co_maestro'];

        $ids = [];
        foreach ($registros as $documento) {
            if (is_numeric($documento)) {
                $ids[] = $documento;
            }
        }

        $ids = implode(",", $ids);

        $sql = "
            SELECT 
                documento.co_cli_pro, 
                documento.co_tip_doc, 
                documento.nu_serie, 
                documento.nu_docu, 
                documento.nu_file, 
                documento.fe_docu,
                documento.fe_venci,
                documento.nu_liquidacion,
                documento.mo_total_doc
            FROM documento  
            INNER JOIN TIPO_DOCUMENTO 
            ON documento.co_cia = TIPO_DOCUMENTO.co_cia 
            AND documento.co_tip_doc = TIPO_DOCUMENTO.co_tip_doc
            WHERE documento.co_cia = '01' 
            AND documento.co_cli_pro = '01' 
            AND documento.co_tip_maestro = :co_tip_maestro 
            AND documento.co_maestro = :co_maestro 
            AND documento.nu_docu IN ($ids) 
            AND documento.fe_docu BETWEEN :fecha1 AND :fecha2 
            AND TIPO_DOCUMENTO.fg_ctacte = '1'
            ORDER BY documento.nu_docu";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':co_tip_maestro', $co_tip_maestro);
        $stmt->bindParam(':co_maestro', $co_maestro);
        $stmt->bindParam(':fecha1', $fecha1);
        $stmt->bindParam(':fecha2', $fecha2);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Verificar si ya hay liquidación en algunos de los documentos
        foreach ($result as $row) {
            if ($row['nu_liquidacion'] !== null && $row['nu_liquidacion'] > 0) {
                // Si ya tiene liquidación, detener la ejecución
                echo json_encode(['error' => 'El documento ' . $row['co_cli_pro'] . ' ya tiene número de liquidación.']);
                exit;
            }
        }

        // Actualizar los documentos seleccionados con el número de liquidación
        $updateSql = "
            UPDATE documento
            SET documento.nu_liquidacion = :nu_liquidacion, documento.co_tipo = :tipo_liquidacion
            FROM documento
            INNER JOIN TIPO_DOCUMENTO 
                ON documento.co_cia = TIPO_DOCUMENTO.co_cia 
                AND documento.co_tip_doc = TIPO_DOCUMENTO.co_tip_doc
            WHERE documento.co_cia = '01' 
                AND documento.co_cli_pro = '01' 
                AND documento.co_tip_maestro = :co_tip_maestro
                AND documento.co_maestro = :co_maestro
                AND documento.nu_docu IN ($ids) 
                AND documento.fe_docu BETWEEN :fecha1 AND :fecha2 
                AND (documento.nu_liquidacion IS NULL OR documento.nu_liquidacion = 0)
                AND TIPO_DOCUMENTO.fg_ctacte = '1'";

        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bindParam(':nu_liquidacion', $nu_liquidacion);
        $updateStmt->bindParam(':co_tip_maestro', $co_tip_maestro);
        $updateStmt->bindParam(':tipo_liquidacion', $tipo_liquidacion);
        $updateStmt->bindParam(':co_maestro', $co_maestro);
        $updateStmt->bindParam(':fecha1', $fecha1);
        $updateStmt->bindParam(':fecha2', $fecha2);

        $updateStmt->execute();

        $cantidadRegistros = $updateStmt->rowCount();

        // Mensaje de éxito
        echo json_encode(['success' => 'Se ha realizado la liquidación de ' . $cantidadRegistros . ' documentos.']);

    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
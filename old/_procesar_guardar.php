<?php

require 'conexion.php'; 

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['excelFile']['name'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["excelFile"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if ($fileType != "xlsx" && $fileType != "xls") {
        echo json_encode(array("message" => "Solo se permiten archivos Excel."));
        exit();
    }

    if (move_uploaded_file($_FILES["excelFile"]["tmp_name"], $target_file)) {
        $spreadsheet = IOFactory::load($target_file);
        $sheet = $spreadsheet->getActiveSheet();

        $data = array();
        $skipFirstRow = true;

        foreach ($sheet->getRowIterator() as $row) {
            if ($skipFirstRow) {
                $skipFirstRow = false;
                continue;
            }
            $rowData = array();
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
            if (!empty($rowData[0])) {
                $data[] = array_slice($rowData, 0, 4); 
            }
        }

        $sqlMaxLiquidacion = "SELECT MAX(nu_liquidacion) AS max_liquidacion FROM documento";
        $stmtMaxLiquidacion = $conn->query($sqlMaxLiquidacion);
        $maxLiquidacion = $stmtMaxLiquidacion->fetch(PDO::FETCH_ASSOC)['max_liquidacion'];

        $nextLiquidacion = $maxLiquidacion + 1;
        $updated = 0;
        foreach ($data as $row) {
            $co_clipro = $row[0];
            $de_abrev_excel = $row[1];
            $nu_serie = $row[2];
            $nu_docu = $row[3];

            $sqlGetTipDoc = "SELECT co_tip_doc FROM TIPO_DOCUMENTO WHERE de_abrev = ?";
            $stmtGetTipDoc = $conn->prepare($sqlGetTipDoc);
            $stmtGetTipDoc->execute([$de_abrev_excel]);
            $co_tip_doc = $stmtGetTipDoc->fetchColumn();

            if (!$co_tip_doc) {
                continue;
            }

            $sqlCheckLiquidacion = "SELECT * FROM documento WHERE co_cli_pro = ? AND co_tip_doc = ? AND nu_serie = ? AND nu_docu = ? AND nu_liquidacion IS NULL";
            $stmtCheckLiquidacion = $conn->prepare($sqlCheckLiquidacion);
            $stmtCheckLiquidacion->execute([$co_clipro, $co_tip_doc, $nu_serie, $nu_docu]);
            $existingLiquidacion = $stmtCheckLiquidacion->fetchColumn();
            /*
            if ($existingLiquidacion !== false) {
                continue;
            }
            */
            $sqlUpdateLiquidacion = "UPDATE documento SET nu_liquidacion = ? WHERE co_cli_pro = ? AND co_tip_doc = ? AND nu_serie = ? AND nu_docu = ? AND nu_liquidacion IS NULL";
            $stmtUpdateLiquidacion = $conn->prepare($sqlUpdateLiquidacion);
            $stmtUpdateLiquidacion->execute([$nextLiquidacion, $co_clipro, $co_tip_doc, $nu_serie, $nu_docu]);

            if ($stmtUpdateLiquidacion->rowCount() > 0) {
                $updated++;
            }
        }

        if ($updated > 0) {
            echo json_encode(array("message" => "Se han actualizado $updated registros correctamente."));
        } else {
            echo json_encode(array("message" => "EL documento $co_clipro - $de_abrev_excel - $nu_serie - $nu_docu ya tiene número de liquidación."));
        }
    } else {
        echo json_encode(array("message" => "Error al cargar el archivo."));
    }
} else {
    echo json_encode(array("message" => "No se ha seleccionado ningún archivo."));
}

?>

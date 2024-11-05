<?php
set_time_limit(600);
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$nuLiquidacion = $_POST['nu_liquidacion'];
$tipoLiquidacion = $_POST['tipo_liquidacion'];
$de_obs_control_doc = $_POST['obs'];

if (isset($_FILES['excelFile']['name'])) {
    $target_dir = "uploads/";
    $target_doc = $_FILES["excelFile"]["name"];
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
        $conditionMet = true;
        $conditionDB = true;
        $errorRows = array();

        try {
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

            foreach ($data as $row) {
                $co_clipro = $row[0];
                $de_abrev_excel = $row[1];
                $nu_serie = $row[2];
                $nu_docu = $row[3];

                $urlTipDoc = "https://SERVER10/tipodoc";
                $responseTipDoc = file_get_contents($urlTipDoc . "?de_abrev=" . urlencode($de_abrev_excel));
                $resultTipDoc = json_decode($responseTipDoc, true);
                $co_tip_doc = $resultTipDoc['co_tip_doc'] ?? null;

                if (!$co_tip_doc) {
                    continue;
                }

                $urlCheckDatabase = "https://SERVER10/checkdatabase";
                $responseCheckDatabase = file_get_contents($urlCheckDatabase . "?co_cli_pro=" . urlencode($co_clipro) . "&co_tip_doc=" . urlencode($co_tip_doc) . "&nu_serie=" . urlencode($nu_serie) . "&nu_docu=" . urlencode($nu_docu));
                $existingDatabase = json_decode($responseCheckDatabase, true)['exists'];

                if (!$existingDatabase) {
                    $conditionDB = false;
                    $errorRows[] = array(
                        'co_clipro' => $co_clipro,
                        'de_abrev_excel' => $de_abrev_excel,
                        'nu_serie' => $nu_serie,
                        'nu_docu' => $nu_docu
                    );
                    break;
                } else {
                    $urlCheckLiquidacion = "https://SERVER10/checkliquidacion";
                    $responseCheckLiquidacion = file_get_contents($urlCheckLiquidacion . "?co_cli_pro=" . urlencode($co_clipro) . "&co_tip_doc=" . urlencode($co_tip_doc) . "&nu_serie=" . urlencode($nu_serie) . "&nu_docu=" . urlencode($nu_docu));
                    $existingLiquidacion = json_decode($responseCheckLiquidacion, true)['exists'];

                    if (!$existingLiquidacion) {
                        $conditionMet = false;
                        $errorRows[] = array(
                            'co_clipro' => $co_clipro,
                            'de_abrev_excel' => $de_abrev_excel,
                            'nu_serie' => $nu_serie,
                            'nu_docu' => $nu_docu
                        );
                        break;
                    }
                }

                $urlUpdateLiquidacion = "https://SERVER10/updateliquidacion";
                $postData = json_encode(array(
                    'nu_liquidacion' => $nuLiquidacion,
                    'co_tipo' => $tipoLiquidacion,
                    'de_obs_control_doc' => $de_obs_control_doc,
                    'co_cli_pro' => $co_clipro,
                    'co_tip_doc' => $co_tip_doc,
                    'nu_serie' => $nu_serie,
                    'nu_docu' => $nu_docu
                ));

                $opts = array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => 'Content-type: application/json',
                        'content' => $postData
                    )
                );
                
                $context = stream_context_create($opts);
                $responseUpdate = file_get_contents($urlUpdateLiquidacion, false, $context);
                $updateResult = json_decode($responseUpdate, true);

                if ($updateResult['success'] !== true) {
                    throw new Exception("No se pudo actualizar el registro: $co_clipro, $de_abrev_excel, $nu_serie, $nu_docu");
                }
            }

            if (!$conditionDB) {
                $errorMessages = array();
                foreach ($errorRows as $errorRow) {
                    $errorMessages[] = "Error !!<br>El documento '$target_doc' <strong>NO ESTÁ REGISTRADO</strong> en la BD. <br><strong>Código:</strong> {$errorRow['co_clipro']}<br><strong>Tipo de documento:</strong> {$errorRow['de_abrev_excel']}<br><strong>Serie:</strong> {$errorRow['nu_serie']}<br><strong>Número de documento:</strong> {$errorRow['nu_docu']}";
                }
                echo json_encode(array("message" => implode("\n", $errorMessages)));
            } else {
                if (!$conditionMet) {
                    $errorMessages = array();
                    foreach ($errorRows as $errorRow) {
                        $errorMessages[] = "Error !!<br>El documento '$target_doc' tiene <strong>número de liquidación</strong>. <br><strong>Código:</strong> {$errorRow['co_clipro']}<br><strong>Tipo de documento:</strong> {$errorRow['de_abrev_excel']}<br><strong>Serie:</strong> {$errorRow['nu_serie']}<br><strong>Número de documento:</strong> {$errorRow['nu_docu']}";
                    }
                    echo json_encode(array("message" => implode("\n", $errorMessages)));
                } else {
                    echo json_encode(array("message" => "Se han actualizado " . count($data) . " registros correctamente."));
                }
            }
        } catch (Exception $e) {
            echo json_encode(array("message" => "Error: " . $e->getMessage()));
        }
    } else {
        echo json_encode(array("message" => "Error al cargar el archivo."));
    }
} else {
    echo json_encode(array("message" => "No se ha seleccionado ningún archivo."));
}
?>
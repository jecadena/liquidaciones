<?php
/*set_time_limit(600);
require 'conexion.php'; 

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
            $conn->beginTransaction();

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
                $co_clipro = substr(strval($row[0]), -2);
                $co_tip_doc_excel = $row[1];
                $nu_serie = $row[2];
                $nu_docu = $row[3];

                $sqlCheckTipDoc = "SELECT co_tip_doc FROM TIPO_DOCUMENTO WHERE co_tip_doc = ?";
                $stmtCheckTipDoc = $conn->prepare($sqlCheckTipDoc);
                $stmtCheckTipDoc->execute([$co_tip_doc_excel]);
                $co_tip_doc = $stmtCheckTipDoc->fetchColumn();

                if (!$co_tip_doc) {
                    $sqlGetTipDoc = "SELECT co_tip_doc FROM TIPO_DOCUMENTO WHERE de_abrev = ?";
                    $stmtGetTipDoc = $conn->prepare($sqlGetTipDoc);
                    $stmtGetTipDoc->execute([$co_tip_doc_excel]);
                    $co_tip_doc = $stmtGetTipDoc->fetchColumn();

                    if (!$co_tip_doc) {
                        continue;
                    }
                }

                $sqlCheckDatabase = "SELECT * FROM documento WITH (UPDLOCK) WHERE co_cli_pro = ? AND co_tip_doc = ? AND nu_serie = ? AND nu_docu = ?";
                $stmtCheckDatabase = $conn->prepare($sqlCheckDatabase);
                $stmtCheckDatabase->execute([$co_clipro, $co_tip_doc, $nu_serie, $nu_docu]);
                $existingDatabase = $stmtCheckDatabase->fetchColumn();

                if ($existingDatabase === false) {
                    $conditionDB = false;
                    $errorRows[] = array(
                        'co_clipro' => $co_clipro,
                        'de_abrev_excel' => $co_tip_doc_excel,
                        'nu_serie' => $nu_serie,
                        'nu_docu' => $nu_docu
                    );
                    break;
                } else {
                    $sqlCheckLiquidacion = "SELECT * FROM documento WITH (UPDLOCK) WHERE co_cli_pro = ? AND co_tip_doc = ? AND nu_serie = ? AND nu_docu = ? AND (nu_liquidacion IS NULL OR nu_liquidacion = 0)";
                    $stmtCheckLiquidacion = $conn->prepare($sqlCheckLiquidacion);
                    $stmtCheckLiquidacion->execute([$co_clipro, $co_tip_doc, $nu_serie, $nu_docu]);
                    $existingLiquidacion = $stmtCheckLiquidacion->fetchColumn();
                    $existingLiquidacion = substr($existingLiquidacion, -1);
                    if ($existingLiquidacion === false) {
                        $conditionMet = false;
                        $errorRows[] = array(
                            'co_clipro' => $co_clipro,
                            'de_abrev_excel' => $co_tip_doc_excel,
                            'nu_serie' => $nu_serie,
                            'nu_docu' => $nu_docu
                        );
                        break;
                    }
                }

                $sqlUpdateLiquidacion = "UPDATE documento SET nu_liquidacion = ?, co_tipo = ?, de_obs_control_doc = ? WHERE co_cli_pro = ? AND co_tip_doc = ? AND nu_serie = ? AND nu_docu = ?";
                $stmtUpdateLiquidacion = $conn->prepare($sqlUpdateLiquidacion);
                $stmtUpdateLiquidacion->execute([$nuLiquidacion, $tipoLiquidacion, $de_obs_control_doc, $co_clipro, $co_tip_doc, $nu_serie, $nu_docu]);
                if ($stmtUpdateLiquidacion->rowCount() <= 0) {
                    throw new Exception("No se pudo actualizar el registro: $co_clipro, $co_tip_doc_excel, $nu_serie, $nu_docu");
                }
            }

            if (!$conditionDB) {
                $conn->rollBack();
                $errorMessages = array();
                foreach ($errorRows as $errorRow) {
                    $errorMessages[] = "Error !!<br>El documento '$target_doc' <strong>NO ESTÁ REGISTRADO</strong> en la BD. <br><strong>Código:</strong> {$errorRow['co_clipro']}<br><strong>Tipo de documento:</strong> {$errorRow['de_abrev_excel']}<br><strong>Serie:</strong> {$errorRow['nu_serie']}<br><strong>Número de documento:</strong> {$errorRow['nu_docu']}";
                }
                echo json_encode(array("message" => implode("\n", $errorMessages)));
            } else {
                if (!$conditionMet) {
                    $conn->rollBack();
                    $errorMessages = array();
                    foreach ($errorRows as $errorRow) {
                        $errorMessages[] = "Error !!<br>El documento '$target_doc' tiene <strong>número de liquidación</strong>. <br><strong>Código:</strong> {$errorRow['co_clipro']}<br><strong>Tipo de documento:</strong> {$errorRow['de_abrev_excel']}<br><strong>Serie:</strong> {$errorRow['nu_serie']}<br><strong>Número de documento:</strong> {$errorRow['nu_docu']}";
                    }
                    echo json_encode(array("message" => implode("\n", $errorMessages)));
                } else {
                    $conn->commit();
                    echo json_encode(array("message" => "Se han actualizado " . count($data) . " registros correctamente."));
                }
                
            }
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(array("message" => "Error: " . $e->getMessage()));
        }
    } else {
        echo json_encode(array("message" => "Error al cargar el archivo."));
    }
} else {
    echo json_encode(array("message" => "No se ha seleccionado ningún archivo."));
}*/

session_start();
set_time_limit(900);
require 'conexion.php';

$nuLiquidacion = $_POST['nu_liquidacion'];
$tipoLiquidacion = $_POST['tipo_liquidacion'];
$de_obs_control_doc = $_POST['obs'];

if (isset($_SESSION['excelData'])) {
    $data = $_SESSION['excelData'];
    $conditionMet = true;
    $conditionDB = true;
    $errorRows = array();

    try {
        $conn->beginTransaction();

        foreach ($data as $index => $row) {
            $co_clipro = substr(strval($row[0]), -2);
            $co_tip_doc_excel = $row[1];
            $nu_serie = $row[2];
            $nu_docu = $row[3];

            $sqlCheckTipDoc = "SELECT co_tip_doc FROM TIPO_DOCUMENTO WHERE co_tip_doc = ?";
            $stmtCheckTipDoc = $conn->prepare($sqlCheckTipDoc);
            $stmtCheckTipDoc->execute([$co_tip_doc_excel]);
            $co_tip_doc = $stmtCheckTipDoc->fetchColumn();

            if (!$co_tip_doc) {
                $sqlGetTipDoc = "SELECT co_tip_doc FROM TIPO_DOCUMENTO WHERE de_abrev = ?";
                $stmtGetTipDoc = $conn->prepare($sqlGetTipDoc);
                $stmtGetTipDoc->execute([$co_tip_doc_excel]);
                $co_tip_doc = $stmtGetTipDoc->fetchColumn();

                if (!$co_tip_doc) {
                    $errorRows[] = array(
                        'index' => $index,
                        'co_clipro' => $co_clipro,
                        'de_abrev_excel' => $co_tip_doc_excel,
                        'nu_serie' => $nu_serie,
                        'nu_docu' => $nu_docu,
                        'error' => 'Tipo de documento no encontrado'
                    );
                    continue;
                }
            }

            $sqlCheckDatabase = "SELECT * FROM documento WITH (UPDLOCK) WHERE co_cli_pro = ? AND co_tip_doc = ? AND nu_serie = ? AND nu_docu = ?";
            $stmtCheckDatabase = $conn->prepare($sqlCheckDatabase);
            $stmtCheckDatabase->execute([$co_clipro, $co_tip_doc, $nu_serie, $nu_docu]);
            $existingDatabase = $stmtCheckDatabase->fetchColumn();

            if ($existingDatabase === false) {
                $conditionDB = false;
                $errorRows[] = array(
                    'index' => $index,
                    'co_clipro' => $co_clipro,
                    'de_abrev_excel' => $co_tip_doc_excel,
                    'nu_serie' => $nu_serie,
                    'nu_docu' => $nu_docu,
                    'error' => 'Documento no encontrado en la base de datos'
                );
                break;
            } else {
                $sqlCheckLiquidacion = "SELECT * FROM documento WITH (UPDLOCK) WHERE co_cli_pro = ? AND co_tip_doc = ? AND nu_serie = ? AND nu_docu = ? AND (nu_liquidacion IS NULL OR nu_liquidacion = 0)";
                $stmtCheckLiquidacion = $conn->prepare($sqlCheckLiquidacion);
                $stmtCheckLiquidacion->execute([$co_clipro, $co_tip_doc, $nu_serie, $nu_docu]);
                $existingLiquidacion = $stmtCheckLiquidacion->fetchColumn();
                if ($existingLiquidacion === false) {
                    $conditionMet = false;
                    $errorRows[] = array(
                        'index' => $index,
                        'co_clipro' => $co_clipro,
                        'de_abrev_excel' => $co_tip_doc_excel,
                        'nu_serie' => $nu_serie,
                        'nu_docu' => $nu_docu,
                        'error' => 'Documento ya tiene liquidación'
                    );
                    break;
                }
            }

            $sqlUpdateLiquidacion = "UPDATE documento SET nu_liquidacion = ?, co_tipo = ?, de_obs_control_doc = ? WHERE co_cli_pro = ? AND co_tip_doc = ? AND nu_serie = ? AND nu_docu = ?";
            $stmtUpdateLiquidacion = $conn->prepare($sqlUpdateLiquidacion);
            $stmtUpdateLiquidacion->execute([$nuLiquidacion, $tipoLiquidacion, $de_obs_control_doc, $co_clipro, $co_tip_doc, $nu_serie, $nu_docu]);
            if ($stmtUpdateLiquidacion->rowCount() <= 0) {
                $errorRows[] = array(
                    'index' => $index,
                    'co_clipro' => $co_clipro,
                    'de_abrev_excel' => $co_tip_doc_excel,
                    'nu_serie' => $nu_serie,
                    'nu_docu' => $nu_docu,
                    'error' => "No se pudo actualizar el documento"
                );
                break;
            }
        }

        if ($conditionMet && $conditionDB) {
            $conn->commit();
            $response = array(
                "message" => "Datos guardados exitosamente"
            );
        } else {
            $conn->rollBack();
            $errorHtml = "<strong>Error: No se pudieron guardar algunas filas</strong><br>";
            foreach ($errorRows as $error) {
                $errorHtml .= "co_clipro: " . $error['co_clipro'] . "<br>";
                $errorHtml .= "tipo: " . $error['de_abrev_excel'] . "<br>";
                $errorHtml .= "serie: " . $error['nu_serie'] . "<br>";
                $errorHtml .= "documento: " . $error['nu_docu'] . "<br>";
                $errorHtml .= "Error: " . $error['error'] . "<br><br>";
            }
            $response = array(
                "message" => $errorHtml
            );
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $errorHtml = "<strong>Error: " . $e->getMessage() . "</strong><br>";
        foreach ($errorRows as $error) {
            $errorHtml .= "co_clipro: " . $error['co_clipro'] . "<br>";
            $errorHtml .= "tipo: " . $error['de_abrev_excel'] . "<br>";
            $errorHtml .= "serie: " . $error['nu_serie'] . "<br>";
            $errorHtml .= "documento: " . $error['nu_docu'] . "<br>";
            $errorHtml .= "Error: " . $error['error'] . "<br><br>";
        }
        $response = array(
            "message" => $errorHtml
        );
    }
} else {
    $response = array(
        "message" => "No hay datos para procesar."
    );
}

echo json_encode($response);


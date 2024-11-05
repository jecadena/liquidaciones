<?php
require 'conexion.php'; 
require 'vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$uploadsDir = 'uploads/';

if(isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] === UPLOAD_ERR_OK) {
    $excelFile = $_FILES['excelFile']['tmp_name'];
    $excelFileName = $_FILES['excelFile']['name'];

    $reader = new Xlsx();
    $spreadsheet = $reader->load($excelFile);

    $sheet = $spreadsheet->getActiveSheet();

    $allRows = $sheet->toArray();

    $data = array();

    $processedRecords = 0;

    $table = '<table class="table"><thead><tr><th>co_cli_pro</th><th>co_tip_doc</th><th>nu_serie</th><th>nu_docu</th></tr></thead><tbody>';
    foreach ($allRows as $row) {
        $rowData = array_slice($row, 0, 4);

        $data[] = $rowData;

        $processedRecords++;

        $table .= '<tr>';
        foreach ($rowData as $cell) {
            $cell = htmlspecialchars_decode($cell, ENT_QUOTES);
            $table .= "<td>$cell</td>";
        }
        $table .= '</tr>';
    }
    $table .= '</tbody></table>';

    $jsonArray = array();
    foreach ($data as $row) {
        $jsonArray[] = array(
            "co_cli_pro" => $row[0],
            "co_tip_doc" => $row[1],
            "nu_serie" => $row[2],
            "nu_docu" => $row[3]
        );
    }

    $jsonData = json_encode($jsonArray, JSON_PRETTY_PRINT);

    $jsonFilename = pathinfo($excelFileName, PATHINFO_FILENAME) . '.json';

    $jsonFilePath = $uploadsDir . $jsonFilename;

    file_put_contents($jsonFilePath, $jsonData);

    $message = "Se han procesado $processedRecords registros del archivo Excel.";

    echo json_encode(array("success" => true, "jsonFile" => $jsonFilePath, "message" => $message, "table" => $table));
} else {
    echo json_encode(array("success" => false, "message" => "No se ha enviado ningún archivo."));
}
?>

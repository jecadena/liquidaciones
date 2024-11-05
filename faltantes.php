<?php
set_time_limit(2000);
require 'conexion.php'; 
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $conn = new PDO("sqlsrv:Server=$servidor;Database=$basedatos", $usr, $pwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a SQL Server: " . $e->getMessage());
}

$excelFile = "uploads/60 LIQUIDACION DEL 09 AL 15 FEBRERO 2024.xlsx";

if (file_exists($excelFile)) {
    $spreadsheet = IOFactory::load($excelFile);
    $sheet = $spreadsheet->getActiveSheet();

    $data = array();
    $missingRecords = array();

    foreach ($sheet->getRowIterator() as $row) {
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
        $co_cli_pro = $row[0];
        $de_abrev_excel = $row[1];
        $nu_serie = $row[2];
        $nu_docu = $row[3];

        $sqlGetTipDoc = "SELECT co_tip_doc FROM TIPO_DOCUMENTO WHERE de_abrev = ?";
        $stmtGetTipDoc = $conn->prepare($sqlGetTipDoc);
        $stmtGetTipDoc->execute([$de_abrev_excel]);
        $co_tip_doc = $stmtGetTipDoc->fetchColumn();

        if ($co_tip_doc) {
            $sqlCheckDocumento = "SELECT co_cli_pro, co_tip_doc, nu_serie, nu_docu, nu_liquidacion FROM documento WHERE co_cli_pro = ? AND co_tip_doc = ? AND nu_serie = ? AND nu_docu = ?";
            $stmtCheckDocumento = $conn->prepare($sqlCheckDocumento);
            $stmtCheckDocumento->execute([$co_cli_pro, $co_tip_doc, $nu_serie, $nu_docu]);
            $existingRecord = $stmtCheckDocumento->fetch(PDO::FETCH_ASSOC);

            if (!$existingRecord) {
                $missingRecords[] = array(
                    'co_cli_pro' => $co_cli_pro,
                    'co_tip_doc' => $co_tip_doc,
                    'nu_serie' => $nu_serie,
                    'nu_docu' => $nu_docu
                );
            }
        }
    }

    if (!empty($missingRecords)) {
        echo "<h2>Registros faltantes:</h2>";
        echo "<table border='1'><tr><th>co_cli_pro</th><th>co_tip_doc</th><th>nu_serie</th><th>nu_docu</th><th>nu_liquidacion</th></tr>";
        foreach ($missingRecords as $record) {
            echo "<tr><td>".$record['co_cli_pro']."</td><td>".$record['co_tip_doc']."</td><td>".$record['nu_serie']."</td><td>".$record['nu_docu']."</td><td>".$record['nu_liquidacion']."</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<h2>No hay registros faltantes.</h2>";
    }
} else {
    echo json_encode(array("message" => "El archivo de Excel no existe."));
}
?>

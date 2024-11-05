<?php
require 'vendor/autoload.php';
require 'conexion.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$inputFileName = 'comisiones.xlsx';

try {
    $spreadsheet = IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();

    $startRow = 4;
    $column = 'G';
    $notFoundCount = 0; 

    foreach ($worksheet->getRowIterator($startRow) as $row) {
        $cellValue = $worksheet->getCell($column . $row->getRowIndex())->getValue();

        $sql = "SELECT * FROM comisiones WHERE ConfirmationCode = :confirmationcode";
        
        $sqlWithValue = str_replace(':confirmationcode', $conn->quote($cellValue), $sql);
        echo "Consulta SQL: " . $sqlWithValue . "<br>";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':confirmationcode', $cellValue, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "Match found for Confirmationcode: " . $cellValue . " in row " . $row->getRowIndex() . "<br>";
        } else {
            echo "No está en la base de datos: " . $cellValue . " in row " . $row->getRowIndex() . "<br>";
            $notFoundCount++; 
        }
    }

    echo "<br><strong>ARCHIVOS NO ENCONTRADOS: " . $notFoundCount . "</strong>";

    $conn = null;

} catch (Exception $e) {
    echo 'Error loading file: ', $e->getMessage();
}

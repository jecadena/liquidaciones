<?php
/*
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if(isset($_FILES['excelFile']['name'])){
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["excelFile"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  
    if($fileType != "xlsx" && $fileType != "xls") {
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
            $firstCell = true;
            foreach ($cellIterator as $cell) {
                if ($firstCell && empty($cell->getValue())) {
                    break;
                }
                $rowData[] = $cell->getValue();
                $firstCell = false;
            }
            if (!empty($rowData[0])) {
                $data[] = array_slice($rowData, 0, 7);
            }
        }
      
        $numRows = count($data);
      
        echo '<h5>Número de registros: ' . $numRows . '</h5>';
        echo generateTable($data);
      
    } else {
        echo json_encode(array("message" => "Error al cargar el archivo."));
    }
} else {
    echo json_encode(array("message" => "No se ha seleccionado ningún archivo."));
}

function generateTable($data) {
    $html = '<table class="table table-striped">';
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= "<td>$cell</td>";
        }
        $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}*/

session_start();
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if(isset($_FILES['excelFile']['name'])){
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["excelFile"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if($fileType != "xlsx" && $fileType != "xls") {
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
            $firstCell = true;
            foreach ($cellIterator as $cell) {
                if ($firstCell && empty($cell->getValue())) {
                    break;
                }
                $rowData[] = $cell->getValue();
                $firstCell = false;
            }
            if (!empty($rowData[0])) {
                $data[] = array_slice($rowData, 0, 7);
                $sessionData[] = array_slice($rowData, 0, 4); // Guardar las primeras 4 columnas en sesión
            }
        }

        // Guardar los datos en la sesión
        $_SESSION['excelData'] = $sessionData;

        echo '<script>';
        echo 'console.log('.json_encode($sessionData).')';
        echo '</script>';

        $numRows = count($data);

        echo '<h5>Número de registros: ' . $numRows . '</h5>';
        echo generateTable($data);

    } else {
        echo json_encode(array("message" => "Error al cargar el archivo."));
    }
} else {
    echo json_encode(array("message" => "No se ha seleccionado ningún archivo."));
}

function generateTable($data) {
    $html = '<table class="table table-striped">';
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= "<td>$cell</td>";
        }
        $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}

<?php
require 'conexion.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nu_liquidacion = $_POST['nu_liquidacion'];
    $tipo_liquidacion = $_POST['tipo_liquidacion'];
    $de_obs_control_doc = $_POST['de_obs_control_doc'];

    $jsonUrl = 'https://localhost/json';
    $jsonData = file_get_contents($jsonUrl);
    if ($jsonData === false) {
        throw new Exception("No se pudo obtener el archivo JSON desde: $jsonUrl");
    }
    $data = json_decode($jsonData, true);
    if ($data === null) {
        throw new Exception("Error al decodificar el archivo JSON");
    }

    $updated = false;
    foreach ($data['documento'] as &$documento) {
        if ($documento['nu_liquidacion'] === $nu_liquidacion && $documento['co_tipo'] === $tipo_liquidacion) {
            $documento['de_obs_control_doc'] = $de_obs_control_doc;
            $updated = true;
            break;
        }
    }
    if (!$updated) {
        throw new Exception("No se pudo actualizar el registro: $nu_liquidacion, $tipo_liquidacion");
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $tipo_documento = '';
    foreach ($data['TIPO_LIQUIDACION'] as $tipo) {
        if ($tipo['co_tipo'] === $tipo_liquidacion) {
            $tipo_documento = $tipo['de_tipo_liquidacion'];
            break;
        }
    }
    if ($tipo_documento === '') {
        throw new Exception("Tipo de liquidación no encontrado: $tipo_liquidacion");
    }

    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'LIQUIDACION NUMERO: ' . $nu_liquidacion . ' PARA ' . $tipo_documento);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $fecha = '';
    foreach ($data['documento'] as $documento) {
        if ($documento['nu_liquidacion'] === $nu_liquidacion && $documento['co_tipo'] === $tipo_liquidacion) {
            $fecha = $documento['de_obs_control_doc'] ?? 'Fecha no encontrada';
            break;
        }
    }

    $sheet->mergeCells('A2:E2');
    $sheet->setCellValue('A2', $fecha);
    $sheet->getStyle('A2')->getFont()->setBold(true);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $columns = ['co_cli_pro', 'co_tipo', 'nu_serie', 'nu_docu', 'de_obs_control_doc'];
    $columnHeaders = ['Código Cliente/Proveedor', 'Tipo', 'Serie', 'Número', 'Observación Control Documento'];
    $colIndex = 'A';
    foreach ($columnHeaders as $header) {
        $sheet->setCellValue($colIndex . '3', $header);
        $sheet->getStyle($colIndex . '3')->getFont()->setBold(true);
        $sheet->getStyle($colIndex . '3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('008080');
        $sheet->getStyle($colIndex . '3')->getFont()->getColor()->setARGB('FFFFFF');
        $sheet->getStyle($colIndex . '3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getColumnDimension($colIndex)->setAutoSize(true);
        $colIndex++;
    }

    $row = 4;
    foreach ($data['documento'] as $documento) {
        if ($documento['nu_liquidacion'] === $nu_liquidacion && $documento['co_tipo'] === $tipo_liquidacion) {
            $colIndex = 'A';
            foreach ($columns as $column) {
                $sheet->setCellValue($colIndex . $row, $documento[$column]);
                $sheet->getStyle($colIndex . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $colIndex++;
            }
            $row++;
        }
    }

    $filename = 'reporte_liquidacion_' . $nu_liquidacion . '_' . $tipo_documento . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    echo $filename;
}
?>

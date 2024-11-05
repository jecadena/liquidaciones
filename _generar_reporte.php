<?php
require 'conexion.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nu_liquidacion = $_POST['nu_liquidacion'];
    $tipo_liquidacion = $_POST['tipo_liquidacion'];
    $de_obs_control_doc = $_POST['de_obs_control_doc'];

    $sqlUpdateLiquidacion = "UPDATE documento SET de_obs_control_doc = ? WHERE nu_liquidacion = ? AND co_tipo = ?";
    $stmtUpdateLiquidacion = $conn->prepare($sqlUpdateLiquidacion);
    $stmtUpdateLiquidacion->execute([$de_obs_control_doc, $nu_liquidacion, $tipo_liquidacion]);

    if ($stmtUpdateLiquidacion->rowCount() <= 0) {
        throw new Exception("No se pudo actualizar el registro: $nu_liquidacion, $tipo_liquidacion");
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $stmt = $conn->prepare("SELECT de_tipo_liquidacion FROM TIPO_LIQUIDACION WHERE co_tipo = ?");
    $stmt->execute([$tipo_liquidacion]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $tipo_documento = $resultado['de_tipo_liquidacion'];

    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'LIQUIDACION NUMERO: ' . $nu_liquidacion . ' PARA ' . $tipo_documento);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $stmt = $conn->prepare("SELECT de_obs_control_doc FROM documento WHERE nu_liquidacion = ? AND co_tipo = ?");
    $stmt->execute([$nu_liquidacion, $tipo_liquidacion]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $fecha = $result['de_obs_control_doc'] ?? 'Fecha no encontrada';

    $sheet->mergeCells('A2:E2');
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

    $stmt = $conn->prepare("SELECT co_cli_pro, co_tipo, nu_serie, nu_docu, de_obs_control_doc FROM documento WHERE nu_liquidacion = ? AND co_tipo = ?");
    $stmt->execute([$nu_liquidacion, $tipo_liquidacion]);
    $row = 4;

    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $colIndex = 'A';
        foreach ($columns as $column) {
            $sheet->setCellValue($colIndex . $row, $data[$column]);
            $sheet->getStyle($colIndex . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $colIndex++;
        }
        $row++;
    }

    $filename = 'reporte_liquidacion_' . $nu_liquidacion . '_'.$tipo_documento.'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    echo $filename;
}
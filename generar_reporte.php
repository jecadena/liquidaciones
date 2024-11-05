<?php
require 'conexion.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nu_liquidacion = $_POST['nu_liquidacion'];
    $tipo_liquidacion = $_POST['tipo_liquidacion'];

    $stmt = $conn->prepare("SELECT de_tipo_liquidacion FROM TIPO_LIQUIDACION WHERE co_tipo = ?");
    $stmt->execute([$tipo_liquidacion]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $de_liquidacion = $resultado['de_tipo_liquidacion'];

    $jsonUrl = 'https://actoursapps.com.pe:8080/erequest/api/liquidacion/'.$tipo_liquidacion.'/'.$nu_liquidacion.'';
    $jsonData = file_get_contents($jsonUrl);
    if ($jsonData === false) {
        throw new Exception("No se pudo obtener el archivo JSON desde: $jsonUrl");
    }
    $data = json_decode($jsonData, true);
    if ($data === null) {
        throw new Exception("Error al decodificar el archivo JSON");
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->mergeCells('A1:Q1');
    $sheet->setCellValue('A1', 'Reporte de Liquidación # '.$nu_liquidacion.' / '.$de_liquidacion);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $columnHeaders = [
        'de_boleto' => 'Boleto',
        'fe_emision' => 'Fecha Emisión',
        'de_maestro_la' => 'Línea Aérea / Transportista',
        'de_ruta' => 'Ruta',
        'de_pasajero' => 'Pasajero',
        'mo_tarifa' => 'Tarifa',
        'mo_igv_stock' => 'IGV',
        'mo_impuesto' => 'Impuesto',
        'mo_total' => 'Total',
        'fe_vuelo' => 'Fecha Vuelo',
        'mo_fee' => 'Fee',
        'mo_igv_fee' => 'IGV Fee',
        't_fee' => 'T. Fee',
        'co_tip_doc_fee' => 'Tipo Doc Fee',
        'nu_serie_fee' => 'Serie Fee',
        'nu_docu_fee' => 'Docu Fee',
        'de_maestro_cl' => 'Cliente',
        'glosa' => 'Glosa'
    ];

    $colIndex = 'A';
    foreach ($columnHeaders as $key => $header) {
        $sheet->setCellValue($colIndex . '2', $header);
        $sheet->getStyle($colIndex . '2')->getFont()->setBold(true);
        $sheet->getStyle($colIndex . '2')->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('008080');
        $sheet->getStyle($colIndex . '2')->getFont()->getColor()->setARGB('FFFFFF');
        $sheet->getStyle($colIndex . '2')->getBorders()->getAllBorders()
              ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getColumnDimension($colIndex)->setAutoSize(true);
        $colIndex++;
    }

    $row = 3;
    $sumTarifa = $sumIGV = $sumImpuesto = $sumTotal = $sumFee = $sumIgvFee = $sumTFee = 0;

    foreach ($data as $documento) {
        $colIndex = 'A';
        foreach ($columnHeaders as $key => $header) {
            if ($key === 't_fee') {
                $tFee = $documento['mo_fee'] + $documento['mo_igv_fee'];
                $sheet->setCellValue($colIndex . $row, $tFee);
                $sumTFee += $tFee;
            } else {
                $sheet->setCellValue($colIndex . $row, $documento[$key]);
            }
            $sheet->getStyle($colIndex . $row)->getBorders()->getAllBorders()
                  ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $colIndex++;
        }
        $sumTarifa += $documento['mo_tarifa'];
        $sumIGV += $documento['mo_igv_stock'];
        $sumImpuesto += $documento['mo_impuesto'];
        $sumTotal += $documento['mo_total'];
        $sumFee += $documento['mo_fee'];
        $sumIgvFee += $documento['mo_igv_fee'];
        $row++;
    }

    $sheet->setCellValue('E' . $row, 'TOTAL');
    $sheet->getStyle('E' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('F' . $row, $sumTarifa);
    $sheet->getStyle('F' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('G' . $row, $sumIGV);
    $sheet->getStyle('G' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('H' . $row, $sumImpuesto);
    $sheet->getStyle('H' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('I' . $row, $sumTotal);
    $sheet->getStyle('I' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('K' . $row, $sumFee);
    $sheet->getStyle('K' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('L' . $row, $sumIgvFee);
    $sheet->getStyle('L' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('M' . $row, $sumTFee);
    $sheet->getStyle('M' . $row)->getFont()->setBold(true);

    $filename = 'reporte_liquidacion_'.$nu_liquidacion.'_'.$de_liquidacion.'.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    echo $filename;
    
}
?>
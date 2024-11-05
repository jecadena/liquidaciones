<?php
require 'conexion.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nu_liquidacion = $_POST['nu_liquidacion'];
    $tipo_liquidacion = $_POST['tipo_liquidacion'];

    // Obtener descripción del tipo de liquidación
    $stmt = $conn->prepare("SELECT de_tipo_liquidacion FROM TIPO_LIQUIDACION WHERE co_tipo = ?");
    $stmt->execute([$tipo_liquidacion]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $de_liquidacion = $resultado['de_tipo_liquidacion'];

    // Consulta a la base de datos para obtener los datos necesarios
    $stmt = $conn->prepare("
        SELECT co_cli_pro, co_tip_doc, nu_serie, nu_docu, mo_total_doc, pagado, 
               ROUND(mo_total_doc - pagado, 2) AS saldo 
        FROM documento 
        WHERE co_cia = '01' AND nu_liquidacion = ? AND co_tipo = ?
    ");
    $stmt->execute([$nu_liquidacion, $tipo_liquidacion]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($data)) {
        throw new Exception("No se encontraron datos para la liquidación $nu_liquidacion y tipo $tipo_liquidacion");
    }

    // Crear la hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezado del reporte
    $sheet->mergeCells('A1:G1');
    $sheet->setCellValue('A1', 'Reporte de Liquidación # ' . $nu_liquidacion . ' / ' . $de_liquidacion);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Encabezados de las columnas
    $columnHeaders = [
        'co_cli_pro' => 'Cliente / Proveedor',
        'co_tip_doc' => 'Tipo Documento',
        'nu_serie' => 'Serie',
        'nu_docu' => 'Documento',
        'mo_total_doc' => 'Total Documento',
        'pagado' => 'Pagado',
        'saldo' => 'Saldo'
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

    // Insertar datos en la hoja de cálculo
    $row = 3;
    $sumTotal = $sumPagado = $sumSaldo = 0;

    foreach ($data as $documento) {
        $colIndex = 'A';
        foreach ($columnHeaders as $key => $header) {
            $sheet->setCellValue($colIndex . $row, $documento[$key]);
            $sheet->getStyle($colIndex . $row)->getBorders()->getAllBorders()
                  ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $colIndex++;
        }
        $sumTotal += $documento['mo_total_doc'];
        $sumPagado += $documento['pagado'];
        $sumSaldo += $documento['saldo'];
        $row++;
    }

    // Agregar totales al final
    $sheet->setCellValue('D' . $row, 'TOTAL');
    $sheet->getStyle('D' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('E' . $row, $sumTotal);
    $sheet->getStyle('E' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('F' . $row, $sumPagado);
    $sheet->getStyle('F' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('G' . $row, $sumSaldo);
    $sheet->getStyle('G' . $row)->getFont()->setBold(true);

    // Guardar el archivo
    $filename = 'reporte_liquidacion_' . $nu_liquidacion . '_' . $de_liquidacion . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    echo $filename;
}
?>

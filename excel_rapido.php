<?php
require 'conexion.php'; // Asegúrate de que la conexión a la base de datos está configurada correctamente.
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Prepara la consulta para obtener los datos de la base de datos
    $stmt = $conn->prepare("
        SELECT co_cli_pro, co_tip_doc, nu_serie, nu_docu, mo_total_doc, pagado, 
               ROUND(mo_total_doc - pagado, 2) AS saldo 
        FROM documento 
        WHERE co_cia = '01' AND nu_liquidacion = 68
    ");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($data)) {
        throw new Exception("No se encontraron datos para la liquidación 68");
    }

    // Crea una nueva hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezado de columnas
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

    // Agregar totales
    $sheet->setCellValue('E' . $row, 'TOTAL');
    $sheet->getStyle('E' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('F' . $row, $sumTotal);
    $sheet->getStyle('F' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('G' . $row, $sumPagado);
    $sheet->getStyle('G' . $row)->getFont()->setBold(true);
    $sheet->setCellValue('H' . $row, $sumSaldo);
    $sheet->getStyle('H' . $row)->getFont()->setBold(true);

    // Guarda el archivo
    $filename = 'reporte_liquidacion_68.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    echo 'Archivo creado: '.$filename;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>

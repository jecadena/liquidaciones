<?php
set_time_limit(600);

require 'conexion.php'; 

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['submit'])) {
    if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] == 0) {
        $uploads_dir = 'descargas';
        $tmp_name = $_FILES['excelFile']['tmp_name'];
        $filename = basename($_FILES['excelFile']['name']);
        $uploadfile = $uploads_dir . '/' . $filename;

        if (move_uploaded_file($tmp_name, $uploadfile)) {
            $spreadsheet = IOFactory::load($uploadfile);
            $sheet = $spreadsheet->getActiveSheet();

            $rows = $sheet->toArray();

            $notFound = [];
            $totalChecked = 0;

            for ($i = 3; $i < count($rows); $i++) {
                $excelCode = $rows[$i][9];

                if (!empty($excelCode)) {
                    $totalChecked++;
                    $sql = "SELECT * FROM COMISIONES WHERE ConfirmationCode = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$excelCode]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$result) {
                        $notFound[] = $excelCode;
                    }
                }
            }

            echo "<h2>Códigos no encontrados en la base de datos:</h2>";
            if (count($notFound) > 0) {
                echo "<ul>";
                foreach ($notFound as $code) {
                    echo "<li>$code</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>Todos los códigos de la columna 10 están en la base de datos.</p>";
            }
            echo "<p>Total de registros comprobados: $totalChecked</p>";
        } else {
            echo "Error al subir el archivo.";
        }
    } else {
        echo "No se ha cargado ningún archivo o hubo un error en la carga.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comparar Excel con Base de Datos</title>
</head>
<body>
    <h1>Comparar Excel con Base de Datos</h1>
    <form action="comparar.php" method="post" enctype="multipart/form-data">
        <label for="excelFile">Cargar archivo Excel:</label>
        <input type="file" name="excelFile" id="excelFile" required>
        <button type="submit" name="submit">Comparar</button>
    </form>
</body>
</html>

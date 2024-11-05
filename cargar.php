<?php
include "conexion.php";

try {
    // Preparar la consulta SQL para obtener los tipos de liquidación
    $sql = "SELECT co_tipo, de_tipo_liquidacion FROM TIPO_LIQUIDACION";
    
    // Ejecutar la consulta
    $stmt = $conn->query($sql);
    
    // Obtener todas las filas como un array asociativo
    $tipos_liquidacion = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al ejecutar la consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selección de Tipo de Liquidación</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Selección de Tipo de Liquidación</h2>
        <form action="#" method="post">
            <div class="form-group">
                <label for="tipo_liquidacion">Tipo de Liquidación</label>
                <select class="form-control" id="tipo_liquidacion" name="tipo_liquidacion">
                    <?php foreach ($tipos_liquidacion as $tipo): ?>
                        <option value="<?php echo $tipo['co_tipo']; ?>"><?php echo $tipo['de_tipo_liquidacion']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="nu_liquidacion">Número de Liquidación</label>
                <input type="text" class="form-control" id="nu_liquidacion" name="nu_liquidacion" readonly>
            </div>
            <button type="submit" class="btn btn-primary">Seleccionar</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#tipo_liquidacion').change(function(){
                var tipoSeleccionado = $(this).val();
                $.ajax({
                    url: 'obtener_nu_liquidacion.php',
                    method: 'POST',
                    data: {co_tipo: tipoSeleccionado},
                    success: function(response){
                        $('#nu_liquidacion').val(response);
                    }
                });
            });
        });
    </script>
</body>
</html>

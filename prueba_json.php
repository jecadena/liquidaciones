<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tipos de Liquidación</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <h2>Seleccione un Tipo de Liquidación:</h2>
    <select id="tipo_liquidacion">
        <option value="">Seleccione...</option>
    </select>

    <script>
    $(document).ready(function() {
        $.ajax({
            url: 'http://remoteserver:9091/api/liquidacion/tipos-liquidacion',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                data.forEach(function(tipo) {
                    $('#tipo_liquidacion').append('<option value="' + tipo.co_tipo + '">' + tipo.de_tipo_liquidacion + '</option>');
                });
            },
            error: function(xhr, status, error) {
                console.error("Error:", status, error);
                alert("No se pudieron cargar los tipos de liquidación.");
            }
        });
    });
    </script>
</body>
</html>

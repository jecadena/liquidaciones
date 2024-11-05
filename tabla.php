<?php
// Datos de conexión a la base de datos
$servidor = "SERVER10\SQL2008";
$basedatos = "bd_AgViajes";
$usr = "sa";
$pwd = "12345678";

// Crear la conexión
$conn = new mysqli($servidor, $usr, $pwd, $basedatos);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

try {
    // Consulta SQL simple para obtener todos los registros
    $sql = "SELECT id, GuestFirstName, GuestLastName, HotelName, ComisionTotal FROM comisiones";
    
    $result = $conn->query($sql);

    // Comprobar si hay resultados
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr>
                <th>ID</th>
                <th>Guest First Name</th>
                <th>Guest Last Name</th>
                <th>Hotel Name</th>
                <th>Comision Total</th>
              </tr>";

        // Recorrer los resultados y generar las filas de la tabla
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars($row['GuestFirstName'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars($row['GuestLastName'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars($row['HotelName'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlspecialchars($row['ComisionTotal'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "No se encontraron registros en la tabla 'comisiones'.";
    }

    // Cerrar el resultado y la conexión
    $result->free();
    $conn->close();

} catch (Exception $e) {
    echo "Error al ejecutar la consulta: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
?>

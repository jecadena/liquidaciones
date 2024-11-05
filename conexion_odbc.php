<?php
$dsn = 'DSN_NAME'; 
$user = 'usuario';
$password = 'contraseña';

$conn = odbc_connect($dsn, $user, $password);

if (!$conn) {
    die("Error en la conexión: " . odbc_errormsg());
} else {
    echo "Conexión establecida.<br />";
}

$sql = "SELECT * FROM tabla";
$result = odbc_exec($conn, $sql);

if (!$result) {
    die("Error en la consulta: " . odbc_errormsg($conn));
}

while ($row = odbc_fetch_array($result)) {
    echo $row['nombre_columna'] . "<br />";
}

odbc_free_result($result);
odbc_close($conn);
?>

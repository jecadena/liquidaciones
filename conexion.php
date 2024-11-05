<?php
//$servidor = "PC-MIGUEL";
$servidor = "SERVER10\SQL2008";
$basedatos = "bd_AgViajes";
$usr = "sa";
$pwd = "12345678";

try {
    $conn = new PDO("sqlsrv:Server=$servidor;Database=$basedatos", $usr, $pwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a SQL Server: " . $e->getMessage());
}

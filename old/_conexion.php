<?php
$host = 'http://localhost:3000';
$endpoint = '/documentos';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $host . $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
if(curl_errno($ch)) {
    echo 'Error al realizar la solicitud: ' . curl_error($ch);
    exit;
}
$data = json_decode($response, true);
if ($data && isset($data[0]['cp'], $data[0]['tip_doc'], $data[0]['serie'], $data[0]['docu'])) {
    $cp = $data[0]['cp'];
    $tip_doc = $data[0]['tip_doc'];
    $serie = $data[0]['serie'];
    $docu = $data[0]['docu'];

    echo "cp: $cp, tip_doc: $tip_doc, serie: $serie, docu: $docu";
} else {
    echo "No se recibieron datos válidos de la ruta '/documentos'.";
}

curl_close($ch);

?>
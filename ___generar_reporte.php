<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuLiquidacion = $_POST['nu_liquidacion'];
    $tipoLiquidacion = $_POST['tipo_liquidacion'];
    $observaciones = $_POST['de_obs_control_doc'];

    $data = array(
        'nu_liquidacion' => $nuLiquidacion,
        'tipo_liquidacion' => $tipoLiquidacion,
        'de_obs_control_doc' => $observaciones
    );

    $url = 'http://REMOTESERVER:9091/api/liquidacion/L/1';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    echo $response;
}
?>

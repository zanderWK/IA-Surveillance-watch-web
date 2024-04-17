<?php
$env = parse_ini_file('.env');
$Dashboard = $env["DASHBOARDURL"];


function fetchData($endpoint) {
    global $Dashboard;
    $apiBaseUrl = $Dashboard;
    $url = $apiBaseUrl . "/rest/" . $endpoint;
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        throw new Exception("cURL Error: " . $err);
    } else {
        return json_decode($response, true); 
    }
}

function postData($endpoint, $data) {
    global $Dashboard;
    $apiBaseUrl = $Dashboard;
    $url = $apiBaseUrl . "/rest/" . $endpoint;
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
     throw new Exception("cURL Error: " . $err);
    } else {
        return ['body' => json_decode($response, true), 'status' => $status];
    }
}

<?php

require '../../inc/psm.inc';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Request-Method: OPTIONS, POST");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

// Handle CORS preflight requests
//
// This is set to allow everyone for developement
// some related info here:
// https://stackoverflow.com/questions/8719276/cross-origin-request-headerscors-with-php-headers
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    return;
}


if($_SERVER['REQUEST_METHOD'] != 'POST') {
    client_error('Bad request');
    return;
}

// Search term
if (!array_key_exists('id', $_POST)) {
    client_error('"id" parameter is required');
    return;
}

// Country
if (!array_key_exists('gl', $_POST)) {
    client_error('"gl" (country) parameter is required');
    return;
}

$id = $_POST['id'];
$gl = strtolower($_POST['gl']);

if (!territory_is_valid($gl)) {
    client_error('Invalid or unsupported country code');
    return;
}

$store_on_failure = true;
if(array_key_exists('user_search', $_POST) && $_POST[user_search]) {
    $store_on_failure = false;
}

$response = test_app($id, $gl, $store_on_failure);

echo json_encode(
    array(
        'id' => htmlspecialchars($id),
        'gl' => $gl, // this has been validated
        'available' => $response->available,
    )
);
echo "\n";

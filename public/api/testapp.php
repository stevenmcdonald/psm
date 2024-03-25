<?php

require '../../inc/psm.inc';

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

$response = test_app($id, $gl);

echo json_encode(
    array(
        'id' => htmlspecialchars($id),
        'gl' => $gl, // this has been validated
        'available' => $response->available,
    )
);
echo "\n";

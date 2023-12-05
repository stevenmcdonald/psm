<?php

function client_error($msg) {
    http_response_code(400);
    echo json_encode(array(
        'message' => $msg
    ));
}

function fatal($message)
{
    header('HTTP/1.1 500 Internal Server Error: ' . $message);
    exit();
}

function h404() {
    echo 'Page not found';
    http_response_code(404);
    exit;
}

// Shuffle associative array
function shuffle_assoc(&$array, $limit = null) {
    $keys = array_keys($array);
    shuffle($keys);
    $new = [];
    foreach($keys as $key) {
        $new[$key] = $array[$key];
        if($limit && count($new) >= $limit) {
            break;
        }
    }
    $array = $new;
    return true;
}

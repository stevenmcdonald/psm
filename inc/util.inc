<?php

function client_error($msg) {
    http_response_code(400);
    echo json_encode(array(
        'message' => $msg
    ));
}

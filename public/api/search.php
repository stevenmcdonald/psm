<?php

require '../../inc/psm.inc';

// Search term
if (!array_key_exists('q', $_GET)) {
    client_error('"q" parameter is required');
    return;
}

$gl = 'us'; // XXX default to US?
if (array_key_exists('gl', $_GET)) {
    $gl = strtolower($_GET['gl']);
}

$results = search_play_store($_GET['q'], $gl);

echo json_encode(
    array(
        'ids' => $results
    )
);

?>

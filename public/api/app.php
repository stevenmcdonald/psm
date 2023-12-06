<?php

require '../../inc/psm.inc';

if (!isset($_GET['id'])) {
    h404();
}

$id = $_GET['id'];

$app = get_app($id);

if (!$app) {
    h404();
}

$agg_statuses = $mongodb_manager->executeQuery('psm.agg_statuses', new MongoDB\Driver\Query([
    '_id.id' => $app->_id
]))->toArray();

echo json_encode(array(
    'app' => $app,
    'agg_statuses' => $agg_statuses
));

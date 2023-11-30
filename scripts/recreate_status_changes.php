<?php

require '../inc/psm.inc';
ini_set('memory_limit', '8192M');

require 'recreate_status_changes.inc';

$changes = getChanges();
print_r($changes);
$significant_changes = array_filter($changes, 'isChangeSignificant');
print_r($significant_changes);

echo count($changes), ' changes', PHP_EOL;
echo count($significant_changes), ' significant changes', PHP_EOL;

global $mongodb_manager;
$mongodb_bulk = new MongoDB\Driver\BulkWrite();
foreach($significant_changes as $change) {
    $change->app = get_app($change->id);
    $mongodb_bulk->insert($change);
}
if (count($significant_changes)) {
    $mongodb_manager->executeBulkWrite('psm.status_changes_tmp', $mongodb_bulk);
}

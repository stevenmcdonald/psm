<?php

require '../inc/psm.inc';

function get_apps_page_ids($territory) {
    $request = array(
        'gl' => $territory
    );
    $url = PLAY_APPS_URL . '?' . http_build_query($request);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    // curl_setopt($ch, CURLOPT_PROXY, PROXY);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code == 200) {
        return array_unique(parse_search_results($html));
    } else {
        echo("error requesting apps: $code");
        return [];
    }
}

function get_test_territories() {
    global $mongodb_manager;
    $test_territories = ['us']; // always test the US

    $mongodb_query = new MongoDB\Driver\Query([]);
    $rows = $mongodb_manager->executeQuery('psm.territories', $mongodb_query);
    $territories = [];
    $general_territory_ids = [];
    foreach($rows as $row) {
        $territories[$row->_id] = $row;
    }

    // Also load territories from file, to make sure territories that have never been tested are included
    foreach(get_territories() as $territory => $name) {
        if(!isset($territories[$territory])) {
            $row = new stdClass;
            $row->_id = $territory;
            $row->apps_count = 0;
            $row->apps_unavailable = 0;
            $territories[$territory] = $row;
        }
    }

    // Always test US
    unset($territories['us']);

    // Territory with fewest tested apps
    usort($territories, function($a, $b) {
        return $a->apps_count - $b->apps_count;
    });
    $territory = array_shift($territories);
    $test_territories[] = $territory->_id;

    // Territory with most unavailable apps
    usort($territories, function($a, $b) {
        return $b->apps_unavailable - $a->apps_unavailable;
    });
    $territory = array_shift($territories);
    $test_territories[] = $territory->_id;

    // Random territory
    shuffle($territories);
    $territory = array_shift($territories);
    $test_territories[] = $territory->_id;

    return $test_territories;
}

$ids = [];

foreach(get_apps_page_ids('us') as $id) {
    $ids[$id] = [];
}

$test_territories = get_test_territories();

// Add apps that haven't been tested in a long time
$mongodb_query = new MongoDB\Driver\Query([], [
    'sort' => [
        'last_ts' => 1
    ],
    'limit' => 40
]);
$rows = $mongodb_manager->executeQuery('psm.agg_statuses', $mongodb_query);
foreach($rows as $row) {
    if(!isset($ids[$row->id])) {
        $ids[$row->id] = [];
    }
    $ids[$row->id][] = $row->territory;
}

// print_r($ids);
// print_r($test_territories);

$start = time();
foreach($ids as $id => $territory_ids) {
    $territory_ids = array_merge($territory_ids, $test_territories);
    $territory_ids = array_unique($territory_ids);
    foreach($territory_ids as $territory_id) {
        echo("testing $id in $territory_id:");
        $response = test_app($id, $territory_id);
        echo(($response->available ? '✅' : '❌') . "\n");
    }
    $duration = time() - $start;
    print "Duration: $duration\n";
    if($duration > 60 * 20) {
        print "Breaking\n";
        break;
    }
}

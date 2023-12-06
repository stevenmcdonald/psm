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

?>
<!doctype html>
<html>
<head><title><?php echo($app->name) ?></title>
</head>
<body>
<div>
    <div>
        <h1><?php echo($app->name) ?></h1>
        <img src="<?php echo($app->icon) ?>">
    </div>
    <div>
    <?php
    if (count($agg_statuses)) {
    ?>
        <table>
            <tr>
                <th>Territory</th>
                <th>Available?</th>
            </tr>
            <?php

                foreach($agg_statuses as $stat) {
                    echo('<tr><td>' . $stat->territory . '</td><td>');
                    echo($stat->last_available ? '✅' : '❌');
                    echo("</td></tr>\n");
                }
            ?>
        </table>
    <?php
    } else {
    ?>
        <div>
            <p>This app is new to PSM, we're still collecting data</p>
        </div>
    <?php
    }
    ?>
    </div>
</div>
</body>
</html>

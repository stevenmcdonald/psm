<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play Store Monitor</title>
</head>
<body>
    <div class="rows">
        <table>
            <tr>
                <th>timestamp</th>
                <th>ID</th>
                <th>Name</th>
                <th>Country</th>
                <th>Availbale</th>
            <tr>
<?php

require '../../inc/psm.inc';

global $mongodb_manager;

$filter = [];
$options = [
    'limit' => 40,
    'sort' => [
        'ts' => -1
    ]
];

$mongodb_query = new MongoDB\Driver\Query($filter, $options);
$rows = $mongodb_manager->executeQuery('psm.main', $mongodb_query);

foreach($rows as $row) {
    // echo '######### ' . $row->request->id . "\n";
    // var_dump($row->request);
    // echo "\n";
    // var_dump($row->response);
    $link = '/test/app.php?id=' . $row->request->id;
?>
        <tr>
            <td><?php echo $row->ts ?></td>
            <td><?php echo $row->request->id ?></td>
            <td><a href='<? php echo $link ?>'><?php echo $row->response->name ?></a></td>
            <td><?php echo $row->request->gl ?></td>
            <td><?php echo $row->response->available ? '✅' : '❌' ?></td>
        </tr>

<?php
}

?>
        </table>
    </div>
</body>
</html>
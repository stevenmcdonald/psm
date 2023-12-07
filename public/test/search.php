<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play Store Monitor</title>
    <style>
        div {
            margin-bottom: 1em;
        }
    </style>
</head>
<body>
<?php

require '../../inc/psm.inc';

$territories = get_territories();

?>
<h2>Play Store Montior</h2>
<div>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
        <div>
            <label for="q">Search:</label>
            <input type="text" name="q">

            <label for="search_territory">Search Territory</label>
            <select name="search_territory">
            <?php
            foreach ($territories as $t => $name) {
                $selected = '';
                if ($t === 'us') {
                    $selected = " selected";
                }
                echo "<option value='$t'$selected>$t - $name</option>\n";
            }
            ?>
            </select>
        </div>

        <div>
            <label for="test1">Test Territory 1</label>
            <select name="test1">
            <?php
            foreach ($territories as $t => $name) {
                $selected = '';
                if ($t === 'us') {
                    $selected = " selected";
                }
                echo "<option value='$t'$selected>$t - $name</option>\n";
            }
            ?>
            </select>

            <label for="test2">Test Territory 2</label>
            <select name="test2">
            <?php
            foreach ($territories as $t => $name) {
                $selected = '';
                if ($t === 'cn') {
                    $selected = " selected";
                }
                echo "<option value='$t'$selected>$t - $name</option>\n";
            }
            ?>
            </select>
        </div>

        <div>
            <button type="submit">Search</button>
        </div>

    </form>
</div>
<?php

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $term = $_POST['q'];

    $search_t = $_POST['search_territory'];
    $test1 = $_POST['test1'];
    $test2 = $_POST['test2'];

    $ids = search_play_store($term, $search_t);

?>
    <p>Searching in <?php echo(strtoupper($search_t)) ?></p>
    <table>
        <tr>
            <th>App</th>
            <th><?php echo(strtoupper($test1)) ?></th>
            <th><?php echo(strtoupper($test2)) ?></th>
        </tr>
<?php
    foreach($ids as $id) {
        ?><tr><?php

        $first_response = test_app($id, 'us');
        $second_response = test_app($id, 'cn');

        $name = $first_response->name;

        $first_avail = ($first_response->available == true) ? '✅' : '❌';
        $second_avail = ($second_response->available == true) ? '✅' : '❌';
        $link = '/test/app.php?id=' . $id;
        echo "<td><a href='$link'>$name</a></td><td>$first_avail</td><td>$second_avail</td>\n";

        ?></tr><?php
    }
}

?>
</body>
</html>
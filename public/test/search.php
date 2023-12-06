<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Play Store Monitor</title>
</head>
<body>
<?php

require '../../inc/psm.inc';

?>
<h2>Play Store Montior</h2>
<div>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
        <label for="q">Search:</label>
        <input type="text" name="q">
        <button type="submit">Search</button>
    </form>
</div>
<?php

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $term = $_POST['q'];

    $ids = search_play_store($term, 'us');

?>
    <table>
        <tr>
            <th>App</th>
            <th>US</th>
            <th>CN</th>
        </tr>
<?php
    foreach($ids as $id) {
        ?><tr><?php

        $us_response = test_app($id, 'us');
        $cn_response = test_app($id, 'cn');

        $name = $us_response->name;

        $us_avail = ($us_response->available == true) ? '✅' : '❌';
        $cn_avail = ($cn_response->available == true) ? '✅' : '❌';
        $link = '/test/app.php?id=' . $id;
        echo "<td><a href='$link'>$name</a></td><td>$us_avail</td><td>$cn_avail</td>\n";

        ?></tr><?php
    }
}

?>
</body>
</html>
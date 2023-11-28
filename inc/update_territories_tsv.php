<?php

// This fetches the list of territories, their names, and supported languages for
// the Play Store from apptweak, and writes it out as tab delimited territories.tsv

// I can't find an "official" source of this data
const SOURCE = 'https://www.apptweak.io/documentation/android/misc_countrycodes';

require 'vendor/autoload.php';

function fetch_html($url) {
    // Fetch the HTML
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
}

function convert_to_text($html) {

    try {
        $qp = html5qp($html);
    } catch (\QueryPath\Exception $e) {
        die('error loading html5qp');
    }

    // this is a pretty simple page, and all the data is in the table body
    $territories = [];
    foreach ($qp->find('tbody tr') as $row) {
        $tds = $row->find('td');
        $name = trim($tds->get(0)->textContent);
        $code = trim($tds->get(1)->textContent);
        $languages = trim($tds->get(2)->textContent);

        if ($name == 'China') {
            $name = 'China mainland';
        }

        if ($name == "Lao People's Democratic Republic") {
            $name = 'Laos';
        }

        // echo "$name, $code, $languages\n";
        $line = "$name\t$code\t$languages";
        array_push($territories, $line);
    }

    return implode("\n", $territories);
}

$html = fetch_html(SOURCE);
$text = convert_to_text($html);

file_put_contents('territories.tsv', $text);
echo "done!";

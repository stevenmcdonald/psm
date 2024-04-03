<?php

const PLAY_STORE_URL='https://play.google.com/store/apps/details';
const PLAY_SEARCH_URL='https://play.google.com/store/search';
const PLAY_APPS_URL='https://play.google.com/store/apps';


require 'local.inc';
require 'util.inc';

// composer autoload
require "vendor/autoload.php";

$mongodb_manager = new MongoDB\Driver\Manager(MONGODB_CONNECTION);


function get_play_store_url($territory, $name, $id) {
    return PLAY_STORE_URL . '?' . http_build_query(array(
        'id' => $name,
        'gl' => strtolower($territory)));
}

function get_app($id) {
    global $mongodb_manager;
    $mongodb_query = new MongoDB\Driver\Query([
        '_id' => $id
    ]);
    $rows = $mongodb_manager->executeQuery('psm.apps', $mongodb_query)->toArray();
    if ($rows) {
        return $rows[0];
    }
}

function play_store_proxy_request($request) {
    $url = PLAY_STORE_URL;
    $url .= '?' . http_build_query($request);

    for ($i = 0; $i < 10; $i ++) {
        $timeout = 8 + pow($i, 2);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_PROXY, PROXY);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        // parsing depends on the English text
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept-Language: en-US,en'
        ]);

        $html = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = array();

        switch($code) {
            case 200:
                $result['html'] = $html;
                break;
            case 404:
                $result['not_found'] = true;
                break;
            default:
                $result['error_code'] = $code;
        }

        return $result;
    }

    trigger_error('play_store_proxy_request failed');
}


function play_store_search($term, $territory) {
    $request = array(
        'q' => $term,
        'c' => 'apps',
        'gl' => $territory,
    );
    $url = PLAY_SEARCH_URL . '?' . http_build_query($request);

    for ($i = 0; $i < 10; $i ++) {
        $timeout = 8 + pow($i, 2);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_PROXY, PROXY);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        // parsing depends on the English text
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept-Language: en-US,en'
        ]);
        $html = curl_exec($ch);
        curl_close($ch);


        return $html;
    }

}

function parse_html_response($html) {
    $qp = html5qp($html);
    $response = new stdClass();

    // version this document so we can update them if we need to
    // add, remove, or change fields
    $response->version = 1;

    try {
        // links to other languages
        // XXX find doesn't find anything
        // foreach ($qp->find('link="alternate"]') as $l) {
        //     echo $l->text();
        // }

        // look for an "Install" button
        $free = null !== $qp->find('button[aria-label="Install"]')->get(0);
        // look for a button with a lable that ends with Buy
        // e.g. "$3.99 Buy"
        $paid = null !== $qp->find('button[aria-label$="Buy"]')->get(0);

        $response->available = $free || $paid;

        // App name
        $name_ele = $qp->find('h1[itemprop="name"]');
        $response->name = $name_ele->get(0)->textContent;

        // Developer name
        $dev_ele = $name_ele->siblings('div')->find('span');
        $response->developer = $dev_ele->get(0)->textContent;

        // App icon
        $app_icon_ele = $qp->find('img[alt="Icon image"]');
        $response->icon = $app_icon_ele->get(0)->getAttribute('src');

        // star rating
        $star_ele = $qp->find('div[aria-label*="Rated "]');
        // this value comes out as something like "4.2star", strip the "star"
        // and convert to a float
        $star_temp = $star_ele->get(0);
        if ($star_temp) {
            $response->star_rating = (float)str_replace('star', '', $star_temp->textContent);
        }

        // content rating
        $rating_ele = $qp->find('span[itemprop="contentRating"]');
        $response->content_rating = $rating_ele->get(0)->firstChild->textContent;

        // short description (from meta tag)
        $short_desc_ele = $qp->find('meta[itemprop="description"]');
        $response->short_description = $short_desc_ele->get(0)->getAttribute('content');

        // long description
        $desc_ele = $qp->find('div[data-g-id="description"]');
        $response->description = $desc_ele->get(0)->textContent;

        // updated date and category
        $sibs = $desc_ele->siblings('div');
        $updated_date_ele = $sibs->get(0)->firstChild->childNodes[1];
        $response->updated_date = strtotime($updated_date_ele->textContent);

        $categories = [];

        foreach ($sibs->find('span') as $cat_ele) {
            $category = $cat_ele->get(0)->textContent;
            if (!str_contains($category, '#')) {
                // this element can be e.g.
                // "#3 in top free social" (Facebook, as I write this)
                // we can skip those
                array_push($categories, $category);
                // the first category is the primary (? XXX)
                if (!isset($response->category)) {
                    $response->category = $category;
                }
            }
        }
        $response->categories = $categories;

        // screenshots
        $screenshots = [];
        foreach($qp->find('img[role="button"]')->get() as $i) {
            $link = $i->getAttribute('src');
            array_push($screenshots, $link);
        }

        $response->screenshots = $screenshots;
    } catch (Exception $ex) {
        error_log('parse error for ' . $request->id . ' in ' . $request->gl . ': '. $ex);
        $response->parse_error = true;
    }

    return $response;
}

// Given a search results page, return an array of the app IDs
// in the search results
//
// "Big" apps get a special case page, e.g.
// https://play.google.com/store/search?q=facebook&c=apps&gl=us
// https://play.google.com/store/search?q=snapchat&c=apps&gl=us
//
// In both cases, we're just looking for links to the Play Store
// to extract the App IDs
function parse_search_results($html) {

    $qp = html5qp($html);
    $results = [];

    foreach ($qp->find('a[href*="/store/apps/details"]') as $a) {
        $url = $a->get(0)->getAttribute('href');
        if (preg_match('/^\/store\/apps\/details\?id=(.*)$/', $url, $match)) {
            array_push($results, $match[1]);
        }
    }

    // sometimes we get dupes, the play store will link to the same app
    // in multiple places on the page
    return array_unique($results);
}

// search the Play Store and return a list of App IDs
function search_play_store($term, $territory) {
    $html = play_store_search($term, $territory);
    return parse_search_results($html);
}

// test if an App is available
function test_app($app_id, $territory) {
    $request = [];
    $request['id'] = $app_id;
    $request['gl'] = $territory;

    $result = play_store_proxy_request($request);

    if (isset($result['error_code'])) {
        # server error or some other problem.
        # Ambiguous results, don't save
        $response = new stdClass();
        $response->server_error = true;
        return $response;
    }

    if (isset($result['not_found'])) {
        $response = new stdClass();
        $response->not_found = true;
        $response->available = false;
    } else {
        $response = parse_html_response($result['html']);
        // $response->available = $available;
    }

    save_request_response($request, $response, 'server');

    return $response;
}


// Save the request and response for a given test
function save_request_response($request, $response, $source) {
    $data = new stdClass();
    $data->id = $request['id'];
    $data->request = $request;
    $data->response = $response;
    $data->source = $source;
    $data->ts = time();

    if(isset($_SERVER['REMOTE_ADDR'])) {
        $data->remote_addr_key = md5($_SERVER['REMOTE_ADDR']);
    }

    $collection = (new MongoDB\Client(MONGODB_CONNECTION))->psm->main;
    $insertOneResult = $collection->insertOne($data);
    $main_id = $insertOneResult->getInsertedId();

    // insert in to statuses collection
    $collection = (new MongoDB\Client(MONGODB_CONNECTION))->psm->statuses;

    $data2 = new stdClass();
    $data2->id = $request['id'];
    $data2->main_id = $main_id;
    $data2->territory = $request['gl'];
    $data2->available = $response->available;
    $data2->ts = $data->ts;

    $collection->insertOne($data2);

    if (!get_app($request['id'])) {
        $collection = (new MongoDB\Client(MONGODB_CONNECTION))->psm->apps;
        // create a placeholder entry in the apps collection
        $app = new stdClass();
        $app->_id = $request['id'];
        $app->name = $response->name;
        $app->first_unavailable_ts = null;
        $app->last_available_ts = 0;
        $app->last_unavailable_ts = 0;
        $app->unavailable_somewhere = 0;
        $app->icon = $response->icon;
        $app->territories = 1;
        $app->short_description = $response->short_description;
        $app->description = $response->description;
        $app->star_rating = $response->star_rating;
        $app->first_ts = $data->ts;
        $app->last_ts = $data->ts;
        $app->developer = $response->developer;

        $collection->insertOne($app);
    }

}


function get_territories() {
    static $territories;
    if(!isset($territories)) {
        $path = dirname(__DIR__) . '/public/territories.json';
        $json = file_get_contents($path);
        $territories = json_decode($json, true);
    }
    return $territories;
}

function territory_is_valid($territory) {
    $territories = get_territories();
    if (isset($territory, $territories)) {
        return true;
    }
    return false;
}

function territory_name($code) {
    $territories = get_territories();
    return $territories[$code];
}

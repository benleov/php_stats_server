<?php

/**
 * For application/AJAX use (JSON response).
 * Returns entries after the specified date.
 * 
 * EXAMPLE QUERY (All entries in the last 3 hours):
 *
 * http://localhost/json/latest_entries.php?user_key=1&tag=my_tag&date=-3%20hour
 *
 * EXAMPLE OUTPUT:
 *
 * [{"user":"1","tag":"my_tag","value":8,"time":"0.00000000 1335613421","id":"4f9bd7ed00bc646007000001"}]
 */
$user_key = filter_input(INPUT_GET, 'user_key', FILTER_SANITIZE_STRING);
$tag = filter_input(INPUT_GET, 'tag', FILTER_SANITIZE_STRING);
$date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING);
//$is_mongo = filter_input(INPUT_GET, 'is_mongo', FILTER_SANITIZE_STRING);
// TODO: not sure how to pass a mongodate string directly to the database?
$is_mongo = 'false';

include_once '../db_functions.php';

header("Content-type: text/json");

$entries = get_entries($user_key, $tag, $date, $is_mongo);
$ready = Array();

foreach ($entries as $entry) {

    // unwrap mongo's id and time objects, just return the real value
    $entry['id'] = (string) $entry['_id'];
    unset($entry['_id']);
    $entry['time'] = date('Y-m-d H:i:s', (string) $entry['time']->sec);
    array_push($ready, $entry);
}
// repack into an array so json_encode works properly
echo json_encode($ready);


<?php

include_once 'db.php';

function get_entries($user_key, $tag, $date, $is_mongo) {
    $collection = get_db_collection();

    if ($is_mongo === 'true') {
        $parsed = strtotime($date);
    } else {
        $parsed = new MongoDate(strtotime($date));
    }

    $cursor = $collection->find(array('user' => $user_key, 'tag' => $tag, 'time' => array('$gt' => $parsed)));
    return $cursor->sort(array('time' => 1)); // asc
}

function save_entry($user_key, $tag, $date, $value) {

    $collection = get_db_collection();
    $parsed = false;

    if (empty($date)) { // no date
        $obj = array("user" => $user_key, "tag" => $tag, "value" => floatval($value), "time" => new MongoDate());
    } else {

        $parsed = strtotime($date);

        if (!$parsed) {
            $obj = array("user" => $user_key, "tag" => $tag, "value" => floatval($value), "time" => new MongoDate());
        } else {
            $obj = array("user" => $user_key, "tag" => $tag, "value" => floatval($value), "time" => new MongoDate($parsed));
        }
    }

    $collection->insert($obj);
    return (string) $obj['_id'];
}

function increment_entry($object_id, $value) {
    $collection = get_db_collection();
    $item = $collection->findOne(array('_id' => new MongoId($object_id)));
    $newdata = array('$set' => array("value" => ($item['value'] + $value)));
    $collection->update(array("_id" => new MongoId($object_id)), $newdata);
    return $object_id;
}

function delete_entry($object_id) {
    $collection = get_db_collection();
    $collection->remove(array('_id' => new MongoId($object_id)), true);
    return $object_id;
}

function update_entry($object_id, $value) {
    $collection = get_db_collection();
    $item = $collection->findOne(array('_id' => new MongoId($object_id)));
    $newdata = array('$set' => array("value" => $value));
    $collection->update(array("_id" => new MongoId($object_id)), $newdata);
    return $object_id;
}

// example usage: 
// date_default_timezone_set('UTC');
// $results = sum('1', '2', 21000);
// print_r($results);

/**
 *   Returns an array of sum's.
 * 
 *   $user_key - Key of user to select
 *   $tag - Tag to select
 * 	$interval - Interval in milliseconds.
 *   $include_last - Boolean. If set to true, the last (incomplete) interval
 *   will be included.
 */
function sum($user_key, $tag, $interval, $include_last) {

    $interval = $interval / 1000;
    $results = array();

    $db = get_db_collection();

    // start time will be lowest date
    $max = $db->find(array(), array('time' => 1))->sort(array('time' => -1))->limit(1);

    // end time will be highest date
    $min = $db->find(array(), array('time' => -1))->sort(array('time' => 1))->limit(1);

    foreach ($max as $curr) {
        $num_max = $curr['time']->sec;
    }

    foreach ($min as $curr) {
        $num_min = $curr['time']->sec;
    }

    $duration = $num_max - $num_min;

    // number of iterations = (max - min) / interval, round up

    if ($include_last) {
        $iterations = ceil($duration / $interval);
    } else {
        $iterations = floor($duration / $interval);
    }

    $x = 0;

    while ($x < $iterations) {

        // current interval, start and finish times
        $marker_start = new MongoDate($num_min + ($interval * $x));
        $marker_end = new MongoDate($num_min + ($interval * ($x + 1)));

        $total = $db->group(
                array(), // keys
                array(// initial value
            'sum' => 0,
                ), new MongoCode('function(doc, out){ out.sum += doc.value; }'), array(
            'condition' => array(
                'user' => $user_key,
                'tag' => $tag,
                'time' => array(
                    '$gte' => $marker_start,
                    '$lt' => $marker_end
                )
            )
                )
        );


        foreach ($total as $curr) {
            // cut out any 0's, put into array
            //var_dump($curr);
            if (sizeof($curr) > 0 && !empty($curr[0]['sum'])) {
                //echo "Total for time period " .  date('Y-d-h-i:s', $marker_start ->sec) . " to " . date('Y-d-h-i:s', $marker_end ->sec) . " is: ", $curr[0]['sum'];
                $results[date('Y,m,d,H,i,s', $marker_end->sec)] = $curr[0]['sum'];
            }
        }

        $x++;
    }

    return $results;
}
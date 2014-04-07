<h1>Entries</h1>

<?php

/**
 * Prints all the entries in an HTML table.
 */
include_once "db.php";
$collection = get_db_collection();

$selected_tag = filter_input(INPUT_GET, 'tag', FILTER_SANITIZE_STRING);

if (empty($selected_tag)) {
    $cursor = $collection->find(array('user' => '1')); // only user 1
} else {
    $cursor = $collection->find(array('user' => '1', 'tag' => $selected_tag)); // only user 1
}

$cursor->sort(array('time' => -1)); // desc
// $start = new MongoDate(strtotime("2010-01-15 00:00:00"));
// date('Y-M-d h:i:s', $yourDate->sec); 
print "<table border=1	>";
print "<tr><td>user</td><td>tag</td><td>value</td><td>time</td></tr>";
foreach ($cursor as $obj) {
    echo "<tr>";
    echo "<td>" . $obj['user'] . "</td>";
    echo "<td>" . $obj['tag'] . "</td>";
    echo "<td>" . $obj['value'] . "</td>";
    echo "<td>" . date('Y-M-d h:i:s', $obj['time']->sec) . "</td>";
    echo "</tr>";
}
print "</table>";

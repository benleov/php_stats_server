<?php

date_default_timezone_set('UTC');

// NOTES
// dynamic db
// $db = $m->selectDB('test');
// dynamic collection:
// $collection = new MongoCollection($db, 'phpmanual');

function get_db_collection() {
    $m = new Mongo();

    // select a database (stats)
    // select a collection (analogous to a relational database's table)

    $collection = $m->stats->entries;

    // remove all from collection (uncomment and refresh to clear database)
    //$collection->remove();
    return $collection;
}

function get_db() {
    $m = new Mongo();

    // select a database (stats)

    $db = $m->stats;
    return $db;
}

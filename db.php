<?php

date_default_timezone_set('UTC');

/**
 * Selects the statastics database, and retreives the collection of entries.
 * 
 * @return Collection
 */
function get_db_collection() {
    $m = new Mongo();

    // select a database (stats)
    // select a collection (analogous to a relational database's table)

    $collection = $m->stats->entries;

    // remove all from collection (uncomment and refresh to clear database)
    //$collection->remove();

    return $collection;
}

/**
 * Removes all entries.
 */
function remove_all() {
    $m = new Mongo();
    $collection = $m->stats->entries;
    $collection->remove();
}

/**
 * Returns the statistics database, which contains the entries collection.
 * 
 * @return Database
 */
function get_db() {
    $m = new Mongo();
    $db = $m->stats;
    return $db;
}

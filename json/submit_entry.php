<?php

/**
 * For application/AJAx use (JSON response).
 */
$user_key = filter_input(INPUT_POST, 'user_key', FILTER_SANITIZE_STRING);
$tag = filter_input(INPUT_POST, 'tag', FILTER_SANITIZE_STRING);
$value = filter_input(INPUT_POST, 'value', FILTER_SANITIZE_STRING);
$date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
$object_id = filter_input(INPUT_POST, 'object_id', FILTER_SANITIZE_STRING);
$action_type = filter_input(INPUT_POST, 'action_type', FILTER_SANITIZE_STRING);

$message = "";
$success = false;
$id = "";

include_once '../db_functions.php';
$id = "null";

if (empty($object_id)) { // new entry
    if (empty($tag)) {
        $message = "invalid tag";
    } else {
        $id = save_entry($user_key, $tag, $date, $value);
        $success = true;
    }
} else {
    // update/delete/increment
    if (empty($object_id)) {
        $message = 'invalid_object_id';
    } elseif (empty($action_type)) {
        $message = 'invalid_action_type';
    } else {
        if ($action_type === "update") {
            $id = update_entry($object_id, $value);
            $success = true;
        } else if ($action_type === "delete") {
            $id = delete_entry($object_id);
            $success = true;
        } else if ($action_type == "increment") {
            $id = increment_entry($object_id, $value);
            $success = true;
        } else {
            $message = 'unknown_action_type';
        }
    }
}
// json response
$response = array('response' => array('success' => $success, 'message' => $message, 'id' => $id));
echo json_encode($response);
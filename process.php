<?php
 $selected_tag = filter_input(INPUT_POST, 'selected_tag', FILTER_SANITIZE_STRING);; // passed back to caller
 $user_key = filter_input(INPUT_POST, 'user_key', FILTER_SANITIZE_STRING);

 // new tag
 $tag = filter_input(INPUT_POST, 'tag', FILTER_SANITIZE_STRING);
 $value = filter_input(INPUT_POST, 'value', FILTER_SANITIZE_STRING);
 $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
 
 // modify existing
 $object_id = filter_input(INPUT_POST, 'object_id', FILTER_SANITIZE_STRING);
 $action_type = filter_input(INPUT_POST, 'action_type', FILTER_SANITIZE_STRING);
 
 if(!is_numeric($value)) {
	header( 'Location: index.php?m=invalid_value' );
 } else {
	 
	include_once "db_functions.php";
		
	if(empty($object_id)) { // new entry
		
		if(empty($tag)) {
			header( 'Location: index.php?m=invalid_tag');
		} else {
			$id = save_entry($user_key, $tag, $date, $value);
		}
	} else {
		
		if(empty($object_id)) {
			header( 'Location: index.php?m=invalid_object_id');
		} elseif (empty($action_type)) {
			header( 'Location: index.php?m=invalid_action_type');
		} else {
			if($action_type === "update") {
				$id = update_entry($object_id, $value);
			} else if ($action_type === "delete") {
				$id = delete_entry($object_id);
			} else if ($action_type == "increment") {
				$id = increment_entry($object_id, $value);
			} else {
				header( 'Location: index.php?m=unknown_action_type');
			}
		}
	}
	
	header( 'Location: index.php?tag=' . $selected_tag ."&id=" . $id);
	
}

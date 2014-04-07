<?php
$uploaded = $_FILES['import_file']['tmp_name'];

$row = 1;

if (($handle = fopen($uploaded, "r")) !== FALSE) {

	include_once 'db_functions.php';
	
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
		
   //     echo "<p> $num fields in line $row: <br /></p>\n";
       
		if($num == 4) {
		
			$user_key = $data[0];
			$tag = $data[1];
			$date = $data[2];
			$value = $data[3];
			
			save_entry($user_key, $tag, $date, $value);
			print "Entry imported <br />";
		} else {
			print "Cannot import this line. Must contain 4 columns <br />";
		}

		$row++;
    }
	
    fclose($handle);
}
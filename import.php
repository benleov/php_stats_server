<?php
/**
 * Displays the import form import form, which is processed by import_file.php.
 */
?>
<html>
    <head><title>import csv</title></head>

    <body>
        <form action='process_import.php' enctype="multipart/form-data" method='post'>
            <p>expected format for each row: USER KEY, TAG, DATE, VALUE</p>

            <h2>file</h2>
            <input id="import_file" type="file" name="import_file" />

            <input type="submit" />
        </form>

    </body>

</html>
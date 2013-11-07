<?php
require_once 'ThirdPartyLibraries/meekrodb.2.2.class.php';

DB::$user = 'root';
DB::$password = '';
DB::$dbName = 'restslim';
DB::$error_handler = 'database_error_handler';

/*
function database_error_handler()
{
	return -1;
}
*/

?>
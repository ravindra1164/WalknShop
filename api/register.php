<?php
include "settings.php";

if (isset($_GET['id']) && $_GET['id']!='' ){

	$new_user_id = $_GET['id'];
	
	$query = "insert into users ( installation_id ) values ( '$new_user_id' )"; 

	$db_conn = new mysqli( DB_HOST  , DB_USER , DB_PASSWORD , DB_NAME );

	$result = $db_conn->query($query) or die ('"success":false');

	echo '{"success":true}';

	$db_conn->close();
	
}
else{

	echo '{"success":true}';

}

?>

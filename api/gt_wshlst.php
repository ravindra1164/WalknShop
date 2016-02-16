<?php 

	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);

	include "settings.php";

	$conn = new mysqli( DB_HOST , DB_USER , DB_PASSWORD , DB_NAME );

	//$user_id = $_POST['user_id'];

	$user_id = $_GET['id'];

	$id_query = "SELECT id from users where installation_id='$user_id'";
	$id_result = $conn->query($id_query);
	$id_row = $id_result->fetch_assoc();
	$id = $id_row['id'];

	$query = "select c.category,c.subcategory from user_wishes uw left join categories c on uw.category_id = c.id where uw.user_id=$id";
	$result = $conn->query($query);

	$retval = array();
	$category_array = array();
	if($result === false)
	{
		echo '{"success":faslse}';
	}
	else
	{
		while($row = $result->fetch_assoc())
		{
			$category = $row['category'];
			$subcategory = $row['subcategory'];
			if(array_key_exists($category,$category_array)){
				$count = $category_array[$category];
				$retval['items'][$count]['subcategory'][] = $subcategory;
			}
			else{
				$category_array[$category] = sizeof($retval['items']);
				$retval['items'][] = array('title' => $category, 'subcategory' => array($subcategory));
			}
		}
	}

	echo json_encode($retval);

	$conn->close();

?>

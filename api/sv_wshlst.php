<?php

include "settings.php";
$db_conn = new mysqli( DB_HOST  , DB_USER , DB_PASSWORD , DB_NAME );
$user_id = $_POST['id'];
$saves = $_POST['saves'];
//$user_id = '55b35e97-1e7c-4bf3-b806-8b16334ccdbf';
//$saves = '[{"men": ["caps-hats-men", "casual-shirts-clothing-men"] } ]';
$id_query = "SELECT id from users where installation_id='$user_id'";
$id_result = $db_conn->query($id_query);
$id_row = $id_result->fetch_assoc();
$id = $id_row['id'];

$delete = "DELETE FROM user_wishes WHERE user_id=".$id;
$db_conn->query($delete);

$sub_query = "SELECT * FROM categories";
$sub_result = $db_conn->query($sub_query);
$subcategory_array = array();
while($sub_row = $sub_result->fetch_assoc()){
  $subcategory_array[$sub_row['subcategory']] = $sub_row['id'];
}
$saves_decode = json_decode($saves,true);
$insert = "";
foreach ($saves_decode as $key => $cat_array) {
  foreach ($cat_array as $cat => $sub_array) {
    foreach ($sub_array as $key => $subcategory) {
      if($subcategory=='')
      continue;
      $insert .= "($id,".$subcategory_array[$subcategory]."),";
    }
  }
}
if(!empty($insert)){
  $insert = "INSERT IGNORE INTO user_wishes(user_id,category_id) VALUES".$insert;
  $insert = rtrim($insert,",");
  if($db_conn->query($insert)) echo '{"success":true}'; else echo '{"success":false}';
}
$db_conn->close();
?>

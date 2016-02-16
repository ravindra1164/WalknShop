<?php
include "settings.php";
$db_conn = new mysqli( DB_HOST  , DB_USER , DB_PASSWORD , DB_NAME );

$user_id = '55b35e97-1e7c-4bf3-b806-8b16334ccdbf';
$lat = '17.4505434';
$lon = '78.3857182';
$id_query = "SELECT id from users where installation_id='$user_id'";
$id_result = $db_conn->query($id_query);
$id_row = $id_result->fetch_assoc();
$id = $id_row['id'];

$last_known = "SELECT * FROM last_known WHERE user_id=$id";
$last_result = $db_conn->query($last_known);
$num_of_rows = $last_result->num_rows;
if($num_of_rows >= 1){
  $last_known_row = $last_result->fetch_assoc();
  $last_lat = $last_known_row['lat'];
  $last_lon = $last_known_row['lon'];
  $store_array = explode(',', $last_known_row['stores']);

  $distance = distance($last_lat,$last_lon,$lat,$lon,'K');
  if($distance>0.5){
    $stores = mystores($id,$lat,$lon,$db_conn);
    $json = mywish($id,$stores,$db_conn);
  }
  else{
    $json = mywish($id,$last_known_row['stores'],$db_conn);
  }

}
else{
  $stores = mystores($id,$lat,$lon,$db_conn);
  $json = mywish($id,$stores,$db_conn);
}

print_r($json);
$db_conn->close();

function mystores($id,$lat,$log,$db_conn){
  $store_query = "SELECT * FROM str_lgn_dtls WHERE latitude<=".($lat+0.020000)." and latitude>=".($lat-0.020000)." and longitude<=".($log+0.020000)." and longitude>=".($log-0.020000);

  $store_result = $db_conn->query($store_query);
  $store_array = array();
  $store_strn = "";
  while($store_row = $store_result->fetch_assoc()){
      $store_array[] = $store_row['store_id'];
      $store_strn .= $store_row['store_id'].",";
  }
  $store_strn = rtrim($store_strn,',');
  $insert = "INSERT IGNORE INTO last_known(user_id,lat,lon,stores) VALUES($id,$lat,$log,'$store_strn') ON DUPLICATE KEY UPDATE lat=VALUES(lat) , lon=VALUES(lon), stores=VALUES(stores)";
  $db_conn->query($insert);
  return $store_strn;
}

function mywish($id, $stores, $db_conn) {
  $wish_query = "SELECT * FROM user_wishes WHERE user_id=".$id;
  $wish_result = $db_conn->query($wish_query);
  $wish_list_str = "";
  $wish_list = array();
  while($wish_row = $wish_result->fetch_assoc()){
    $wish_list[] = $wish_row['category_id'];
    $wish_list_str .= $wish_row['category_id'].",";
  }
  $wish_list_str = rtrim($wish_list_str,',');

  $store_query = "SELECT sld.store_id,sld.name,sld.latitude,sld.longitude,sp.category_id,c.subcategory FROM store_products sp left join str_lgn_dtls sld on sp.store_id=sld.store_id  left join  categories c on c.id=sp.category_id WHERE sp.store_id IN (".$stores.") AND sp.category_id IN (".$wish_list_str.")";
  $store_result = $db_conn->query($store_query);
  $result_array = array();
  while($store_row = $store_result->fetch_assoc()){
    $store_id = $store_row['store_id'];
    $store_name = $store_row['name'];
    $latitude = $store_row['latitude'];
    $longitude = $store_row['longitude'];
    $category_id = $store_row['category_id'];
    $subcategory = $store_row['subcategory'];
    if(array_key_exists($store_id, $result_array)){
      $result_array[$store_id]['items'][] = $subcategory;
    }
    else{
      $result_array[$store_id] = array('store_name'=>$store_name,'lat'=>$latitude,'long'=>$longitude,'items'=>array($subcategory));
    }
  }
  $final_array = array();
  foreach ($result_array as $key => $value) {
    $final_array[]=$value;
  }
  $final = array();
  $final['stores'] = $final_array;
   return json_encode($final);

}

function distance($lat1, $lon1, $lat2, $lon2, $unit) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}
//echo distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers<br>";
?>
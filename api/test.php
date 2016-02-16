<?php
/*
include "settings.php";

if ( isset($_POST['tag']) && $_POST['tag']!='' ){

	$db_conn = new mysqli(DB_HOST , DB_USER , DB_PASSWORD , DB_NAME );

	$time = date("Y/m/d");

	$query = "insert into api_test ( time_called ) values ( \" $time \" )";

	echo "Call Made";

	$db_conn->query($query);

	$db_conn->close();
}

<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&libraries=geometry"></script>
<script>
var p1 = new google.maps.LatLng(45.463688, 9.18814);
var p2 = new google.maps.LatLng(44.463688, 9.18814);

alert(calcDistance(p1, p2));

//calculates distance between two points in km's
function calcDistance(p1, p2){
  return (google.maps.geometry.spherical.computeDistanceBetween(p1, p2) ).toFixed(6);
}

</script>
*/
?>
<script>
var p1 = new google.maps.LatLng(45.463688, 9.18814);
var p2 = new google.maps.LatLng(44.463688, 9.18814);

alert(distance(p1, p2));
function distance(lat1, lon1, lat2, lon2, unit) {
var radlat1 = Math.PI * lat1/180
var radlat2 = Math.PI * lat2/180
var radlon1 = Math.PI * lon1/180
var radlon2 = Math.PI * lon2/180
var theta = lon1-lon2
var radtheta = Math.PI * theta/180
var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
dist = Math.acos(dist)
dist = dist * 180/Math.PI
dist = dist * 60 * 1.1515
if (unit=="K") { dist = dist * 1.609344 }
if (unit=="N") { dist = dist * 0.8684 }
return dist
}

</script>
<?php

include "settings.php";

?>

<html>
	<head>
		<title> Store Admin </title>
	</head>
	<body>
		<form id = "str_lgn" action = "login.php" method = "get" >
		<input type = "text" id = "username" name = "username" placeholder = "Username" /><br />
		<input type = "password" id = "password" name = "password" placeholder = "Password"  /><br />
			<input type = "submit" value = "submit">
		</form>
	</body>
</html>

<!DOCTYPE html>
<?php

include('../sql_config.php');
$db = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_dbname);
if(!$db) exit("Database connection error: ".mysqli_connect_error());


?>


<html>
<head>
	<link href="style.css" rel="stylesheet" type="text/css" media="all">
	<link rel="stylesheet" href="../fonts/fork-awesome/css/fork-awesome.min.css">

	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">

	<title> Manhattan - Bestellung </title>
</head>

<body>
	<div class="logo-background">
		<div class='logo'>
			<img src='../images/logo.png' alt='Manhattan' width='100%'>
		</div>
	</div>

	<div class='content'>

		<p> Hallo lieber Burgerliebhaber und Manhattan-Fan, </p> 
        <p> da du einen Beyond-Meat-Burger bestellt hast haben wir eine kurze Frage an dich: </p>

		<p><b> Wie wichtig ist dir die Marke Beyond Meat bei unseren Burgern? </b></p>
        <p>
            Seid ihr überzeugt von unseren hochwertigen Patties der Marke Beyond Meat oder 
            würdet ihr auch vergleichbare Patties von anderen Herstellern mit ähnlicher Qualität bestellen,
            die preislich etwas günstiger ausfallen?
        </p>
	</div>
</body>

</html>

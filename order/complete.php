<!DOCTYPE html>
<?php

include('../sql_config.php');
$db = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_dbname);
if(!$db) exit("Database connection error: ".mysqli_connect_error());

$price = mysqli_fetch_row(mysqli_query($db,
	"SELECT (CASE WHEN o.house = 1 THEN 0.5 ELSE 1 END) + SUM(price) as sum
	FROM orders o
		LEFT JOIN (
			SELECT p.order_id, p.position, 4.00 +
				(CASE WHEN p.patty = 0 THEN 0 ELSE 1.5 END) +
				(CASE WHEN p.bacon = 1 THEN 0.5 ELSE 0 END) +
				(CASE WHEN p.camembert = 1 THEN 0.5 ELSE 0 END) +
				(CASE WHEN p.beilage = 0 THEN 0 ELSE 1.4 END) +
				(CASE WHEN p.bier = 0 THEN 0 ELSE 1.4 END) as price
			FROM menu_positions p
			) AS positions ON (o.id = order_id)
	WHERE o.id = '$_GET[id]'"))[0];
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
			<a href='index.php'><img src='../images/logo.png' alt='Manhattan' width='100%'></a>
		</div>
	</div>

	<div class='content'>
		<h2> Vielen Dank für deine Bestellung! </h2>

		<p> Bitte überweise <b>möglichst bald</b> den Betrag von</p>

		<p id='complete_price'> <?php echo $price; ?> € </p>

		<p>
			an <a href='//paypal.me/manhattanburger'>paypal.me/manhattanburger</a> und gib als Verwendungszweck deine <b> Bestellnummer <?php echo $_GET['id'] ?></b> an.<br/>
			Denke daran, als "Freunde und Familie" zu bezahlen :)
		</p>
	</div>
</body>

</html>

<!DOCTYPE html>
<?php

include('../sql_config.php');
$db = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_dbname);
if(!$db) exit("Database connection error: ".mysqli_connect_error());

// Datenbankabfrage Settings
$sql = 'SELECT * FROM settings';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$results = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

$settings = [];
foreach($results as $result){
	$settings[$result['title']] = $result['value'];
}

// Datenbankabfrage Preis
$price = mysqli_fetch_row(mysqli_query($db,
	"SELECT (CASE WHEN o.house = 1 THEN 0.5 ELSE 1 END) + SUM(price) as sum
	FROM orders o
		LEFT JOIN (
			SELECT p.order_id, p.position, 4.00 +
				(CASE WHEN p.patty = 0 THEN 0 ELSE 1.5 END) +
				(CASE WHEN p.bacon = 1 THEN 0.5 ELSE 0 END) +
				(CASE WHEN p.camembert = 1 THEN 0.5 ELSE 0 END) +
				(CASE WHEN p.beilage = 0 THEN 0 ELSE 1.4 END) +
				(CASE WHEN p.dip_1 = 1 THEN 0.1 ELSE 0 END) +
				(CASE WHEN p.dip_2 = 1 THEN 0.1 ELSE 0 END) +
				(CASE WHEN p.bier = 0 THEN 0 ELSE
					(CASE WHEN p.bier = 10 OR p.bier = 11 THEN 2.5 ELSE 1.4 END)
				END) as price
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
			<img src='../images/logo.png' alt='Manhattan' width='100%'>
		</div>
	</div>

	<div class='content'>
		<h2> Vielen Dank für deine Bestellung! </h2>

		<p> Bitte überweise <b>möglichst bald</b> den Betrag von</p>

		<p id='complete_price'> <?php echo $price; ?> € </p>

		<p>
			an <a href="https://<?php echo($settings['paypal_url']); ?>" target="_blank"><?php echo($settings['paypal_url']); ?></a> und gib als Verwendungszweck deine <b> Bestellnummer <?php echo $_GET['id'] ?></b> an.<br/>
			Denke daran, als "Freunde und Familie" zu bezahlen :)
		</p>
	</div>
</body>

</html>

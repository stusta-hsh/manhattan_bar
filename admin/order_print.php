<!DOCTYPE html>

<head>
	<title> Manhattan - Bestellungen </title>
</head>

<body>
	<?php

	include('../sql_config.php');
	$db = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_dbname);
	if(!$db) exit("Database connection error: ".mysqli_connect_error());

	$orders = mysqli_query($db, "SELECT o.id, o.date, o.name, o.phone, o.paid, o.comment, h.name as house, o.room FROM orders o JOIN houses h ON (o.house = h.id) ORDER BY -o.paid, o.house, o.room");

	foreach ($orders as $order) { ?>

		<p> --------------------------------------------------------------------------- </p>
		<p> ## Bestellung <?php echo $order['id']; ?> (<?php echo $order['date']; ?>) ## </p>
		<p> <?php echo $order['name']; ?> (<?php echo $order['house']; ?>, <?php echo $order['room']; ?>) </br> Telefon: <?php echo $order['phone']; ?> </p>
		<?php if(!$order['paid']) echo '<p> NICHT BEZAHLT </p>'; ?>

		<ul>
			<?php $positions = mysqli_query($db, "SELECT * FROM menu_positions WHERE order_id = $order[id]");
			foreach ($positions as $position) { ?>

				<?php
					switch ($position['patty']) {
						case 0:
							$position['cheese'] ? $burger = 'Cheeseburger' : $burger = 'Hamburger';
							break;
						case 1:
							$burger = 'Beyond-Meat-Burger';
							if($position['cheese']) $burger .= ' mit Käse';
							break;
						case 2:
							$burger = 'Double-Burger';
							if($position['cheese']) $burger .= ' mit Käse';
							break;
					}
				?>

				<li>
				 <strong><?php echo $burger ?></strong>
					<ul>
						<?php if(!$position['salad']) { ?> <li> OHNE Salat </li> <?php } ?>
						<?php if(!$position['tomato']) { ?> <li> OHNE Tomate </li> <?php } ?>
						<?php if(!$position['onion']) { ?> <li> OHNE Zwiebel </li> <?php } ?>
						<?php if(!$position['sauce']) { ?> <li> OHNE Sauce </li> <?php } ?>
						<?php if($position['friedonions']) { ?> <li> MIT Röstzwiebeln </li> <?php } ?>
						<?php if($position['pickles']) { ?> <li> MIT Essiggurken </li> <?php } ?>
						<?php if($position['bacon']) { ?> <li> Extra: Bacon </li> <?php } ?>
						<?php if($position['camembert']) { ?> <li> Extra: Camembert </li> <?php } ?>
						<?php if($position['beilage'] == 1) { ?> <li> Beilage: Pommes </li> <?php } ?>
						<?php if($position['beilage'] == 2) { ?> <li> Beilage: Wedges </li> <?php } ?>
						<?php if($position['bier'] != 0) {
							$bier = '';
							switch ($position['bier']) {
								case 1: $bier = 'Augustiner'; break;
								case 2: $bier = 'Tegernseer Spezial'; break;
								case 3: $bier = 'Weißbier TAP7'; break;
								case 4: $bier = 'Alkoholfreies Weißbier TAP3'; break;
								case 5: $bier = 'Kuchlbauer dunkles Weißbier'; break;
								case 6: $bier = 'Weihenstephaner Radler'; break;
								case 7: $bier = 'Paulaner Spezi'; break;
								case 8: $bier = 'Almdudler'; break;
								case 9: $bier = 'Club Mate'; break;
								default: break;
							}
							echo "<li> Getränk: $bier </li>";
						} ?>
					</ul>
				</li>
			<?php } ?>
		</ul>
		<?php if($order['comment'] != '') echo '<p>** '.$order['comment'].' **</p>'; ?>
	<?php } ?>
</body>

<!DOCTYPE html>

<head>
	<title> Manhattan - Bestellungen </title>
</head>

<body>
	<?php

	include('../sql_config.php');
	$db = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_dbname);
	if(!$db) exit("Database connection error: ".mysqli_connect_error());

	$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

	$orders = mysqli_query($db,
	"SELECT o.id, o.date, o.slot, o.name, o.phone, o.paid, o.comment, h.name as house, o.room
	FROM orders o JOIN houses h ON (o.house = h.id)
	WHERE deleted = 0 AND DATE(date) = '$date'
	ORDER BY -o.paid, o.slot, o.house, o.room");

	foreach ($orders as $order) { ?>

		<p> --------------------------------------------------------------------------- </p>
		<p> ## Slot <?php echo($order['slot'] + 1); ?> - Bestellung <?php echo $order['id']; ?> (<?php echo $order['date']; ?>) ## </p>
		<p>
			<?php echo $order['house']; ?>, <?php echo $order['room']; ?>,
			<?php echo $order['name']; ?>, Tel: <?php echo $order['phone']; ?>
		</p>
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
							break;
						case 2:
							$burger = 'Double-Burger';
							break;
					}
				?>

				<li><strong><?php echo "$burger"; ?></strong>

				<?php
					if(($position['cheese'] && ($position['patty'] != 0)) || $position['bacon'] || $position['camembert']){
						echo '<ul><li>';
						if($position['cheese'] && ($position['patty'] != 0)) { ?>Käse, <?php }
						if($position['bacon']) { ?>Bacon<?php }
						if($position['bacon'] && $position['camembert']) echo ', ';
						if($position['camembert']) { ?>Camembert<?php }?>
						</li></ul><?php
					} else {
						echo '<br>';
					}
					if($position['friedonions'] || $position['pickles']) { ?>
						<ul><li>
						<?php if($position['friedonions']) { ?>Röstzwiebeln<?php } ?>
						<?php if($position['friedonions'] && $position['pickles']) echo ', ' ?>
						<?php if($position['pickles']) { ?>Essiggurken<?php } ?>
					</li></ul><?php
					}
					if ($position['beilage'] != 0){
						echo '<strong>';
						echo ($position['beilage'] == 1 ? 'Pommes' : 'Wedges');
						echo '</strong><br>';
					} ?>

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
						echo "<ul><ul><ul><strong>Getränk </strong>".$bier.'</ul></ul></ul>';
					} ?><br>

					<!--
					<ul><ul><ul><ul>
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
							echo "<li> $bier </li>";
						} ?>
					</ul></ul></ul></ul>
					<br>--></li>
			<?php } ?>
		</ul>
		<?php if($order['comment'] != '') echo '<p>** '.$order['comment'].' **</p>'; ?>
	<?php } ?>
</body>

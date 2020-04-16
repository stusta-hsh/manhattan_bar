<?php
$page_title='order';

include('header.php');
include('order_header.php');

$date = isset($_GET['date']) ? strtotime($_GET['date']) : time();
$soll = 0; $ist = 0;

if ($_POST) {
	$sql_pay = ""; $sql_unpay = "";
	foreach ($_POST as $key=>$value) {
		if ($value) { $sql_pay .= "OR id = $key "; }
		else { $sql_unpay .= "OR id = $key "; }
	}
	mysqli_query($db, "UPDATE orders SET paid = 1 WHERE FALSE $sql_pay");
	mysqli_query($db, "UPDATE orders SET paid = 0 WHERE FALSE $sql_unpay");

	if (isset($_GET['delete'])) {
		mysqli_query($db, "UPDATE orders SET deleted = 1 WHERE id = " . $_GET['delete']);
	}
}

?>
	<div class="content">
		<div class="card">
			<div class="card-title">
				<a class="card-title-arrow-left" href="order_list.php?date=<?php echo date('Y-m-d', $date - 60 * 60 * 24) // TODO letzten öffnungstag herausfinden ?>">
					<i class='fa fa-chevron-left'></i>
				</a>
				<?php echo(' ' . strftime('%a, %d. %b %Y', $date) . ' '); ?>
				<a class="card-title-arrow-right" href="order_list.php?date=<?php echo date('Y-m-d', $date + 60 * 60 * 24) ?>">
					<i class='fa fa-chevron-right'></i>
				</a>
			</div>
			<div class="card-content">
				Slot 1: <?php echo mysqli_fetch_row(mysqli_query($db, "SELECT COUNT(slot) FROM orders WHERE deleted = 0 AND DATE(date) = '" . date('Y-m-d') . "' AND slot = 0 GROUP BY slot"))[0];?>/25,
				Slot 2: <?php echo mysqli_fetch_row(mysqli_query($db, "SELECT COUNT(slot) FROM orders WHERE deleted = 0 AND DATE(date) = '" . date('Y-m-d') . "' AND slot = 1"))[0];?>/25
				<form method='post'>
					<table>
						<tr>
							<th style="text-align: left">ID</th>
							<th style="text-align: left">Zeit</th>
							<th style="text-align: left">Slot</th>
							<th style="text-align: left">Name</th>
							<th style="text-align: right">Preis</th>
							<th style="text-align: right">Bezahlt</th>
							<th/>
						</tr>

						<?php

						$orders = mysqli_query($db,
						"SELECT o.id, TIME(o.date) as time, o.slot, o.name, o.paid,
							(CASE WHEN o.house = 1 THEN 0.5 ELSE 1 END) +
							SUM(price) as sum
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
						WHERE deleted = 0 AND DATE(o.date) = '" . date('Y-m-d', $date) . "' GROUP BY o.id");

						foreach ($orders as $order) { ?>
							<tr>
								<td style="text-align: left"> <?php echo $order['id']; ?> </td>
								<td style="text-align: left"> <?php echo $order['time']; ?> </td>
								<td style="text-align: left"> <?php echo $order['slot']+1; ?> </td>
								<td style="text-align: left"> <?php echo $order['name']; ?> </td>
								<td style="text-align: right"> <?php echo $order['sum']; ?> € </td>
								<td>
									<input type='hidden' name='<?php echo $order['id'] ?>' value='0'/>
									<input type='checkbox' name='<?php echo $order['id'] ?>' value='1' <?php echo ($order['paid'] ? "checked='checked'" : ""); ?> />
									<?php // TODO: checkbox nur submitten, wenn sie tatsächlich geändert wird ?>
								</td>
								<td> <button class="fa fa-trash" type="input"
									formaction="order_list.php?date=<?php echo date('Y-m-d', $date) ?>&delete=<?php echo $order['id'] ?>" />
								</td>
							</tr>
						<?php
							$soll += $order['sum'];
							$ist += $order['paid'] ? $order['sum'] : 0;
						} ?>
					</table>
					<input type='submit' value='Speichern'/>
				</form>
				<p> <?php printf("eingeganene Zahlungen: %s€ (von %s€)", $ist, $soll); ?> </p>
			</div>
		</div>
	</div>
</body>
</html>

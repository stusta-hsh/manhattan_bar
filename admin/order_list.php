<?php
$page_title='order';

include('header.php');
include('order_header.php');

if ($_POST) {
	$sql_pay = ""; $sql_unpay = ""; 
	foreach ($_POST as $key=>$value) {
		if ($value) { $sql_pay .= "OR id = $key "; }
		else { $sql_unpay .= "OR id = $key "; }
	}
	mysqli_query($db, "UPDATE orders SET paid = 1 WHERE FALSE $sql_pay");
	mysqli_query($db, "UPDATE orders SET paid = 0 WHERE FALSE $sql_unpay");
}

?>
	<form method='post' class="content">
		<h3>Bestellungen</h3>
		<table>
			<tr>
				<th style="text-align: left">ID</th>
				<th style="text-align: left">Zeit</th>
				<th style="text-align: left">Name</th>
				<th style="text-align: right">Preis</th>
				<th style="text-align: right">Bezahlt</th>
			</tr>

			<?php
			
			$orders = mysqli_query($db, 
			'SELECT o.id, o.date, o.name, o.paid,
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
			GROUP BY o.id');
			
			foreach ($orders as $order) { ?>
				<tr>
					<td style="text-align: left"> <?php echo $order['id']; ?> </td>
					<td style="text-align: left"> <?php echo $order['date']; ?> </td>
					<td style="text-align: left"> <?php echo $order['name']; ?> </td>
					<td style="text-align: right"> <?php echo $order['sum']; ?> </td>
					<td>
						<input type='hidden' name='<?php echo $order['id'] ?>' value='0'/>
						<input type='checkbox' name='<?php echo $order['id'] ?>' value='1' <?php echo ($order['paid'] ? "checked='checked'" : ""); ?> />
						<?php // TODO: checkbox nur submitten, wenn sie tatsächlich geändert wird ?>
					</td>
				</tr>
			<?php } ?>

		</table>
		<input type='submit' value='Speichern'/>
	</form>
</body>
</html>
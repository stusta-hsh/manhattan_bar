<?php

$page_title='settings';
include('header.php');

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

// Änderungen in die Datenbank schreiben
if($_POST){
	foreach($_POST as $title => $value){
		$sql = 'UPDATE settings SET value = ? WHERE title = ?';
		$sql_query = mysqli_prepare($db, $sql);
		if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
		mysqli_stmt_bind_param($sql_query, 'ss', $value, $title);
		mysqli_stmt_execute($sql_query);
		mysqli_stmt_close($sql_query);
	}
	header('Location: settings_delivery.php');
	exit();
}

include('settings_header.php');

?>
	<div class="content">
		<div class="card">
			<div class="card-title">Lieferservice</div>
			<div class="card-content">
				<form class="card-content-form" method='post' action=''>
					<div class="card-form-box">
						<div class="card-form-row">
							<label class="flex-200">Bestellannahme jeden
								<select name="order_weekday">
									<option value="1" <?php if($settings['order_weekday']==1)echo'selected' ?>>Montag</option>
									<option value="2" <?php if($settings['order_weekday']==2)echo'selected' ?>>Dienstag</option>
									<option value="3" <?php if($settings['order_weekday']==3)echo'selected' ?>>Mittwoch</option>
									<option value="4" <?php if($settings['order_weekday']==4)echo'selected' ?>>Donnerstag</option>
									<option value="5" <?php if($settings['order_weekday']==5)echo'selected' ?>>Freitag</option>
									<option value="6" <?php if($settings['order_weekday']==6)echo'selected' ?>>Samstag</option>
									<option value="0" <?php if($settings['order_weekday']==0)echo'selected' ?>>Sonntag</option>
									<option value="7" <?php if($settings['order_weekday']==7)echo'selected' ?>>deaktivieren</option>
								</select>
							</label>
							<label class="flex-100">von
								<input type="time" name="order_opentime" value="<?php echo $settings['order_opentime'] ?>">
							</label>
							<label class="flex-100">bis
								<input type="time" name="order_closetime" value="<?php echo $settings['order_closetime'] ?>">
							</label>
						</div>
						<div class="card-form-row">
							<label class="flex-100">Anzahl Bestellungen je Slot
								<input type="number" name="order_max_slot" value="<?php echo $settings['order_max_slot'] ?>">
							</label>
							<label class="flex-100">Anzahl Menüs je Bestellung
								<input type="number" name="order_max_position" value="<?php echo $settings['order_max_position'] ?>">
							</label>
						</div>
						<div class="card-form-row">
							<label class="flex-300">PayPal-Link
								<input type="text" name="paypal_url" value="<?php echo $settings['paypal_url'] ?>">
							</label>
						</div>
					</div>
					<input type='submit' value='Anwenden'></input>
				</form>
			</div>
		</div>
	</div>

</body>
</html>

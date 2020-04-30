<?php

$page_title='team';

include('header.php');

// Datenbankabfrage Häuser
$sql = 'SELECT id, name, alias FROM houses ORDER BY no ASC';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$houses = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

// Neuen Mitarbeiter speichern
if($_POST){
	if($_POST['display_name'] == '') $_POST['display_name'] = $_POST['first_name'];
	$sql = 'INSERT INTO employees ('.implode(', ', array_slice(array_keys($_POST),0,sizeof($_POST))).') VALUES ('.str_repeat('?, ', sizeof($_POST)-1).'?)';
	$sql_query = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($sql_query, 'sssiisssiisiiiiiis', ...array_values($_POST));
	mysqli_stmt_execute($sql_query);
	mysqli_stmt_close($sql_query);
	header('Location: employee_edit.php?id='.$_POST['id']);
	exit();
}

include('employee_header.php');

?>
	<div class="content">
		<div class="card">
			<div class="card-title">
				Neuer Mitarbeiter
			</div>
			<div class="card-content">
				<form class="card-content-form" method='post' action=''>
					<div class="card-form-box">
						<div class="card-form-row">
							<label class="flex-100">Vorname
								<input name="first_name" type="text">
							</label>
							<label class="flex-100">Nachname
								<input name="last_name" type="text">
							</label>
							<label class="flex-100" title="Wenn leer, wird der Vorname angezeigt">Spitzname (Anzeigename)
								<input name="display_name" type="text">
							</label>
						</div>
						<div class="card-form-row">
							<label class="flex-200">Haus
								<select name="house">
									<?php foreach($houses as $house){ ?>
										<option value='<?php echo $house['id'] ?>' <?php if($house['name']=='HSH')echo'selected' ?>><?php echo $house['name']; if(!empty($house['alias']))echo(' ('.$house['alias'].')'); ?></option>
									<?php } ?>
								</select>
							</label>
							<label class="flex-100">Zimmer
								<input name="room_number" type="number">
							</label>
							<label class="flex-100">Geburtstag
								<input name="birthday" type="date">
							</label>
						</div>
						<div class="card-form-row">
							<label class="flex-100">E-Mail
								<input name="email" type="email">
							</label>
							<label class="flex-100">Handy
								<input name="phone" type="tel">
							</label>
						</div>
					</div>
					<div class="card-form-box">
						<div class="card-form-row">
							<input name="active" type="hidden" value="1">
							<label class="flex-100">Position
								<select name="role">
									<option value="0">Mitarbeiter</option>
									<option value="1">Betreiber</option>
									<option value="2">Ausschussmitglied</option>
								</select>
							</label>
							<label class="flex-100">Im Team seit
								<input name="date_employed" type="date" value="<?php echo(date('Y-m-d')); ?>">
							</label>
						</div>
						<div class="card-form-row">
							<label>Einarbeitung Theke
								<input name="training_0" type="hidden" value="0">
								<input name="training_0" type="checkbox" value="1">
							</label>
							<label>Einarbeitung Dachterrasse
								<input name="training_1" type="hidden" value="0">
								<input name="training_1" type="checkbox" value="1">
							</label>
							<label>Einarbeitung Küche
								<input name="training_2" type="hidden" value="0">
								<input name="training_2" type="checkbox" value="1">
							</label>
						</div>
						<div class="card-form-row">
							<label>Hygienebelehrung
								<input name="health_certificate" type="hidden" value="0">
								<input name="health_certificate" type="checkbox" value="1">
							</label>
							<label>Einkäufer
								<input name="buyer" type="hidden" value="0">
								<input name="buyer" type="checkbox" value="1">
							</label>
							<label>Putzkraft
								<input name="cleaner" type="hidden" value="0">
								<input name="cleaner" type="checkbox" value="1">
							</label>
						</div>
						<div class="card-form-row">
							<label class="flex-300">Kommentar
								<textarea rows="4" name="comment"><?php echo $employee['comment'] ?></textarea>
							</label>
						</div>
					</div>
					<input type='submit' value='Speichern'>
				</form>
			</div>
		</div>
	</div>
</body>
</html>

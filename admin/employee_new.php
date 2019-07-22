<?php

$page_title='team';

include('header.php');

// Datenbankabfrage Häuser
$sql = 'SELECT id, name, alias FROM houses ORDER BY no ASC';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: could not prepare sql: $sql');
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
	<div class='content'>
		<div class='edit_employee_form'>
			<form method='post' action=''>
				<h3>Neuer Mitarbeiter</h3>
				<div class="employee-card">
					<label>Vorname
						<input name="first_name" type="text">
					</label>
					<label>Nachname
						<input name="last_name" type="text">
					</label>
					<label title="Wenn leer, wird der Vorname angezeigt">Spitzname (Anzeigename)
						<input name="display_name" type="text">
					</label>

					<br>
					<label>Haus
						<select name="house">
							<?php foreach($houses as $house){ ?>
								<option value='<?php echo $house['id'] ?>' <?php if($house['name']=='HSH')echo'selected' ?>><?php echo $house['name']; if(!empty($house['alias']))echo(' ('.$house['alias'].')'); ?></option>
							<?php } ?>
						</select>
					</label>
					<label>Zimmer
						<input name="room_number" type="number">
					</label>
					<label>Geburtstag
						<input name="birthday" type="date">
					</label>

					<br>
					<label>E-Mail
						<input name="email" type="email">
					</label>
					<label>Handy
						<input name="phone" type="fon">
					</label>

				</div>
				<div class="employee-card">
					<label>Aktiv
						<input name="active" type="hidden" value="0">
						<input name="active" type="checkbox" value="1" checked>
					</label>
					<label>
						<input type="radio" name="role"  value="0" checked>Mitarbeiter
						<input type="radio" name="role"  value="1">Betreiber
						<input type="radio" name="role"  value="2">Ausschuss
					</label>

					<br>
					<label>Im Team seit
						<input name="date_employed" type="date" value="<?php echo(date('Y-m-d')); ?>">
					</label>

					<br>
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

					<br>
					<label>Kommentar
						<textarea rows="4" name="comment"><?php echo $employee['comment'] ?></textarea>
					</label>
				</div>
				<input type='submit' value='Speichern'>
			</form>
		</div>
	</div>
</body>
</html>

<?php

$page_title='team';

include('header.php');

// Datenbankabfrage Mitarbeiter
$sql = 'SELECT employees.*, houses.name AS "house.name" FROM employees LEFT JOIN houses ON employees.house = houses.id WHERE employees.id=? AND employees.deleted=0';
$sql_query = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($sql_query, 'i', $id);
if (!$sql_query) die('ERROR: could not prepare sql: $sql');
mysqli_stmt_execute($sql_query);
$employee = mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query));
mysqli_stmt_close($sql_query);

// Datenbankabfrage Anzahl Schichten
$sql = 'SELECT COUNT(mo_theke) AS total_shifts FROM schedules WHERE ? in
(
	mo_theke, mo_springer, mo_kueche,
	di_theke, di_springer, di_kueche,
	mi_theke, mi_springer, mi_kueche,
	do_theke, do_springer, do_kueche,
	fr_theke, fr_springer, fr_kueche,
	sa_theke, sa_springer, sa_kueche,
	so_theke, so_springer, so_kueche
)';
$sql_query = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($sql_query, 'i', $employee['id']);
if (!$sql_query) die('ERROR: could not prepare sql: $sql');
mysqli_stmt_execute($sql_query);
$total_shifts = mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query))['total_shifts'];
mysqli_stmt_close($sql_query);

// Datenbankabfrage Häuser
$sql = 'SELECT id, name, alias FROM houses ORDER BY no ASC';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: could not prepare sql: $sql');
mysqli_stmt_execute($sql_query);
$houses = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

if(empty($employee)){
	header('Location: employee_list.php');
	exit();
}

if($_POST){
	if($_POST['display_name'] == '') $_POST['display_name'] = $_POST['first_name'];
	$sql = 'UPDATE employees SET '.implode(' = ?, ', array_slice(array_keys($_POST),0,sizeof($_POST)-1)).' = ? WHERE id = ?';
	$sql_query = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($sql_query, 'sssiisssiisiiiiiisi', ...array_values($_POST));
	mysqli_stmt_execute($sql_query);
	mysqli_stmt_close($sql_query);
	header('Location: employee_list.php');
	exit();
}

include('employee_header.php');

?>
	<div class='content'>
		<div class='edit_employee_form'>
			<form method='post' action=''>
				<?php if(!empty($employee_previous)){ ?>
					<a href='schedule_edit.php?id=<?php echo $employee_previous['id'] ?>'><i class='fa fa-chevron-left' aria-hidden='true'></i><?php echo $employee_previous['first_name'] ?></a>
				<?php } ?>
				<h3><?php echo parse_employee_name($employee, false); ?></h3>
				<?php if(!empty($employee_next)){ ?>
					<a href='schedule_edit.php?id=<?php echo $employee_next['id'] ?>'><?php echo $employee_next['first_name'] ?><i class='fa fa-chevron-right' aria-hidden='true'></i></a>
				<?php } ?>
				<div class="employee-card">
					<label>Vorname
						<input name="first_name" type="text" value="<?php echo $employee['first_name'] ?>">
					</label>
					<label>Nachname
						<input name="last_name" type="text" value="<?php echo $employee['last_name'] ?>">
					</label>
					<label title="Wenn leer, wird der Vorname angezeigt">Spitzname (Anzeigename)
						<input name="display_name" type="text" value="<?php if($employee['display_name']!=$employee['first_name']) echo $employee['display_name'] ?>">
					</label>

					<br>
					<label>Haus
						<select name="house">
							<?php foreach($houses as $house){ ?>
								<option value='<?php echo $house['id'] ?>' <?php if($employee['house']==$house['id'])echo'selected' ?>><?php echo $house['name']; if(!empty($house['alias']))echo(' ('.$house['alias'].')'); ?></option>
							<?php } ?>
						</select>
					</label>
					<label>Zimmer
						<input name="room_number" type="number" value="<?php echo $employee['room_number'] ?>">
					</label>
					<label>Geburtstag
						<input name="birthday" type="date" value="<?php echo $employee['birthday'] ?>">
					</label>

					<br>
					<label>E-Mail
						<input name="email" type="email" value="<?php echo $employee['email'] ?>">
					</label>
					<label>Handy
						<input name="phone" type="fon" value="<?php echo $employee['phone'] ?>">
					</label>

				</div>
				<div class="employee-card">
					<label>Aktiv
						<input name="active" type="hidden" value="0">
						<input name="active" type="checkbox" value="1" <?php if($employee['active']==1)echo('checked'); ?>>
					</label>
					<label>
						<input type="radio" name="role"  value="0" <?php if($employee['role']==0)echo'checked' ?>>Mitarbeiter
						<input type="radio" name="role"  value="1" <?php if($employee['role']==1)echo'checked' ?>>Betreiber
						<input type="radio" name="role"  value="2" <?php if($employee['role']==2)echo'checked' ?>>Ausschuss
					</label>

					<br>
					<label>Im Team seit
						<input name="date_employed" type="date" value="<?php echo $employee['date_employed'] ?>">
					</label>
					<label>Schichten gesamt
					</label><?php echo($total_shifts) ?>

					<br>
					<label>Einarbeitung Theke
						<input name="training_0" type="hidden" value="0">
						<input name="training_0" type="checkbox" value="1" <?php if($employee['training_0']==1)echo('checked'); ?>>
					</label>
					<label>Einarbeitung Dachterrasse
						<input name="training_1" type="hidden" value="0">
						<input name="training_1" type="checkbox" value="1" <?php if($employee['training_1']==1)echo('checked'); ?>>
					</label>
					<label>Einarbeitung Küche
						<input name="training_2" type="hidden" value="0">
						<input name="training_2" type="checkbox" value="1" <?php if($employee['training_2']==1)echo('checked'); ?>>
					</label>
					<label>Hygienebelehrung
						<input name="health_certificate" type="hidden" value="0">
						<input name="health_certificate" type="checkbox" value="1" <?php if($employee['health_certificate']==1)echo('checked'); ?>>
					</label>
					<label>Einkäufer
						<input name="buyer" type="hidden" value="0">
						<input name="buyer" type="checkbox" value="1" <?php if($employee['buyer']==1)echo('checked'); ?>>
					</label>
					<label>Putzkraft
						<input name="cleaner" type="hidden" value="0">
						<input name="cleaner" type="checkbox" value="1" <?php if($employee['cleaner']==1)echo('checked'); ?>>
					</label>

					<br>
					<label>Kommentar
						<textarea rows="4" name="comment"><?php echo $employee['comment'] ?></textarea>
					</label>
				</div>
				<input type='hidden' name='id' value='<?php echo $employee['id'] ?>'></input>
				<input type='submit' value='Speichern'>
			</form>
		</div>
	</div>
</body>
</html>

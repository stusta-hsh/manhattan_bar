<?php
$page_title='schedules';

include('header.php');

// Datenbankabfrage KW des aktuellsten Wochenplans
$sql = 'SELECT calendar_week FROM schedules WHERE year=? ORDER BY year DESC, calendar_week DESC LIMIT 1';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_bind_param($sql_query, 'i', date('o'));
mysqli_stmt_execute($sql_query);
$max_calendar_week = mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query))['calendar_week'];
mysqli_stmt_close($sql_query);

// Datenbankabfrage Liste aller aktiven Mitarbeiter
$sql = 'SELECT employees.id, employees.first_name, employees.last_name, employees.display_name, employees.room_number, employees.training_0, employees.training_1, employees.training_2, houses.name AS "house.name" FROM employees LEFT JOIN houses ON employees.house = houses.id  WHERE employees.deleted=0 AND employees.active=1 ORDER BY employees.display_name ASC, employees.last_name ASC';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$employees = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

//Neuen Wochenplan speichern
if($_POST){
	$_POST['days_open']=$_POST['mo_open']+$_POST['di_open']+$_POST['mi_open']+$_POST['do_open']+$_POST['fr_open']+$_POST['sa_open']+$_POST['so_open'];
	$_POST['complete']=0; //TODO
	$sql = 'INSERT INTO schedules ('.implode(', ', array_slice(array_keys($_POST),0,sizeof($_POST))).') VALUES ('.str_repeat('?, ', sizeof($_POST)-1).'?)';
	$sql_query = mysqli_prepare($db, $sql);
	if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
	mysqli_stmt_bind_param($sql_query, 'ii'.str_repeat('isssiii', 7).'ii', ...array_values($_POST));
	mysqli_stmt_execute($sql_query);
	mysqli_stmt_close($sql_query);
	header('Location: schedule_edit.php?id='.$_POST['id']);
	exit();
}

?>
	<div class='toolbar-background'>
		<div class='toolbar'>
			<span><a href='schedule_list.php?y=<?php echo date('Y') ?>&m=<?php echo date('n') ?>'><i class='fa fa-list'></i><br>Alle Pläne</a></span>
			<span><a href='schedule_edit.php?id=<?php echo $current_schedule['id'] ?>'><i class='fa fa-calendar-o'></i><br>Aktueller Plan</a></span>
			<span><a href='schedule_new.php'><i class='fa fa-calendar-plus-o'></i><br>Neuer Plan</a></span>
			<span style='color: #ccc'><i style='color: #ccc' class='fa fa-print'></i><br>Drucken</span>
		</div>
	</div>
	<div class='content'>
		<div class="card">
			<form method='post' action='' class="edit_schedule_form">
				<div class="card-title">
					<div></div>
					<div>
						<input name='year' type='number' value='<?php echo date('o') ?>' class='input_year'></input>
						KW
						<input name='calendar_week' type='number' value='<?php echo $max_calendar_week+1 ?>' class='input_week'></input>
					</div>
					<div></div>
				</div>
		<div class="card-content">
			<div class='edit_schedule_form'>
					<table>
						<?php for($day=1; $day<8; $day++){ ?>
							<tr>
								<td>
									<?php echo ucfirst($weekdays[$day%7]) ?>
									<input name='<?php echo $weekdays[$day%7].'_open' ?>' type='hidden' value='0'><input name='<?php echo $weekdays[$day%7].'_open' ?>' type='checkbox' value='1' checked>
									<br><input name='<?php echo $weekdays[$day%7].'_opening_time' ?>' type='hidden' value='19:00'>
								</td>
								<td>
									<input type='text' name='<?php echo $weekdays[$day%7].'_event' ?>' placeholder='Event / Tagesessen'><br>
									<input type='text' name='<?php echo $weekdays[$day%7].'_deal' ?>' placeholder='Angebot'>
								</td>
								<td>
									<select name='<?php echo $weekdays[$day%7].'_theke' ?>'>
										<option value=''>Theke</option>
										<?php foreach($employees as $employee){
											if($employee['training_0'] || $employee['training_1'])
												echo ('<option value="'.$employee['id'].'">'.parse_employee_name($employee,1).'</option>');
										} ?>
									</select>
									<br>
									<select name='<?php echo $weekdays[$day%7].'_springer' ?>'>
										<option value=''>Springer</option>
										<?php foreach($employees as $employee){
											if($employee['training_0'] || $employee['training_1'])
												echo ('<option value="'.$employee['id'].'">'.parse_employee_name($employee,1).'</option>');
										} ?>
									</select>
									<br>
									<select name='<?php echo $weekdays[$day%7].'_kueche' ?>'>
										<option value=''>Küche</option>
										<?php foreach($employees as $employee){
											if($employee['training_2'])
												echo ('<option value="'.$employee['id'].'">'.parse_employee_name($employee,1).'</option>');
										} ?>
									</select>
								</td>
							</tr>
						<?php } ?>
					</table>
					<input type='hidden' name='days_open' value='0'></input>
					<input type='hidden' name='complete' value='0'></input>
					<div class="button-wrapper">
						<input type='submit' value='Speichern'>
					</div>
				</div>
			</form>
		</div>
	</div>
</body>
</html>

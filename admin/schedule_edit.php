<?php
$page_title='schedules';

include('header.php');

// Datenbankabfrage Wochenplan
$sql = 'SELECT * FROM schedules WHERE id = ? AND deleted = 0';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_bind_param($sql_query, 'i', $id);
mysqli_stmt_execute($sql_query);
$schedule = mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query));
mysqli_stmt_close($sql_query);

if (empty($schedule)) {
	header('Location: schedule_list.php');
	exit();
}

// Datenbankabfrage id des vorherigen Wochenplans
$sql = 'SELECT id FROM schedules WHERE year = ? AND calendar_week = ? AND deleted = 0';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
$previous_week = $schedule['calendar_week']-1;
mysqli_stmt_bind_param($sql_query, 'ii', $schedule['year'], $previous_week);
mysqli_stmt_execute($sql_query);
$schedule_previous = mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query));
mysqli_stmt_close($sql_query);

// Datenbankabfrage id des folgenden Wochenplans
$sql = 'SELECT id FROM schedules WHERE year = ? AND calendar_week = ? AND deleted = 0';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
$next_week = $schedule['calendar_week']+1;
mysqli_stmt_bind_param($sql_query, 'ii', $schedule['year'], $next_week);
mysqli_stmt_execute($sql_query);
$schedule_next = mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query));
mysqli_stmt_close($sql_query);

// Datenbankabfrage Liste aller aktiven Mitarbeiter
$sql = 'SELECT employees.id, employees.first_name, employees.last_name, employees.display_name, employees.room_number, employees.training_0, employees.training_1, employees.training_2, houses.name AS "house.name" FROM employees LEFT JOIN houses ON employees.house = houses.id  WHERE employees.deleted=0 AND employees.active=1 ORDER BY employees.display_name ASC, employees.last_name ASC';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$employees = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

$employee_names=[];
foreach($employees as $employee){
	if(!empty($employee['display_name']))
		$employee_names[$employee['id']]=$employee['display_name'].' ('.($employee['house.name']!='HSH' ? $employee['house.name'] : '').(($employee['house.name']!='HSH' && $employee['room_number']!=0) ? ', ' : '').($employee['room_number']!=0 ? $employee['room_number'] : '').')';
}

if($_POST){
	$_POST['days_open']=$_POST['mo_open']+$_POST['di_open']+$_POST['mi_open']+$_POST['do_open']+$_POST['fr_open']+$_POST['sa_open']+$_POST['so_open'];
	$_POST['complete']=0; //TODO
	$sql = 'UPDATE schedules SET '.implode(' = ?, ', array_slice(array_keys($_POST),0,sizeof($_POST)-1)).' = ? WHERE id = ?';
	$sql_query = mysqli_prepare($db, $sql);
	if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
	mysqli_stmt_bind_param($sql_query, str_repeat('isssiii', 7).'iii', ...array_values($_POST));
	mysqli_stmt_execute($sql_query);
	mysqli_stmt_close($sql_query);
	header('Location: schedule_edit.php?id='.$id);
	exit();
}

?>

<div class='toolbar-background'>
	<div class='toolbar'>
		<span><a href='schedule_list.php?y=<?php echo date('Y') ?>&m=<?php echo date('n') ?>'><i class='fa fa-list'></i><br>Alle Pläne</a></span>
		<span><a href='schedule_edit.php?id=<?php echo $current_schedule['id'] ?>'><i class='fa fa-calendar-o'></i><br>Aktueller Plan</a></span>
		<span><a href='schedule_new.php'><i class='fa fa-calendar-plus-o'></i><br>Neuer Plan</a></span>
		<span><a href='schedule_print_new.php?id=<?php echo $schedule['id'] ?>' target='_blank'><i class='fa fa-print'></i><br>Drucken</a></span>
		<!--<span style='color: #ccc'><i style='color: #ccc' class='fa fa-trash'></i><br>Löschen</span>-->
	</div>
	<!--<div class='toolbar'>
		<span><a <?php if(!empty($schedule_previous))echo('href="schedule_edit.php?id='.$schedule_previous['id'].'"') ?>><i class='fa fa-chevron-left' <?php if(empty($schedule_previous))echo('style="color:#ccc"') ?>></i><br></a></span>
		<h3>
			<?php echo $schedule['year'] ?>
			KW
			<?php echo $schedule['calendar_week'] ?>
		</h3>
		<span><a <?php if(!empty($schedule_next))echo('href="schedule_edit.php?id='.$schedule_next['id'].'"') ?>><i class='fa fa-chevron-right' <?php if(empty($schedule_next))echo('style="color:#ccc"') ?>></i><br></a></span>
	</div>-->
</div>
<div class='content'>
	<div class="card">
		<div class="card-title">
			<a class="card-title-arrow-left" <?php if(!empty($schedule_previous))echo('href="schedule_edit.php?id='.$schedule_previous['id'].'"'); ?>>
				<i class='fa fa-chevron-left'></i>
			</a>
			<?php echo $schedule['year'] ?>
			KW
			<?php echo $schedule['calendar_week'] ?>
			<a class="card-title-arrow-right" <?php if(!empty($schedule_next))echo('href="schedule_edit.php?id='.$schedule_next['id'].'"'); ?>>
				<i class='fa fa-chevron-right'></i>
			</a>
		</div>
		<div class="card-content">
			<form method='post' action=''>
				<?php for($day=1; $day<8; $day++){ ?>
					<div class="card-form-box">
						<div class="card-form-row">
							<?php echo ucfirst($weekdays[$day%7]) ?>
							<input name='<?php echo $weekdays[$day%7].'_open' ?>' type='hidden' value='0'><input name='<?php echo $weekdays[$day%7].'_open' ?>' type='checkbox' value='1' <?php if($schedule[$weekdays[$day%7].'_open']==1) echo 'checked' ?>>
							<br><input name='<?php echo $weekdays[$day%7].'_opening_time' ?>' type='hidden' value='<?php echo $schedule[$weekdays[$day%7].'_opening_time'] ?>'>
							<label class="flex-200">Event / Tagesessen
								<input type='text' name='<?php echo $weekdays[$day%7].'_event' ?>' value='<?php echo $schedule[$weekdays[$day%7].'_event'] ?>'>
							</label>
							<label class="flex-200">Angebot
								<input type='text' name='<?php echo $weekdays[$day%7].'_deal' ?>' value='<?php echo $schedule[$weekdays[$day%7].'_deal'] ?>'>
							</label>
						</div>
						<div class="card-form-row">
							<label class="flex-100">Theke
								<select name='<?php echo $weekdays[$day%7].'_theke' ?>'>
									<option value=''> - </option>
									<?php foreach($employees as $employee){
										if($employee['training_0'] || $employee['training_1']){ ?>
											<option value="<?php echo $employee['id'] ?>" <?php if($schedule[$weekdays[$day%7].'_theke']==$employee['id'])echo('selected'); ?>><?php echo parse_employee_name($employee, 1); ?></option>
										<?php }
									} ?>
								</select>
							</label>
							<label class="flex-100">Springer
								<select name='<?php echo $weekdays[$day%7].'_springer' ?>'>
									<option value=''> - </option>
									<?php	foreach($employees as $employee){
										if($employee['training_0'] || $employee['training_1']){ ?>
											<option value="<?php echo $employee['id'] ?>" <?php if($schedule[$weekdays[$day%7].'_springer']==$employee['id'])echo('selected'); ?>><?php echo parse_employee_name($employee, 1); ?></option>
										<?php }
									}?>
								</select>
							</label>
							<label class="flex-100">Küche
								<select name='<?php echo $weekdays[$day%7].'_kueche' ?>'>
									<option value=''> - </option>
									<?php foreach($employees as $employee){
										if($employee['training_2']){ ?>
											<option value="<?php echo $employee['id'] ?>" <?php if($schedule[$weekdays[$day%7].'_kueche']==$employee['id'])echo('selected'); ?>><?php echo parse_employee_name($employee, 1); ?></option>
										<?php }
									} ?>
								</select>
							</label>
						</div>
					</div>
				<?php } ?>
				<input type='hidden' name='days_open' value='0'></input>
				<input type='hidden' name='complete' value='0'></input>
				<input type='hidden' name='id' value='<?php echo $schedule['id'] ?>'></input>
				<input type='submit' value='Speichern'>
			</form>
		</div>
	</div>

	<div class="card team-schedule">
		<div class="card-title">
			Teamplan
			<?php
			$monday = (strtotime("first thursday of January ".$schedule['year']." +".$schedule['calendar_week']." week -1 week last Monday"));
			$sunday = $monday+(60*60*24*6);
			if(date('n', $monday) == date('n', $sunday)){
				echo date('j.', $monday).' - '.date('j. ', $sunday).$months[date('n', $monday)-1];
			}else{
				echo date('j. ', $monday).$months[date('n', $monday)-1].' - '.date('j. ', $sunday).$months[date('n', $sunday)-1];
			}
			?>
		</div>
		<div class="card-content">
			<table>
				<?php for($day=1; $day<8; $day++){ ?>
					<tr class="team-schedule-row">
						<td>
						<?php echo '<span style="font-size: 18px">'.ucfirst($weekdays[$day%7]).'</span>' ?><br>
						<?php echo '<span style="font-size: 12px">'.date('j.n.', $monday+(60*60*24*($day-1))).'</span>' ?>
						</td>
						<td>
							<span style="font-size: 14px">
								<?php if($schedule[$weekdays[$day%7].'_open']){
									echo ($schedule[$weekdays[$day%7].'_event'] ? $schedule[$weekdays[$day%7].'_event'].': ' : '');
									echo ($schedule[$weekdays[$day%7].'_deal'] ? $schedule[$weekdays[$day%7].'_deal'] : '');
								} else {
									echo ('geschlossen');
								} ?>
							</span>
						<table>
							<tr>
								<td style="text-align: left">
									<?php echo ($schedule[$weekdays[$day%7].'_theke'] ? '<a class="fa fa-glass"></a>'.$employee_names[$schedule[$weekdays[$day%7].'_theke']] : ''); ?>
									<?php echo ($schedule[$weekdays[$day%7].'_springer'] ? '<br><a class="fa fa-sun"></a>'.$employee_names[$schedule[$weekdays[$day%7].'_springer']] : ''); ?>
									<?php echo ($schedule[$weekdays[$day%7].'_kueche'] ? '<br><a class="fa fa-cutlery"></a>'.$employee_names[$schedule[$weekdays[$day%7].'_kueche']] : ''); ?>
								</td>
								<td style="text-align: right; vertical-align: bottom; font-size: 12px; color: #555;">
									Schlüssel zu<br>
									<input type="text" value=<?php echo '"'.($schedule[$weekdays[$day+1%7].'_kueche'] ? $employee_names[$schedule[$weekdays[$day+1%7].'_kueche']] : $employee_names[$schedule[$weekdays[$day+1%7].'_theke']]).'"' ?> placeholder="Jonas (1622)">
								</td>
							</tr>
						</table>
						</td>
					</tr>

					</div>
				<?php } ?>
			</table>
		</div>
</div>
</body>
</html>

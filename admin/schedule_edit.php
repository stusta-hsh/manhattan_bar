<?php
$page_title='schedules';

include('header.php');

// Datenbankabfrage Wochenplan
$sql = 'SELECT * FROM schedules WHERE id = ? AND deleted = 0';
$sql_query = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($sql_query, 'i', $id);
if (!$sql_query) die('ERROR: could not prepare sql: $sql');
mysqli_stmt_execute($sql_query);
$schedule = mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query));
mysqli_stmt_close($sql_query);

if(empty($schedule)){
	header('Location: schedule_list.php');
	exit();
}

// Datenbankabfrage id des vorherigen Wochenplans
$sql = 'SELECT id FROM schedules WHERE year = ? AND calendar_week = ? AND deleted = 0';
$sql_query = mysqli_prepare($db, $sql);
$previous_week = $schedule['calendar_week']-1;
mysqli_stmt_bind_param($sql_query, 'ii', $schedule['year'], $previous_week);
if (!$sql_query) die('ERROR: could not prepare sql: $sql');
mysqli_stmt_execute($sql_query);
$schedule_previous = mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query));
mysqli_stmt_close($sql_query);

// Datenbankabfrage id des folgenden Wochenplans
$sql = 'SELECT id FROM schedules WHERE year = ? AND calendar_week = ? AND deleted = 0';
$sql_query = mysqli_prepare($db, $sql);
$next_week = $schedule['calendar_week']+1;
mysqli_stmt_bind_param($sql_query, 'ii', $schedule['year'], $next_week);
if (!$sql_query) die('ERROR: could not prepare sql: $sql');
mysqli_stmt_execute($sql_query);
$schedule_next = mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query));
mysqli_stmt_close($sql_query);

// Datenbankabfrage Liste aller aktiven Mitarbeiter
$sql = 'SELECT employees.id, employees.first_name, employees.last_name, employees.display_name, employees.room_number, employees.training_0, employees.training_1, employees.training_2, houses.name AS "house.name" FROM employees LEFT JOIN houses ON employees.house = houses.id  WHERE employees.deleted=0 AND employees.active=1 ORDER BY employees.display_name ASC, employees.last_name ASC';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: could not prepare sql: $sql');
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
		<span><a href='schedule_edit.php?id=<?php echo $schedule['id'] ?>'><i class='fa fa-calendar-o'></i><br>Aktueller Plan</a></span>
		<span><a href='schedule_new.php'><i class='fa fa-calendar-plus-o'></i><br>Neuer Plan</a></span>
		<span><a href='schedule_print.php?id=<?php echo $schedule['id'] ?>' target='_blank'><i class='fa fa-print'></i><br>Drucken</a></span>
		<!--<span style='color: #ccc'><i style='color: #ccc' class='fa fa-trash'></i><br>Löschen</span>-->
	</div>
	<div class='toolbar'>
		<span><a <?php if(!empty($schedule_previous))echo('href="schedule_edit.php?id='.$schedule_previous['id'].'"') ?>><i class='fa fa-chevron-left' <?php if(empty($schedule_previous))echo('style="color:#ccc"') ?>></i><br></a></span>
		<h3>
			<?php echo $schedule['year'] ?>
			KW
			<?php echo $schedule['calendar_week'] ?>
		</h3>
		<span><a <?php if(!empty($schedule_next))echo('href="schedule_edit.php?id='.$schedule_next['id'].'"') ?>><i class='fa fa-chevron-right' <?php if(empty($schedule_next))echo('style="color:#ccc"') ?>></i><br></a></span>
	</div>
</div>
<div class='content'>
	<div class='edit_schedule_form'>
		<form method='post' action=''>
			<table>
				<?php for($day=1; $day<8; $day++){ ?>
					<tr>
						<td>
							<?php echo ucfirst($weekdays[$day%7]) ?>
							<input name='<?php echo $weekdays[$day%7].'_open' ?>' type='hidden' value='0'><input name='<?php echo $weekdays[$day%7].'_open' ?>' type='checkbox' value='1' <?php if($schedule[$weekdays[$day%7].'_open']==1) echo 'checked' ?>>
							<br><input name='<?php echo $weekdays[$day%7].'_opening_time' ?>' type='hidden' value='<?php echo $schedule[$weekdays[$day%7].'_opening_time'] ?>'>
						</td>
						<td>
							<input type='text' name='<?php echo $weekdays[$day%7].'_event' ?>' value='<?php echo $schedule[$weekdays[$day%7].'_event'] ?>' placeholder='Event / Tagesessen'><br>
							<input type='text' name='<?php echo $weekdays[$day%7].'_deal' ?>' value='<?php echo $schedule[$weekdays[$day%7].'_deal'] ?>' placeholder='Angebot'>
						</td>
						<td>
							<select name='<?php echo $weekdays[$day%7].'_theke' ?>'>
								<option value=''>Theke</option>
								<?php foreach($employees as $employee){
									if($employee['training_0'] || $employee['training_1']){ ?>
										<option value="<?php echo $employee['id'] ?>" <?php if($schedule[$weekdays[$day%7].'_theke']==$employee['id'])echo('selected'); ?>><?php echo parse_employee_name($employee, 1); ?></option>
									<?php }
								} ?>
							</select>
							<br>
							<select name='<?php echo $weekdays[$day%7].'_springer' ?>'>
								<option value=''>Springer</option>
								<?php	foreach($employees as $employee){
									if($employee['training_0'] || $employee['training_1']){ ?>
										<option value="<?php echo $employee['id'] ?>" <?php if($schedule[$weekdays[$day%7].'_springer']==$employee['id'])echo('selected'); ?>><?php echo parse_employee_name($employee, 1); ?></option>
									<?php }
								}?>
							</select>
							<br>
							<select name='<?php echo $weekdays[$day%7].'_kueche' ?>'>
								<option value=''>Küche</option>
								<?php foreach($employees as $employee){
									if($employee['training_2']){ ?>
										<option value="<?php echo $employee['id'] ?>" <?php if($schedule[$weekdays[$day%7].'_kueche']==$employee['id'])echo('selected'); ?>><?php echo parse_employee_name($employee, 1); ?></option>
									<?php }
								} ?>
							</select>
						</td>
					</tr>
				<?php } ?>
			</table>
			<input type='hidden' name='days_open' value='0'></input>
			<input type='hidden' name='complete' value='0'></input>
			<input type='hidden' name='id' value='<?php echo $schedule['id'] ?>'></input>
			<input type='submit' value='Speichern'>
		</form>
	</div>
	<div class="team-schedule">
	<h4>Teamplan <?php

	if(date('n', strtotime($schedule['year'].'-W'.$schedule['calendar_week'].'-1')) == date('n', strtotime($schedule['year'].'-W'.$schedule['calendar_week'].'-7'))){
		echo date('j.', strtotime($schedule['year'].'-W'.$schedule['calendar_week'].'-1')).' - '.date('j. ', strtotime($schedule['year'].'-W'.$schedule['calendar_week'].'-7')).$months[date('n', strtotime($schedule['year'].'-W'.$schedule['calendar_week'].'-7'))-1];
	}else{
		echo date('j. ', strtotime($schedule['year'].'-W'.$schedule['calendar_week'].'-1')).$months[date('n', strtotime($schedule['year'].'-W'.$schedule['calendar_week'].'-1'))-1].' - '.date('j. ', strtotime($schedule['year'].'-W'.$schedule['calendar_week'].'-7')).$months[date('n', strtotime($schedule['year'].'-W'.$schedule['calendar_week'].'-7'))-1];
	}

	?></h4>
		<table>
			<!--<tr>
				<th></th>
				<th></th>
				<th>Theke</th>
				<th>Springer</th>
				<th>Küche</th>
				<th>Schlüssel zu</th>
			</tr>-->
			<?php for($day=1; $day<8; $day++){ ?>
				<tr>
					<td><?php echo ucfirst($weekdays[$day%7]) ?><br><?php echo '<span style="font-size:12px">'.date('j.n.', strtotime($schedule['year'].'-W'.$schedule['calendar_week'].'-'.$day)).'</span>' ?></td>
					<!--<td><?php echo date('j.n.', strtotime($schedule['year'].'-W'.$schedule['calendar_week'].'-'.$day)) ?></td>-->
					<td style="font-size:12px">
						<?php if($schedule[$weekdays[$day%7].'_open']){
							echo ($schedule[$weekdays[$day%7].'_event'] ? $schedule[$weekdays[$day%7].'_event'].'<br>' : '');
							echo ($schedule[$weekdays[$day%7].'_deal'] ? $schedule[$weekdays[$day%7].'_deal'] : '').'<br>';
						} else { ?>
							geschlossen
						<?php } ?>
					</td>
					<td style="color:lightgrey;">
						<?php //if($schedule[$weekdays[$day%7].'_open']){
							//echo '<span style="font-size:12px">Theke</span><br>';
							echo '<a class="fa fa-glass" style="font-size:12px"></a><br>';
							//echo '<span style="font-size:12px">Springer</span><br>';
							echo '<a class="fa fa-life-ring" style="font-size:12px"></a><br>';
							//echo '<span style="font-size:12px">'.($schedule[$weekdays[$day%7].'_kueche'] ? 'Küche' : '').'</span>';
							echo ($schedule[$weekdays[$day%7].'_kueche'] ? '<a class="fa fa-cutlery" style="font-size:12px"></a>' : '');
						//} ?>
					<?php echo ($schedule[$weekdays[$day%7].'_open'] ? '<td>' : '<td style="color:grey;">'); ?>
						<?php //if($schedule[$weekdays[$day%7].'_open']){
							echo ($schedule[$weekdays[$day%7].'_theke'] ? $employee_names[$schedule[$weekdays[$day%7].'_theke']] : '-').'<br>';
							echo ($schedule[$weekdays[$day%7].'_springer'] ? $employee_names[$schedule[$weekdays[$day%7].'_springer']] : '-').'<br>';
							echo ($schedule[$weekdays[$day%7].'_kueche'] ? $employee_names[$schedule[$weekdays[$day%7].'_kueche']] : '');
						/*} else {
							echo '<span style="color: grey">geschlossen</span>';
						}*/ ?>
					</td>
					<td>
						<span style="font-size:12px">Schlüssel zu<br></span>
						<input type="text" value=<?php echo '"'.($schedule[$weekdays[$day+1%7].'_kueche'] ? $employee_names[$schedule[$weekdays[$day+1%7].'_kueche']] : $employee_names[$schedule[$weekdays[$day+1%7].'_theke']]).'"' ?>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
</div>
</body>
</html>

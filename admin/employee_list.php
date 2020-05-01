<?php

$page_title='team';

include('header.php');

// Datenbankabfrage Liste aller Mitarbeiter
$sql = 'SELECT employees.id, employees.first_name, employees.last_name, employees.display_name, employees.room_number, employees.active, employees.training_0, employees.training_1, employees.training_2, employees.health_certificate, employees.buyer, employees.cleaner, houses.shortname AS "house.name" FROM employees LEFT JOIN houses ON employees.house = houses.id WHERE employees.deleted = 0 ORDER BY employees.active DESC, employees.display_name ASC, employees.last_name ASC';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$employees = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);


include('employee_header.php');

?>
<div class="content">
	<div class="card">
		<div class="card-title">
			Alle Mitarbeiter (<?php echo(mysqli_num_rows($employees)); ?>)
			<div class="employee-searchbar">
				<i class="fa fa-search" aria-hidden="true"></i>
				<input type="text" id="employee_searchbar" onkeyup="searchlist('employee_searchbar', 'sortable_list')" placeholder="" autofocus>
			</div>
		</div>
		<div class="card-content">
			<table class="employee_list" id="sortable_list">
				<tr>
					<th onclick="sortTable(0)"><i title="Zimmer" class="fa fa-map-marker"></i></th>
					<th onclick="sortTable(1)"><i title="Name" class="fa fa-user"></i></th>
					<th onclick="sortTable(2)"><i title="Einarbeitung Theke" class="fa fa-glass"></i></th>
					<th onclick="sortTable(3)"><i title="Einarbeitung Dachterrasse" class="fa fa-sun"></i></th>
					<th onclick="sortTable(4)"><i title="Einarbeitung Küche" class="fa fa-cutlery"></i></th>
					<th onclick="sortTable(5)"><i title="Hygienebelehrung" class="fa fa-id-card"></i></th>
					<th onclick="sortTable(6)"><i title="Einkäufer" class="fa fa-truck"></i></th>
					<th onclick="sortTable(7)"><i title="Putzkraft" class="fa fa-tint"></i></th>
				</tr>
				<?php foreach($employees as $employee){ ?>
					<tr <?php echo (!$employee['active'] ? 'style="background-color: #ddd"' : ''); ?> onclick="location.href='employee_edit.php?id=<?php echo $employee['id'] ?>'">
						<td style="width: 50px; text-align: right">
							<?php if(!empty($employee['room_number']))echo($employee['room_number']); ?>
							<span style="font-size: 8pt" href="employee_edit.php?id=<?php echo $employee['id'] ?>"><br><?php echo($employee['house.name']); ?></span>
						</td>
						<td style="text-align: left">
							<?php echo($employee['display_name']); ?>
							<span style="font-size: 8pt; color: #666" href="employee_edit.php?id=<?php echo $employee['id'] ?>"><br><?php echo($employee['first_name'].' '.$employee['last_name']); ?></span>
						</td>
						<td title="Einarbeitung Theke">
							<?php if($employee['training_0'])echo('<i class="fa fa-glass"></i>'); ?>
						</td>
						<td title="Einarbeitung Dachterrasse">
							<?php if($employee['training_1'])echo('<i class="fa fa-sun"></i>'); ?>
						</td>
						<td title="Einarbeitung Küche">
							<?php if($employee['training_2'])echo('<i class="fa fa-cutlery"></i>'); ?>
						</td>
						<td title="Hygienebelehrung">
							<?php if($employee['health_certificate'])echo('<i class="fa fa-id-card"></i>'); ?>
						</td>
						<td title="Einkäufer">
							<?php if($employee['buyer'])echo('<i class="fa fa-truck"></i>'); ?>
						</td>
						<td title="Putzkraft">
							<?php if($employee['cleaner'])echo('<i class="fa fa-tint"></i>'); ?>
						</td>
					</tr>
				<?php } ?>
			</table>
		</div>
	</div>



</div>
</body>
</html>

<?php

$page_title='team';

include('header.php');

// Datenbankabfrage Liste aller Mitarbeiter
$sql = 'SELECT employees.id, employees.first_name, employees.last_name, employees.display_name, employees.room_number, employees.active, employees.training_0, employees.training_1, employees.training_2, employees.health_certificate, employees.buyer, employees.cleaner, houses.name AS "house.name" FROM employees LEFT JOIN houses ON employees.house = houses.id WHERE employees.deleted = 0 ORDER BY employees.active DESC, employees.display_name ASC, employees.last_name ASC';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: could not prepare sql: $sql');
mysqli_stmt_execute($sql_query);
$employees = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);


include('employee_header.php');

?>
	<div class="content">
		<h3>Alle Mitarbeiter (<?php echo(mysqli_num_rows($employees)); ?>)</h3>
		<table class="employee_list">
			<tr>
				<th style="width: 50px;"></th>
				<th></th>
				<th>aktiv</th>
				<th style="width: 15px;"></th>
				<th style="width: 15px;"></th>
				<th style="width: 15px;"></th>
				<th style="width: 15px;"></th>
				<th style="width: 15px;"></th>
				<th style="width: 15px;"></th>
			</tr>
			<?php foreach($employees as $employee){ ?>
				<tr>
					<td style="text-align: right">
						<a href="employee_edit.php?id=<?php echo $employee['id'] ?>">
							<?php if(!empty($employee['room_number']))echo($employee['room_number']); ?>
							<span style="font-size: 8pt" href="employee_edit.php?id=<?php echo $employee['id'] ?>"><br><?php echo($employee['house.name']); ?></span>
						</a>
					</td>
					<td style="text-align: left">
						<a href="employee_edit.php?id=<?php echo $employee['id'] ?>">
							<?php echo($employee['display_name']); ?>
							<span style="font-size: 8pt; color: #666" href="employee_edit.php?id=<?php echo $employee['id'] ?>"><br><?php echo($employee['first_name'].' '.$employee['last_name']); ?></span>
						</a>
					</td>
					<td title="aktiv">
						<a href="employee_edit.php?id=<?php echo $employee['id'] ?>">
							<?php if($employee['active'])echo('<i class="fa fa-check"></i>'); ?>
						</a>
					</td>
					<td title="Einarbeitung Theke">
						<a href="employee_edit.php?id=<?php echo $employee['id'] ?>">
							<?php if($employee['training_0'])echo('<i class="fa fa-glass"></i>'); ?>
						</a>
					</td>
					<td title="Einarbeitung Dachterrasse">
						<a href="employee_edit.php?id=<?php echo $employee['id'] ?>">
							<?php if($employee['training_1'])echo('<i class="fa fa-sun-o"></i>'); ?>
						</a>
					</td>
					<td title="Einarbeitung Küche">
						<a href="employee_edit.php?id=<?php echo $employee['id'] ?>">
							<?php if($employee['training_2'])echo('<i class="fa fa-cutlery"></i>'); ?>
						</a>
					</td>
					<td title="Hygienebelehrung">
						<a href="employee_edit.php?id=<?php echo $employee['id'] ?>">
							<?php if($employee['health_certificate'])echo('<i class="fa fa-id-card"></i>'); ?>
						</a>
					</td>
					<td title="Einkäufer">
						<a href="employee_edit.php?id=<?php echo $employee['id'] ?>">
							<?php if($employee['buyer'])echo('<i class="fa fa-truck"></i>'); ?>
						</a>
					</td>
					<td title="Putzkraft">
						<a href="employee_edit.php?id=<?php echo $employee['id'] ?>">
							<?php if($employee['cleaner'])echo('<i class="fa fa-tint"></i>'); ?>
						</a>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
</body>
</html>

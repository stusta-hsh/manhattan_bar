<?php
$page_title='schedules';

$year=$_GET['y'];
$month=$_GET['m'];

include('header.php');

// Datenbankabfrage Liste der letzten 25 Wochenpläne
$sql = 'SELECT id, year, calendar_week, days_open, mo_open, di_open, mi_open, do_open, fr_open, sa_open, so_open, mo_event, di_event, mi_event, do_event, fr_event, sa_event, so_event FROM schedules WHERE deleted=0 AND year IN ('.($year-1).', '.$year.', '.($year+1).') ORDER BY year DESC, calendar_week ASC LIMIT 25';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: could not prepare sql: $sql');
mysqli_stmt_execute($sql_query);
$schedules = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

?>
	<div class='toolbar-background'>
		<div class='toolbar'>
			<span><a href='schedule_list.php?y=<?php echo date('Y') ?>&m=<?php echo date('n') ?>'><i class='fa fa-list'></i><br>Alle Pläne</a></span>
			<span><a href='schedule_edit.php?id=<?php echo $schedule['id'] ?>'><i class='fa fa-calendar-o'></i><br>Aktueller Plan</a></span>
			<span><a href='schedule_new.php'><i class='fa fa-calendar-plus-o'></i><br>Neuer Plan</a></span>
			<span style='color: #ccc'><i style='color: #ccc' class='fa fa-print'></i><br>Drucken</span>
			<span style='color: #ccc'><i style='color: #ccc' class='fa fa-trash'></i><br>Löschen</span>
		</div>
	</div>

	<div class="content">
		<h3>
			<a href="schedule_list.php?y=<?php echo($month==1?$year-1:$year) ?>&m=<?php echo($month==1?'12':$month-1) ?>"><i class="fa fa-chevron-left"></i></a>
			<?php echo(' '.$months[$month-1].' '.$year.' '); ?>
			<a href="schedule_list.php?y=<?php echo($month==12?$year+1:$year) ?>&m=<?php echo($month==12?'1':$month+1) ?>"><i class="fa fa-chevron-right"></i></a>
		</h3>
		<table>
			<tr>
				<th></th>
				<th>Mo</th>
				<th>Di</th>
				<th>Mi</th>
				<th>Do</th>
				<th>Fr</th>
				<th>Sa</th>
				<th>So</th>
			</tr>
			<?php foreach($schedules as $schedule){
				if($schedule['year']==$year && (date('n',$schedule['year']+($schedule['calendar_week']-1)*7*24*60*60-(24*60*60))==$month || date('n',$schedule['year']+($schedule['calendar_week']-1)*7*24*60*60+(5*24*60*60))==$month)){
				?>
					<tr>
						<td>
							<a href="schedule_edit.php?id=<?php echo $schedule['id'] ?>">
								<span style="font-size: 8pt">KW</span><br>
								<span><?php echo $schedule['calendar_week'] ?></span>
							</a>
						</td>
						<?php for($i=1; $i<8; $i++){ ?>
							<td>
								<a href="schedule_edit.php?id=<?php echo $schedule['id'] ?>" <?php if(!$schedule[$weekdays[$i%7].'_open']) echo('style="color:#bbb"'); ?>><?php echo date('j', $schedule['year']+($schedule['calendar_week']-1)*7*24*60*60+(($i-2)*24*60*60)) ?></a>
							</td>
						<?php } ?>
					</tr>
				<?php }
			} ?>
		</table>
	</div>
</body>
</html>

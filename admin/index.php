<?php
$page_title='admin';

include('header.php');

// Datenbankabfrage Mitarbeiter
$sql = 'SELECT id, first_name, display_name FROM employees WHERE deleted = 0';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: could not prepare sql: $sql');
mysqli_stmt_execute($sql_query);
$employees = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

$employee_names = [];
foreach($employees as $employee){
	if(!empty($employee['display_name']))
		$employee_names[$employee['id']] = $employee['display_name'];
	else
		$employee_names[$employee['id']] = $employee['first_name'];
}
?>

	<div class="content">
		<h3>Hallo <?php echo(ucfirst($_SERVER['PHP_AUTH_USER'])); ?>!</h3>
		<br><br>
		Status:
		<h3 style="color: <?php echo($fcolor); ?>"><?php echo($desc); ?></h3>
		Letzte Aktualisierung:
		<?php echo $lastrefreshed ?>

		<br><br><br>

		<table class="wochenplan">
			<?php for($i=1; $i<8; $i++){ ?>
				<tr id="<?php echo($weekdays[$i%7]) ?>">
					<td style="width: 40px">
						<?php echo ucfirst($weekdays[$i%7]) ?><br>
						<a style="font-size: 8pt">
							<?php echo date('j.n.', time()-($days_from_monday[date('w')]*24*60*60)+($i-1)*24*60*60); ?>
						</a>
					</td>
					<td id="<?php echo($weekdays[$i%7].'_daily') ?>">
						<?php
						if(!$current_schedule[$weekdays[$i%7].'_open']){
							echo('<span style="color: grey">geschlossen</span>');
						}else{
							if(empty($current_schedule[$weekdays[$i%7].'_deal']) && empty($current_schedule[$weekdays[$i%7].'_event']))
								echo('geöffnet');
							if(!empty($current_schedule[$weekdays[$i%7].'_event']))
								echo('<span id="'.$weekdays[$i%7].'_event">'.$current_schedule[$weekdays[$i%7].'_event'].'</span>');
							if(!empty($current_schedule[$weekdays[$i%7].'_event']) && !empty($current_schedule[$weekdays[$i%7].'_deal']))
								echo('<br>');
							if(!empty($current_schedule[$weekdays[$i%7].'_deal']))
								echo('<span id="'.$weekdays[$i%7].'_deal">'.$current_schedule[$weekdays[$i%7].'_deal'].'</span>');
						}?><br>
					</td>
					<td id="<?php echo($weekdays[$i%7].'_team') ?>">
						<?php
						if(!empty($current_schedule[$weekdays[$i%7].'_kueche'])) echo($employee_names[$current_schedule[$weekdays[$i%7].'_kueche']]);
						if(!empty($current_schedule[$weekdays[$i%7].'_kueche']) && !empty($current_schedule[$weekdays[$i%7].'_theke']) && !empty($current_schedule[$weekdays[$i%7].'_springer'])) echo(', <br>');
						if(!empty($current_schedule[$weekdays[$i%7].'_kueche']) && !empty($current_schedule[$weekdays[$i%7].'_theke']) && empty($current_schedule[$weekdays[$i%7].'_springer'])) echo(' & ');
						if(!empty($current_schedule[$weekdays[$i%7].'_theke'])) echo($employee_names[$current_schedule[$weekdays[$i%7].'_theke']]);
						if(!empty($current_schedule[$weekdays[$i%7].'_springer'])) echo(' & '.$employee_names[$current_schedule[$weekdays[$i%7].'_springer']]);
						?>
					</td>
				</tr>
			<?php }
			?>
		</table>

	</div>
</body>
</html>

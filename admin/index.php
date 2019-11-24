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

// Neuen Status übernehmen
if($_POST){
	$sql = 'INSERT INTO openstatus (STATUS) VALUES (?)';
	$sql_query = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($sql_query, 'i', $_POST['new_status']);
	mysqli_stmt_execute($sql_query);
	mysqli_stmt_close($sql_query);
	header('Location: index.php');
	exit();
}
?>

	<div class="content">
		<h3>Hallo <?php echo(ucfirst($_SERVER['PHP_AUTH_USER'])); ?>!</h3>

		<div class="card">
			<div class="card-title">Status</div>
			<div class="card-content">
				<br>
				<?php echo($desc); ?>
				<br><br>
				<form method='post' action=''>
					<div class="radio-input-wrapper">
						<input id="status_closed" type='radio' name='new_status' value='0' <?php if($status==0||status==3)echo'checked' ?>></input>
						<label for="status_closed"><i class="fa fa-refresh" aria-hidden="true"></i><br>automatisch</label>
						<input id="status_open" type='radio' name='new_status' value='1' <?php if($status==1)echo'checked' ?>></input>
						<label for="status_open"><i class="fa fa-umbrella" aria-hidden="true"></i><br>Manhattan offen</label>
						<input id="status_rooftop" type='radio' name='new_status' value='2' <?php if($status==2)echo'checked' ?>></input>
						<label for="status_rooftop"><i class="fa fa-sun" aria-hidden="true"></i><br>Dachterrasse offen</label>
					</div>
					<div class='button-wrapper'>
						<input type='submit' value='Anwenden'></input>
					</div>
					<!-- Letzte Aktualisierung: <?php echo $lastrefreshed ?>-->
				</form>
			</div>
		</div>

		<div class="card">
			<div class="card-title">Wochenplan</div>
			<div class="card-content">
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
		</div>
		<!--
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
	-->
	</div>
</body>
</html>

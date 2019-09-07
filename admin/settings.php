<?php
$page_title='settings';

include('header.php');

// Neuen Status übernehmen
if($_POST){
	$sql = 'INSERT INTO openstatus (STATUS) VALUES (?)';
	$sql_query = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($sql_query, 'i', $_POST['new_status']);
	mysqli_stmt_execute($sql_query);
	mysqli_stmt_close($sql_query);
	header('Location: settings.php');
	exit();
}

?>

	<div class="content">
		<div class="card">
			<div class="card-title">Status</div>
			<div class="card-content">
				<form method='post' action=''>
					<div class="radio-input-wrapper">
						<input id="status_closed" type='radio' name='new_status' value='0' <?php if($status==0)echo'checked' ?>></input>
						<label for="status_closed"><i class="fa fa-refresh" aria-hidden="true"></i><br>automatisch</label>
						<input id="status_open" type='radio' name='new_status' value='1' <?php if($status==1)echo'checked' ?>></input>
						<label for="status_open"><i class="fa fa-umbrella" aria-hidden="true"></i><br>Manhattan offen</label>
						<input id="status_rooftop" type='radio' name='new_status' value='2' <?php if($status==2)echo'checked' ?>></input>
						<label for="status_rooftop"><i class="fa fa-sun" aria-hidden="true"></i><br>Dachterrasse offen</label>
					</div>
					<br>
					<input type='submit' value='Speichern'></input>
					<!-- Letzte Aktualisierung: <?php echo $lastrefreshed ?>-->
				</form>
			</div>
		</div>
	</div>
</body>
</html>

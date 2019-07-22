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
		<h3>Status</h3>
		Letzte Aktualisierung:
		<?php echo $lastrefreshed ?>
		<br>

		<form method='post' action=''>
			<input type='radio' name='new_status' value='0' <?php if($status==0)echo'checked' ?>>geschlossen</input>
			<input type='radio' name='new_status' value='1' <?php if($status==1)echo'checked' ?>>Manhattan offen</input>
			<input type='radio' name='new_status' value='2' <?php if($status==2)echo'checked' ?>>Dachterrasse offen</input>
			<br>
			<input type='submit' value='Speichern'></input>
		</form>

	</div>
</body>
</html>

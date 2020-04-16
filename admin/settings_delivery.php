<?php

include('header.php');

// Datenbankabfrage Settings
$sql = 'SELECT * FROM settings';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$results = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

$settings = [];
foreach($results as $result){
	$settings[$result['title']] = $result['value'];
}

// Änderungen in die Datenbank schreiben
if($_POST){
	foreach($_POST as $title => $value){
		$sql = 'UPDATE settings SET value = ? WHERE title = ?';
		$sql_query = mysqli_prepare($db, $sql);
		if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
		mysqli_stmt_bind_param($sql_query, 'ss', $value, $title);
		mysqli_stmt_execute($sql_query);
		mysqli_stmt_close($sql_query);
	}
	header('Location: settings.php');
	exit();
}

include('settings_header.php');

?>
	<div class="content">
		<div class="card">
			<div class="card-title">Lieferservice</div>
			<div class="card-content">
				
			</div>
		</div>
	</div>

</body>
</html>

<?php

// Datenbankabfrage id des aktuellen Wochenplans
$sql = 'SELECT id FROM schedules WHERE year=? AND calendar_week=?';
$sql_query = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($sql_query, 'ii', date('o'), date('W'));
if (!$sql_query) die('ERROR: could not prepare sql: $sql');
mysqli_stmt_execute($sql_query);
$schedule = mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query));
mysqli_stmt_close($sql_query);

?>

<div class='logo'>
	<a href='index.php'><img src='../images/logo.png' alt='Manhattan' width='100%'></a>
</div>

<div class='tab-navigation-background'>
	<div class='tab-navigation'>
		<a class='tab <?php if($page_title=='admin') echo 'active-tab'; ?>' href='index.php' title='Startseite'><i class='fa fa-home'></i></a>
		<a class='tab <?php if($page_title=='schedules') echo 'active-tab'; ?>' href='schedule_list.php' title='Wochenpläne'><i class='fa fa-calendar'></i></a>
		<a class='tab <?php if($page_title=='team') echo 'active-tab'; ?>' href='employee_list.php' title='Mitarbeiter'><i class='fa fa-users'></i></a>
		<a class='tab <?php if($page_title=='events') echo 'active-tab'; ?>' title='Events'><i style='color: #888' class='fa fa-bullhorn'></i></a>
		<a class='tab <?php if($page_title=='finances') echo 'active-tab'; ?>' title='Finanzen'><i style='color: #888' class='fa fa-euro'></i></a>
		<a class='tab <?php if($page_title=='stats') echo 'active-tab'; ?>' title='Statistiken'><i style='color: #888' class='fa fa-bar-chart'></i></a>
		<a class='tab <?php if($page_title=='settings') echo 'active-tab'; ?>' href='settings.php' title='Einstellungen'><i class='fa fa-cog'></i></a>
	</div>
</div>

<!DOCTYPE html>

<?php
	$weekdays = ['so', 'mo', 'di', 'mi', 'do', 'fr', 'sa'];
	$months = ['Jan.', 'Feb.', 'März', 'Apr.', 'Mai', 'Juni', 'Juli', 'Aug.', 'Sept.', 'Okt.', 'Nov.', 'Dez.'];
	$days_from_monday = [6, 0, 1, 2, 3, 4, 5];
	if(isset($_GET['id'])) $id = $_GET['id'];

	// Datenbankabfrage aus der alten Seite um den Stand des Schalters zu prüfen.

	$sql_config = parse_ini_file('../sql_config.ini');
	$db = mysqli_connect($sql_config['host'], $sql_config['username'], $sql_config['password'], $sql_config['dbname']);
	if(!$db) exit("Database connection error: ".mysqli_connect_error());

	// Datenbankabfrage aktueller Wochenplan
	$sql = 'SELECT * FROM schedules WHERE calendar_week = ?';
	$sql_query = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($sql_query, 'i', date('W'));
	if (!$sql_query) die('ERROR: could not prepare sql: '.$sql);
	mysqli_stmt_execute($sql_query);
	$current_schedule = mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query));
	mysqli_stmt_close($sql_query);

	$sql = 'SELECT date, status FROM openstatus ORDER BY date DESC LIMIT 1';
	$sql_query = mysqli_prepare($db, $sql);
	if (!$sql_query) die('ERROR: could not prepare sql: '.$sql);
	mysqli_stmt_execute($sql_query);

	$result = mysqli_stmt_get_result($sql_query);

	if (mysqli_num_rows($result) == 0)
		die("db result empty!");
	$row = mysqli_fetch_assoc($result);
	mysqli_stmt_close($sql_query);
	$status = $row['status'];
	$lastrefreshed = $row['date'];
	echo "<!--Last status: ". $status. "  updated: ". $lastrefreshed. " -->";

	$lrd = strtotime($lastrefreshed);
	$diff = time() - $lrd;

	if ($status != 0 && $diff > 43200){
		 echo "\n<!--WARNING: Assuming CLOSED because the last status update is older than twelve hours! -->";
		 $status = 0;
	}

	if ($status == 1){
		 $fcolor = "#000";
		 $titlestatus = "Geöffnet";
		 $desc = "Wir haben geöffnet!<br>Die Dachterrasse bleibt heute geschlossen.";
		 if(empty($current_schedule))
			 $desc .= "<br>Der aktuelle Plan kommt bald.<br><br><br><br>";
	}
	else if ($status == 2){
		 $fcolor = "#000";
		 $titlestatus = "Dachterrasse geöffnet";
		 $desc = "Die Dachterrasse ist geöffnet!";
		 if(empty($current_schedule))
			 $desc .= "<br>Der aktuelle Plan kommt bald.<br><br><br><br>";
	}
	else{
		 $fcolor = "gray";
		 $titlestatus = "Geschlossen";
		 $desc = "Aktuell geschlossen.";
		 if(empty($current_schedule))
			 $desc .= "<br>Der Wochenplan kommt bald.<br><br><br><br>";
		 elseif($current_schedule[$weekdays[date('w')].'_open'])
			 $desc .= "<br>Wir öffnen heute um 19 Uhr.";
		 else
			 $desc = "Heute bleiben wir geschlossen.";
	}

	// Ende der Datenbankabfrage

	function calc_time(){
		global $status;
		// Vor 10 Uhr vormittags: Manhattan noch offen? -> Special von gestern noch aktuell, sonst heutiges
		if($status != 0 && intval(date('G'))<10)
			return time()-(24*60*60);
		else
			return time();
	}

	function get_event(){
		global $current_schedule, $weekdays;
		if (isset($current_schedule) && isset($current_schedule[$weekdays[date('w', calc_time())].'_event']))
			return $current_schedule[$weekdays[date('w', calc_time())].'_event'];
		else
			return '';
	}

	function get_deal(){
		global $current_schedule, $weekdays;
		if (isset($current_schedule) && isset($current_schedule[$weekdays[date('w', calc_time())].'_deal']))
			return $current_schedule[$weekdays[date('w', calc_time())].'_deal'];
		else
			return '';
	}

	function get_theke(){
		global $current_schedule, $weekdays, $employee_names;
		if (isset($current_schedule) && isset($current_schedule[$weekdays[date('w', calc_time())].'_theke']))
			return $employee_names[$current_schedule[$weekdays[date('w', calc_time())].'_theke']];
		else
			return '';
	}

	function get_springer(){
		global $current_schedule, $weekdays, $employee_names;
		if (isset($current_schedule) && isset($current_schedule[$weekdays[date('w', calc_time())].'_springer']))
			return $employee_names[$current_schedule[$weekdays[date('w', calc_time())].'_springer']];
		else
			return '';
	}

	function get_kueche(){
		global $current_schedule, $weekdays, $employee_names;
		if (isset($current_schedule) && isset($current_schedule[$weekdays[date('w', calc_time())].'_kueche']))
			return $employee_names[$current_schedule[$weekdays[date('w', calc_time())].'_kueche']];
		else
			return '';
	}

	function parse_employee_name($employee, $short){
		$name = '';
		if($short && !empty($employee['display_name'])) $name.=$employee['display_name'];
		else{
			if(!empty($employee['first_name'])) $name.=$employee['first_name'];
			if(!empty($employee['display_name']) && $employee['display_name'] != $employee['first_name']) $name.=' „'.$employee['display_name'].'“';
			if(!empty($employee['last_name']))
				if($short) $name.=' '.substr($employee['last_name'],0,1).'.';
				else $name.=' '.$employee['last_name'];
		}
		if(!empty($employee['room_number']) || (!empty($employee['house.name']) && $employee['house.name']!='HSH')) $name.=' (';
		if(!empty($employee['house.name']) && $employee['house.name']!='HSH') $name.=$employee['house.name'];
		if(!empty($employee['room_number']) && !empty($employee['house.name']) && $employee['house.name']!='HSH') $name.=', ';
		if(!empty($employee['room_number'])) $name.=$employee['room_number'];
		if(!empty($employee['room_number']) || (!empty($employee['house.name']) && $employee['house.name']!='HSH')) $name.=')';

		return $name;
	}
?>

<html>
<head>

	<link href="style.css" rel="stylesheet" type="text/css" media="all">
	<link rel="stylesheet" href="../fonts/fork-awesome/css/fork-awesome.min.css">

	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">

	<title>Manhattan - Admin</title>

</head>
<body>

	<?php include('navbar.php'); ?>

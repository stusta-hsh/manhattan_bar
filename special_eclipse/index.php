<?php

	/*
	*	Erstellt im Juni 2019 von Tim Weber (HSH, 1007)
	*	auf Grundlage der Statusseite von Daniel Frejek (HSH, 1525)
	*	Kontakt: tim.weber@stusta.de
	*	Letzte Änderung: 07.07.2019
	*/

	$weekdays = ['so', 'mo', 'di', 'mi', 'do', 'fr', 'sa'];
	$days_from_monday = [6, 0, 1, 2, 3, 4, 5];

	// Datenbankabfrage aus der alten Seite um den Stand des Schalters zu prüfen.

	$servername = "localhost";
	$username = "e00038";
	$password = "hfDAJSDWy7vR5Pmd";
	$dbname = "e00038a";

	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) {
		 die("Connection failed: " . $conn->connect_error);
	}

	$db = mysqli_connect($servername, $username, $password, $dbname);
	if(!$db) exit("Database connection error: ".mysqli_connect_error());

	// Datenbankabfrage aktueller Wochenplan
	$sql = 'SELECT * FROM schedules WHERE calendar_week= ?';
	$sql_query = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($sql_query, 'i', date('W'));
	if (!$sql_query) die('ERROR: could not prepare sql: $sql');
	mysqli_stmt_execute($sql_query);
	$current_schedule=mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query));
	mysqli_stmt_close($sql_query);

	// Datenbankabfrage Mitarbeiter
	$sql = 'SELECT id, first_name, display_name FROM employees WHERE deleted=0';
	$sql_query = mysqli_prepare($db, $sql);
	if (!$sql_query) die('ERROR: could not prepare sql: $sql');
	mysqli_stmt_execute($sql_query);
	$employees = mysqli_stmt_get_result($sql_query);
	mysqli_stmt_close($sql_query);

	$employee_names=[];
	foreach($employees as $employee){
		if(!empty($employee['display_name']))
			$employee_names[$employee['id']]=$employee['display_name'];
		else
			$employee_names[$employee['id']]=$employee['first_name'];
	}

	$sql = "SELECT date, status FROM openstatus ORDER BY date DESC LIMIT 1";
	$result = $conn->query($sql);


	if ($result->num_rows == 0) {
		 die("db result empty!");
	}

	$row = $result->fetch_assoc();
	$status = $row["status"];
	$lastrefreshed = $row["date"];
	echo "<!--Last status: ". $status. "  updated: ". $lastrefreshed. " -->";

	$lrd = strtotime($lastrefreshed);
	$diff = time() - $lrd;

	if ($status != 0 && $diff > 43200)
	{
		 echo "\n<!--WARNING: Assuming CLOSED because the last status update is older than twelve hours! -->";
		 $status = 0;
	}


	if ($status == 1)
	{
		 $fcolor = "#000";
		 $desc = "Wir haben geöffnet!<br>Die Dachterrasse bleibt heute geschlossen.";
		 $titlestatus = "Geöffnet";
	}
	else if ($status == 2)
	{
		 $fcolor = "#000";
		 $desc = "Dachterrasse geöffnet!";
		 $titlestatus = "Dachterrasse geöffnet";
	}
	else
	{
		 $fcolor = "gray";
		 if(empty($current_schedule))
			 $desc = "Gerade geschlossen.<br>Der aktuelle Plan kommt bald.<br><br><br><br>";
		 elseif($current_schedule[$weekdays[date('w')].'_open'])
			 $desc = "Gerade geschlossen.<br>Wir öffnen wieder um 19 Uhr.";
		 else
			 $desc = "Heute bleiben wir geschlossen.";
		 $titlestatus = "Geschlossen";
	}

	$conn->close();

	// Ende der Datenbankabfrage

	function calc_time(){
		global $status;
		// Manhattan schon oder noch offen?
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
?>

<!DOCTYPE html>
<html>
<head>
	<link href="style.css" rel="stylesheet" type="text/css" media="all">
	<link rel="stylesheet" href="../fonts/fork-awesome/css/fork-awesome.min.css">

	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">

	<title>Manhattan - <?php echo($titlestatus); ?></title>

</head>

<body>
	<div class="content">
		<div class="logo">
			<img src="images/logo_eclipse.png" alt="Manhattan" width="100%">
		</div>

		<div class="status textbox">
			<h3><?php echo($desc); ?></h3>
		</div>

		<?php if(!empty($current_schedule)){
			if(!empty(get_event()) || !empty(get_deal())){ ?>
				<div class="special textbox">
					<br><br>
					<h4>Heute:</h4>
					<?php
					$acc1='';
					if(!empty(get_event()))
						$acc1.=get_event();
					if(!empty(get_event()) && !empty(get_deal()))
						$acc1.='<br>';
					if(!empty(get_deal()))
						$acc1.=get_deal();
					echo('<h2 style="color: #03aa2a">'.$acc1.'</h2>');
					?>
					<?php
					$acc2='';
					if(!empty(get_theke()) || !empty(get_springer()) || !empty(get_kueche())){
							$acc2.='mit ';
						if(!empty(get_kueche()))
							$acc2.=get_kueche();
						if(!empty(get_kueche()) && !empty(get_theke()) && empty(get_springer()))
							$acc2.=' & ';
						if(!empty(get_kueche()) && !empty(get_theke()) && !empty(get_springer()))
							$acc2.=', ';
						if(!empty(get_theke()))
							$acc2.=get_theke();
						if(!empty(get_springer()))
							$acc2.=' & '.get_springer();
						echo('<h3>'.$acc2.'</h3>');
					}
					?>
				</div>
			<?php } ?>

			<div class="textbox">
				<table class="wochenplan">
					<?php
					for($i=1; $i<8; $i++){ ?>
						<tr>
							<td>
								<?php echo ucfirst($weekdays[$i%7]) ?><br>
								<a style="font-size: 8pt">
									<?php echo date('j.n.', time()-($days_from_monday[date('w')]*24*60*60)+($i-1)*24*60*60); ?>
								</a>
							</td>
							<td>
								<?php
								if(!$current_schedule[$weekdays[$i%7].'_open']){
									echo('<span style="color: grey">geschlossen</span>');
								}else{
									if(empty($current_schedule[$weekdays[$i%7].'_deal']) && empty($current_schedule[$weekdays[$i%7].'_event']))
										echo('geöffnet');
									if(!empty($current_schedule[$weekdays[$i%7].'_event']))
										echo('<a id="'.$weekdays[$i%7].'_event'.'">'.$current_schedule[$weekdays[$i%7].'_event'].'</a><br>');
									if(!empty($current_schedule[$weekdays[$i%7].'_deal']))
										echo('<a id="'.$weekdays[$i%7].'_deal'.'">'.$current_schedule[$weekdays[$i%7].'_deal'].'</a>');
							 	}?><br>
							</td>
							<td>
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
		<?php } ?>

		<div class="skyline">
			<div class="skyline-image-div">
				<img class="wheel" src="images/wheel2.svg">
				<img class="skyline-image" src="images/skyline_night.png">
			</div>
			<div class="skyline-spacer"></div>
		</div>


	</div>
	<div class="footer">
		Geöffnet ab 19:00 Uhr<br>
		Nur für Bewohner der Studentenstadt Freimann
		<div class="social-icons">
			<a href="https://www.facebook.com/manhattanbarhsh/"><i class="fa fa-facebook-square"></i></a>
			<a href="mailto:manhattan@stusta.de"><i class="fa fa-envelope-square"></i></a>
			<a href="https://wiki.stusta.de/Manhattan"><i class="fa fa-book"></i></a>
		</div>
	</div>

</body>
</html>

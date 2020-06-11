<?php

$rota = parse_ini_file('../wochenplan_'.date('W').'.ini');
$weekdays = ['so', 'mo', 'di', 'mi', 'do', 'fr', 'sa'];
$months = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
$days_from_monday = [6, 0, 1, 2, 3, 4, 5];
if(isset($_GET['id'])) $id = $_GET['id'];

include('../sql_config.php');
$db = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_dbname);
if(!$db) exit("Database connection error: ".mysqli_connect_error());

$page_title='schedules';

require('fpdf181/fpdf.php');

// Datenbankabfrage Wochenplan
$sql = 'SELECT * FROM schedules WHERE id= ?';
$sql_query = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($sql_query, 'i', $id);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$schedule = mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query));
mysqli_stmt_close($sql_query);

if(empty($schedule)){
	header('Location: schedule_list.php');
	exit();
}

$monday = (strtotime("first thursday of January ".$schedule['year']." +".$schedule['calendar_week']." week -1 week last Monday"));
$sunday = $monday+(60*60*24*6);

// Datenbankabfrage Liste aller aktiven Mitarbeiter
$sql = 'SELECT employees.id, employees.first_name, employees.last_name, employees.display_name, employees.room_number, houses.name AS "house.name" FROM employees LEFT JOIN houses ON employees.house = houses.id  WHERE employees.deleted=0 AND employees.active=1 ORDER BY employees.first_name ASC, employees.last_name ASC';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
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

//PDF-variables
$borders = 1; //draw cell-borders 0, 1, R, T, L, B
$line_height = 6;
$line_margin = 3;

$pdf = new FPDF();
$pdf->AddFont('raleway','','Raleway-Medium.php');

$pdf->SetTitle('Wochenplan '.$schedule['year'].'-'.$schedule['calendar_week']);
$pdf->SetAuthor(ucfirst($_SERVER['PHP_AUTH_USER']));
$pdf->SetCreator('Manhattan WebApp');

$pdf->AddPage();
$pdf->SetMargins(20, 10);
$pdf->SetFont('Raleway', '', 15);
$pdf->Image('../images/logo.png', 23, 10, -300, -300);
$pdf->SetY(70);
$pdf->SetFontSize(20);
/*
if(date('n', $monday) == date('n', $sunday)){
	echo date('j.', $monday).' - '.date('j. ', $sunday).$months[date('n', $monday)-1];
}else{
	echo date('j. ', $monday).$months[date('n', $monday)-1].' - '.date('j. ', $sunday).$months[date('n', $sunday)-1];
}*/

if(date('n', $monday) == date('n', $sunday)){
	$pdf->Cell(0, $line_height, date('j.', $monday).' - '.date('j. ', $sunday).$months[date('n', $monday)-1], 0, 0, 'C');
}else{
	$pdf->Cell(0, $line_height, date('j. ', $monday).$months[date('n', $monday)-1].' - '.date('j. ', $sunday).$months[date('n', $sunday)-1], 0, 0, 'C');
}
$pdf->SetFontSize(15);
$pdf->SetY(90);
// Für jeden Wochentag
for($i=1; $i<8; $i++){
	$x=$pdf->GetX();
	$y=$pdf->GetY();
	// Farbe abwechselnd grün und weiß
	if($i%2==0) $pdf->SetDrawColor(256, 256, 256); else $pdf->SetDrawColor(190, 229, 198);
	if($i%2==0) $pdf->SetFillColor(256, 256, 256); else $pdf->SetFillColor(190, 229, 198);
	// Padding oben in der Zeile
	$pdf->Cell(0, $line_margin, '', $borders, 1, 'C', 'true');

	// Wochentagkürzel
	$pdf->Cell(15, $line_height, ucfirst($weekdays[$i%7]), $borders, 2, 'C', 'true');
	// Datum TT.MM.
	$pdf->SetFontSize(10);
	$pdf->Cell(15, $line_height, date('j.n.', $monday+($i-1)*60*60*24), $borders, 0, 'C', 'true');
	$pdf->SetFontSize(15);
	$pdf->SetXY($x+15, $y+$line_margin);

	// Tagestext
	if(!$schedule[$weekdays[$i%7].'_open']){
		// geschlossen
		$pdf->SetTextColor(100, 100, 100);
		$pdf->Cell(100, $line_height*2, 'geschlossen', $borders, 0, 'C', 'true');
		$pdf->SetTextColor(0, 0, 0);
	}elseif(!$schedule[$weekdays[$i%7].'_deal'] && !$schedule[$weekdays[$i%7].'_event']){
		// Geöffnet, aber weder Deal noch Event eingetragen
		$pdf->Cell(100, $line_height*2, iconv('UTF-8', 'windows-1252', 'Geöffnet'), $borders, 0, 'C', 'true');
	}elseif(!$schedule[$weekdays[$i%7].'_event']){
		// Geöffnet, kein Event eingetragen
		if(strpos($schedule[$weekdays[$i%7].'_deal'], '<br>')){
			$pdf->MultiCell(100, $line_height, iconv('UTF-8', 'windows-1252', str_replace("<br>","\n",$schedule[$weekdays[$i%7].'_deal'])), $borders, 'C', 'true');
		}else{
			$pdf->Cell(100, $line_height*2, iconv('UTF-8', 'windows-1252', str_replace("<br>","",$schedule[$weekdays[$i%7].'_deal'])), $borders, 0, 'C', 'true');
		}
	}elseif(!$schedule[$weekdays[$i%7].'_deal']){
		// Geöffnet, kein Deal eingetragen
		$pdf->Cell(100, $line_height*2, iconv('UTF-8', 'windows-1252', str_replace("<br>","",$schedule[$weekdays[$i%7].'_event'])), $borders, 0, 'C', 'true');
	}else{
		// Geöffnet, Deal und Event eingetragen
		$pdf->Cell(100, $line_height, iconv('UTF-8', 'windows-1252', $schedule[$weekdays[$i%7].'_event']), $borders, 2, 'C', 'true');
		$pdf->SetFontSize(12);
		$pdf->Cell(100, $line_height, iconv('UTF-8', 'windows-1252', str_replace("<br>","",str_replace("<sup>","",str_replace("</sup>","",$schedule[$weekdays[$i%7].'_deal'])))), $borders, 0, 'C', 'true');
		$pdf->SetFontSize(15);
	}

	$pdf->SetXY($x+115, $y+$line_margin);

	// Team
	if(!$schedule[$weekdays[$i%7].'_open']){
		// geschlossen
		$pdf->Cell(0, $line_height*2, '', $borders, 1, 'C', 'true');
	}elseif(!$schedule[$weekdays[$i%7].'_kueche']){
		// Geöffnet, keine Küchenschicht eingetragen
		if($schedule[$weekdays[$i%7].'_springer']){
			$pdf->Cell(0, $line_height*2, iconv('UTF-8', 'windows-1252', $employee_names[$schedule[$weekdays[$i%7].'_theke']].' & '.$employee_names[$schedule[$weekdays[$i%7].'_springer']]), $borders, 1, 'C', 'true');
		}else{
			$pdf->Cell(0, $line_height*2, iconv('UTF-8', 'windows-1252', $employee_names[$schedule[$weekdays[$i%7].'_theke']]), $borders, 1, 'C', 'true');
		}
	}else{
		// Geöffnet, Küchenschicht eingetragen
		if(!$schedule[$weekdays[$i%7].'_springer']){
			$pdf->Cell(0, $line_height*2, iconv('UTF-8', 'windows-1252', $employee_names[$schedule[$weekdays[$i%7].'_kueche']].' & '.$employee_names[$schedule[$weekdays[$i%7].'_theke']]), $borders, 2, 'C', 'true');
		}else{
			$pdf->Cell(0, $line_height, iconv('UTF-8', 'windows-1252', $employee_names[$schedule[$weekdays[$i%7].'_kueche']].','), $borders, 2, 'C', 'true');
			$pdf->Cell(0, $line_height, iconv('UTF-8', 'windows-1252', $employee_names[$schedule[$weekdays[$i%7].'_theke']].' & '.$employee_names[$schedule[$weekdays[$i%7].'_springer']]), $borders, 1, 'C', 'true');
		}
	}
	$pdf->Cell(0, $line_margin, '', $borders, 1, 'C', 'true');
}
$pdf->SetY($y+30);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetFillColor(0, 0, 0);
$pdf->Rect(0,280,250,100,'DF');
$pdf->Image('../images/skyline_static.png', 10, 246, 190);
$pdf->SetFontSize(13);
$pdf->Cell(0, 8, iconv('UTF-8', 'windows-1252', 'HSH 21. Stock | Geöffnet ab 19 Uhr'), 0, 2, 'C');
$pdf->Cell(0, 8, iconv('UTF-8', 'windows-1252', 'Wochenplan und aktueller Status unter manhattan.stusta.de'), 0, 0, 'C');

$pdf->Output('I', 'wochenplan_'.$schedule['year'].'-'.$schedule['calendar_week']);

?>

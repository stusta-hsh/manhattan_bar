<?php

$rota = parse_ini_file('../wochenplan_'.date('W').'.ini');
$weekdays = ['so', 'mo', 'di', 'mi', 'do', 'fr', 'sa'];
$days_from_monday = [6, 0, 1, 2, 3, 4, 5];
if(isset($_GET['id'])) $id = $_GET['id'];

$servername = "localhost";
$username = "e00038";
$password = "hfDAJSDWy7vR5Pmd";
$dbname = "e00038a";

$db = mysqli_connect($servername, $username, $password, $dbname);
if(!$db) exit("Database connection error: ".mysqli_connect_error());

$page_title='schedules';

require('fpdf181/fpdf.php');

// Datenbankabfrage Wochenplan
$sql = 'SELECT * FROM schedules WHERE id= ?';
$sql_query = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($sql_query, 'i', $id);
if (!$sql_query) die('ERROR: could not prepare sql: $sql');
mysqli_stmt_execute($sql_query);
$schedule = mysqli_fetch_assoc(mysqli_stmt_get_result($sql_query));
mysqli_stmt_close($sql_query);

if(empty($schedule)){
	header('Location: schedule_list.php');
	exit();
}

// Datenbankabfrage Liste aller aktiven Mitarbeiter
$sql = 'SELECT employees.id, employees.first_name, employees.last_name, employees.display_name, employees.room_number, houses.name AS "house.name" FROM employees LEFT JOIN houses ON employees.house = houses.id  WHERE employees.deleted=0 AND employees.active=1 ORDER BY employees.first_name ASC, employees.last_name ASC';
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

//PDF-variables
$borders = 1; //draw cell-borders 0, 1, R, T, L, B
$line_height = 6;
$line_margin = 3;

$pdf = new FPDF();
$pdf->AddFont('raleway','','Raleway-Medium.php');
$pdf->AddPage();
$pdf->SetMargins(20, 10);
$pdf->SetFont('Raleway', '', 15);
$pdf->Image('../images/logo.png', 23, 10, -300, -300);
$pdf->SetY(100);
$pdf->Ln();
for($i=1; $i<8; $i++){
	$x=$pdf->GetX();
	$y=$pdf->GetY();
	if($i%2==0) $pdf->SetDrawColor(256, 256, 256); else $pdf->SetDrawColor(190, 229, 198);
	if($i%2==0) $pdf->SetFillColor(256, 256, 256); else $pdf->SetFillColor(190, 229, 198);
	$pdf->Cell(0, $line_margin, '', $borders, 1, 'C', 'true');

	$pdf->Cell(15, $line_height, ucfirst($weekdays[$i%7]), $borders, 2, 'C', 'true');
	$pdf->SetFontSize(10);
	$pdf->Cell(15, $line_height, date('j.n.', strtotime($schedule['year'].'-W'.$schedule['calendar_week'].'-'.$i)), $borders, 0, 'C', 'true');
	$pdf->SetFontSize(15);
	$pdf->SetXY($x+15, $y+$line_margin);

	if(!$schedule[$weekdays[$i%7].'_open']){
		$pdf->Cell(100, $line_height*2, 'geschlossen', $borders, 0, 'C', 'true');
	}elseif(!$schedule[$weekdays[$i%7].'_event']){
		if(strpos($schedule[$weekdays[$i%7].'_deal'], '<br>')){
			$pdf->MultiCell(100, $line_height, iconv('UTF-8', 'windows-1252', str_replace("<br>","\n",$schedule[$weekdays[$i%7].'_deal'])), $borders, 'C', 'true');
		}else{
			$pdf->Cell(100, $line_height*2, iconv('UTF-8', 'windows-1252', str_replace("<br>","",$schedule[$weekdays[$i%7].'_deal'])), $borders, 0, 'C', 'true');
		}
	}else{
		$pdf->Cell(100, $line_height, iconv('UTF-8', 'windows-1252', $schedule[$weekdays[$i%7].'_event']), $borders, 2, 'C', 'true');
		$pdf->Cell(100, $line_height, iconv('UTF-8', 'windows-1252', str_replace("<br>","",str_replace("<sup>","",str_replace("</sup>","",$schedule[$weekdays[$i%7].'_deal'])))), $borders, 0, 'C', 'true');
	}

	$pdf->SetXY($x+115, $y+$line_margin);

	if(!$schedule[$weekdays[$i%7].'_open']){
		$pdf->Cell(0, $line_height*2, '', $borders, 1, 'C', 'true');
	}elseif(!$schedule[$weekdays[$i%7].'_kueche']){
		if($schedule[$weekdays[$i%7].'_springer']){
			$pdf->Cell(0, $line_height*2, iconv('UTF-8', 'windows-1252', $employee_names[$schedule[$weekdays[$i%7].'_theke']].' & '.$employee_names[$schedule[$weekdays[$i%7].'_springer']]), $borders, 1, 'C', 'true');
		}else{
			$pdf->Cell(0, $line_height*2, iconv('UTF-8', 'windows-1252', $employee_names[$schedule[$weekdays[$i%7].'_theke']]), $borders, 1, 'C', 'true');
		}
	}else{
		if(!$schedule[$weekdays[$i%7].'_springer']){
			$pdf->Cell(0, $line_height*2, iconv('UTF-8', 'windows-1252', $employee_names[$schedule[$weekdays[$i%7].'_kueche']].' & '.$employee_names[$schedule[$weekdays[$i%7].'_theke']]), $borders, 2, 'C', 'true');
		}else{
			$pdf->Cell(0, $line_height, iconv('UTF-8', 'windows-1252', $employee_names[$schedule[$weekdays[$i%7].'_kueche']].','), $borders, 2, 'C', 'true');
			$pdf->Cell(0, $line_height, iconv('UTF-8', 'windows-1252', $employee_names[$schedule[$weekdays[$i%7].'_theke']].' & '.$employee_names[$schedule[$weekdays[$i%7].'_springer']]), $borders, 1, 'C', 'true');
		}
	}
	//$pdf->MultiCell(0, $line_height*2, parse_shift($i%7), $borders, 'C', 'true');

	$pdf->Cell(0, $line_margin, '', $borders, 1, 'C', 'true');
}
$pdf->SetY($y+30);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetFillColor(0, 0, 0);
$pdf->Rect(0,280,250,100,'DF');
$pdf->Image('../images/skyline_static.png', 5, 245, 200);
$pdf->SetFontSize(13);
$pdf->Cell(0, 10, 'Wochenplan und aktueller Status unter manhattan.stusta.de', 0, 0, 'C');

$pdf->Output();

?>

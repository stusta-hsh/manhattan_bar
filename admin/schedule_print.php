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

function get_special(){
	global $rota, $weekdays;
	if (isset($rota) && isset($rota[$weekdays[date('w', calc_time())].'_special']))
		return $rota[$weekdays[date('w', calc_time())].'_special'];
	else
		return '';
}

function get_schicht(){
	global $rota, $weekdays;
	if (isset($rota) && isset($rota[$weekdays[date('w', calc_time())].'_schicht']))
		return $rota[$weekdays[date('w', calc_time())].'_schicht'];
	else
		return '';
}

function parse_employee_name($employee, $short){
	$name = '';
	if($short && !empty($employee['display_name'])) $name.=$employee['display_name'];
	else{
		if(!empty($employee['first_name'])) $name.=$employee['first_name'];
		if(!empty($employee['display_name'])) $name.=' „'.$employee['display_name'].'“';
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

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Image('../images/logo.png', 23, 10, -300, -300);
$pdf->SetY(80);
$pdf->Ln();
for($i=1; $i<8; $i++){
	$pdf->Cell(10, 10, ucfirst($weekdays[$i%7]));
	$pdf->Cell(15, 10, date('j.n.', strtotime($schedule['year'].'-W'.$schedule['calendar_week'].'-'.$i)));
	$pdf->Cell(30, 10, iconv('UTF-8', 'windows-1252', $schedule[$weekdays[$i%7].'_event']));
	$pdf->Cell(80, 10, iconv('UTF-8', 'windows-1252', $schedule[$weekdays[$i%7].'_deal']));
	$pdf->Cell(20, 10, iconv('UTF-8', 'windows-1252', $employee_names[$schedule[$weekdays[$i%7].'_kueche']]));
	$pdf->Cell(20, 10, iconv('UTF-8', 'windows-1252', $employee_names[$schedule[$weekdays[$i%7].'_theke']]));
	$pdf->Cell(20, 10, iconv('UTF-8', 'windows-1252', $employee_names[$schedule[$weekdays[$i%7].'_springer']]));
	$pdf->Ln();
}
$pdf->Cell(0, 10, 'Wochenplan und aktueller Status auch unter www.manhattan.stusta.de', 0, 0, 'C');
$pdf->Output();

?>

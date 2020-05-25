<?php

//echo("debug");

if(isset($_GET['date'])) $date = $_GET['date'];

include('../sql_config.php');
$db = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_dbname);
if(!$db) exit("Database connection error: ".mysqli_connect_error());

require('fpdf181/fpdf.php');

// Datenbankabfrage Bestellungen
$sql = 'SELECT id, house, room, comment, patty, cheese, friedonions, pickles, bacon, camembert, beilage, dip_1, dip_2, bier FROM menu_positions RIGHT JOIN orders ON menu_positions.order_id = orders.id';
$sql_query = mysqli_prepare($db, $sql);
//mysqli_stmt_bind_param($sql_query, 'i', $date);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$orders = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

if(empty($orders)){
	header('Location: order_list.php');
	exit();
}

$house = ['extern', 'HSH'];

$patty = ['Beef', 'Beyond Meat', 'Double-Burger'];
$cheese = ['', ' mit Käse'];
$friedonions = ['', '+ Röstzw.'];
$pickles = ['', '+ Gurken'];
$bacon = ['', '+ Bacon'];
$camembert = ['', '+ Camem.'];

$beilage = ['', 'Pommes', 'Wedges'];
$dip_1 = ['', '+ Mayo'];
$dip_2 = ['', '+ Ketchup'];
$bier = ['', 'Augustiner', 'Tegernseer', 'Schneider TAP7', 'Schneider TAP3', 'Kuchlbauer', 'Weihenstephaner', 'Spezi', 'Almdudler', 'Club Mate', 'Bulmers', 'Bulmers Pear'];

//echo("debug");

//PDF-variables
$rows = 8;
$columns = 3;
$draw_borders = 0;

$pdf = new FPDF();
$pdf->AddFont('raleway','','Raleway-Medium.php');

//$pdf->SetTitle('Bestellungen '.$date);
$pdf->SetAuthor(ucfirst($_SERVER['PHP_AUTH_USER']));
$pdf->SetCreator('Manhattan WebApp');

$pdf->SetMargins(0, 0);
$pdf->SetAutoPageBreak(false, 0);

$pdf->SetFont('Raleway', '', 15);

$cell_width = $pdf->GetPageWidth()/$columns;
$cell_height = $pdf->GetPageHeight()/$rows;

function print_cell($order){
	global $pdf, $cell_width, $cell_height, $cheese, $friedonions, $pickles, $bacon, $camembert, $beilage, $dip_1, $dip_2, $bier;

	if(isset($order['patty'])){
		switch ($order['patty']) {
			case 0:
				$order['cheese'] ? $burger = 'Cheeseburger' : $burger = 'Hamburger';
				$order['cheese'] = 0;
				break;
			case 1:
				$burger = 'Beyond-Meat-Burger';
				break;
			case 2:
				$burger = 'Double-Burger';
				break;
		}
	}

	$x = $pdf->GetX();
	$y = $pdf->GetY();
	$pdf->SetFontSize(12);


	$pdf->Cell($cell_width, $cell_height/8, $order['house'].', '.iconv('UTF-8', 'windows-1252', $order['room']), $draw_borders, 0);
	$pdf->SetXY($x, $y);
	$pdf->Cell($cell_width, $cell_height/8, $order['id'], 'B', 2, 'R');

	$pdf->Cell($cell_width, $cell_height/8, $burger.iconv('UTF-8', 'windows-1252', $cheese[$order['cheese']]), $draw_borders, 2);
	$pdf->SetFontSize(10);
	$pdf->Cell($cell_width, $cell_height/8, iconv('UTF-8', 'windows-1252', $friedonions[$order['friedonions']]).' '.$pickles[$order['pickles']].' '.$bacon[$order['bacon']].' '.$camembert[$order['camembert']], $draw_borders, 2, 'R');

	$pdf->SetFontSize(12);
	$pdf->Cell($cell_width, $cell_height/8, $beilage[$order['beilage']], $draw_borders, 0);
	$pdf->SetXY($x, $y+($cell_height/8)*3);
	$pdf->SetFontSize(10);
	$pdf->Cell($cell_width, $cell_height/8, $dip_1[$order['dip_1']].' '.$dip_2[$order['dip_2']], $draw_borders, 2, 'R');

	$pdf->SetFontSize(12);
	$pdf->Cell($cell_width, $cell_height/8, $bier[$order['bier']], $draw_borders, 2, 'R');
	$pdf->SetFontSize(8);
	$pdf->MultiCell($cell_width, $cell_height/12, iconv('UTF-8', 'windows-1252', $order['comment']), 'T');

	$pdf->SetXY($x+$cell_width, $y);
}


for ($page=0; $page<mysqli_num_rows($orders)/($rows*$columns); $page++) {
	$pdf->AddPage();
	for ($row=0; $row<$rows; $row++) {
		for ($column=0; $column<$columns; $column++) {
			$order = mysqli_fetch_assoc($orders);
			if(!empty($order)) print_cell($order);
		}
		$pdf->SetXY(0, ($row+1)*$cell_height);
	}
}



$pdf->Output('I', 'etiketten');

?>

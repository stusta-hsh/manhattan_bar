<?php

$date = (isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'));

include('../sql_config.php');
$db = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_dbname);
if(!$db) exit("Database connection error: ".mysqli_connect_error());

require('fpdf181/fpdf.php');

// Datenbankabfrage Bestellungen
$sql = 'SELECT orders.id, houses.shortname AS house, room, orders.name as name, phone, paid, slot, position, comment, patty, cheese, friedonions, pickles, bacon, camembert, beilage, dip_1, dip_2, bier FROM orders INNER JOIN menu_positions ON menu_positions.order_id = orders.id LEFT JOIN houses ON houses.id = orders.house WHERE orders.deleted = 0 AND DATE(orders.date) = ? ORDER BY slot ASC, houses.delivery_order ASC, room ASC';
$sql_query = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($sql_query, 's', $date);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$orders = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

if(mysqli_num_rows($orders) == 0){
	echo('Keine Bestellungen ausgewählt');
	exit();
}

$patty = ['Beef', 'Beyond Meat', 'Double-Burger'];
$cheese = ['', ' mit Käse'];
$friedonions = ['', '+Röstz'];
$pickles = ['', '+Gurke'];
$bacon = ['', '+Bacon'];
$camembert = ['', '+Cam'];

$beilage = ['', 'Pommes', 'Wedges'];
$dip_1 = ['', '+Mayo'];
$dip_2 = ['', '+Ketchup'];
$bier = ['', 'Augustiner', 'Tegernseer', 'Schneider TAP7', 'Schneider TAP3', 'Kuchlbauer', 'Weihenstephaner', 'Spezi', 'Almdudler', 'Club Mate', 'Bulmers', 'Bulmers Pear'];

//PDF-variables
$pdf = new FPDF();
$pdf->AddFont('raleway','','Raleway-Medium.php');

$pdf->SetTitle('Bestellungen '.$date);
$pdf->SetAuthor(ucfirst($_SERVER['PHP_AUTH_USER']));
$pdf->SetCreator('Manhattan WebApp');

$pdf->SetMargins(10, 10);
$pdf->SetAutoPageBreak(true, 10);

$pdf->SetFont('courier', '', 15);

$draw_borders = 0;
$cell_height = 5;

function print_order($order){
	global $pdf, $cell_width, $cell_height, $cell_margin, $draw_borders, $cheese, $friedonions, $pickles, $bacon, $camembert, $beilage, $dip_1, $dip_2, $bier, $houses;

	$order['slot']++;

	$draw_borders = 'T';

	$pdf->SetFont('courier', 'B', 12);
	$pdf->Cell(5, $cell_height, $order['slot'], $draw_borders);
	$pdf->Cell(10, $cell_height, $order['id'], $draw_borders);
	$pdf->Cell(40, $cell_height, $order['house'].', '.iconv('UTF-8', 'windows-1252', $order['room']), $draw_borders);
	$pdf->Cell(50, $cell_height, iconv('UTF-8', 'windows-1252', $order['phone']), $draw_borders);
	$pdf->Cell(70, $cell_height, iconv('UTF-8', 'windows-1252', $order['name']), $draw_borders,1);
	$draw_borders = '0';
	$pdf->SetFont('courier', '', 10);
	if(!empty($order['comment'])){
		$pdf->SetX(20);
		$pdf->MultiCell(0, $cell_height, iconv('UTF-8', 'windows-1252', $order['comment']), $draw_borders, 1);
	}
	$pdf->SetFontSize(12);
}

function print_position($order){
	global $pdf, $cell_width, $cell_height, $cell_margin, $draw_borders, $cheese, $friedonions, $pickles, $bacon, $camembert, $beilage, $dip_1, $dip_2, $bier, $houses;

	$pdf->SetFont('courier', '', 10);
	if(isset($order['patty'])){
		switch ($order['patty']) {
			case 0:
				$order['cheese'] ? $burger = 'Cheeseburger' : $burger = 'Hamburger';
				$order['cheese'] = 0;
				break;
			case 1:
				$burger = 'Beyond-Meat';
				break;
			case 2:
				$burger = 'Double-Burger';
				break;
		}
	}

	$pdf->SetFontSize(12);
	$pdf->SetX(20);
	$pdf->Cell(50, $cell_height, $burger, $draw_borders);
	$pdf->Cell(70, $cell_height, $beilage[$order['beilage']].' '.$dip_1[$order['dip_1']].' '.$dip_2[$order['dip_2']], $draw_borders);
	$pdf->Cell(50, $cell_height, $bier[$order['bier']], $draw_borders, 1);
}

function print_cell($order){
	global $pdf, $cell_width, $cell_height, $cell_margin, $cheese, $friedonions, $pickles, $bacon, $camembert, $beilage, $dip_1, $dip_2, $bier, $houses;

	if(isset($order['patty'])){
		switch ($order['patty']) {
			case 0:
				$order['cheese'] ? $burger = 'Cheeseburger' : $burger = 'Hamburger';
				$order['cheese'] = 0;
				break;
			case 1:
				$burger = 'Beyond-Meat';
				break;
			case 2:
				$burger = 'Double-Burger';
				break;
		}
	}
	$order['slot']++;

	$x = $pdf->GetX();
	$y = $pdf->GetY();
	$pdf->SetFontSize(10);

	$pdf->SetXY($x + $cell_margin, $y);

	$pdf->SetFontSize(10);
	$pdf->Cell($cell_width-2*$cell_margin, ($cell_height-$cell_margin)/8, $order['house'].', '.iconv('UTF-8', 'windows-1252', $order['room']), $draw_borders, 0);
	$pdf->SetXY($x + $cell_margin, $y);
	$pdf->Cell($cell_width-2*$cell_margin, ($cell_height-$cell_margin)/8, $order['slot'].' #'.$order['id'].'-'.$order['position'], 'B', 2, 'R');

	//$pdf->SetFontSize(12);
	$pdf->SetFont('courier', 'B', 12);
	$pdf->Cell($cell_width-2*$cell_margin, ($cell_height-$cell_margin)/8, $burger.iconv('UTF-8', 'windows-1252', $cheese[$order['cheese']]), $draw_borders, 2);
	$pdf->SetX($x + $cell_width/8);
	//$pdf->SetFontSize(10);
	$pdf->SetFont('courier', '', 10);
	$pdf->Cell(($cell_width-2*$cell_margin)-($cell_width/5), ($cell_height-$cell_margin)/10, iconv('UTF-8', 'windows-1252', $friedonions[$order['friedonions']]).' '.$pickles[$order['pickles']].' '.$bacon[$order['bacon']].' '.$camembert[$order['camembert']], $draw_borders, 2, 'L');
	$pdf->SetX($x + $cell_margin);

	//$pdf->SetFontSize(12);
	$pdf->SetFont('courier', 'B', 12);
	$pdf->Cell($cell_width-2*$cell_margin, ($cell_height-$cell_margin)/8, $beilage[$order['beilage']], $draw_borders, 2);
	$pdf->SetXY($x + $cell_margin, $y + (($cell_height-$cell_margin)/12)*5);
	$pdf->Cell($cell_width-2*$cell_margin, ($cell_height-$cell_margin)/8, iconv('UTF-8', 'windows-1252', $bier[$order['bier']]), $draw_borders, 2, 'R');

	$pdf->SetX($x + $cell_width/8);
	//$pdf->SetFontSize(10);
	$pdf->SetFont('courier', '', 10);
	$pdf->Cell($cell_width-2*$cell_margin, ($cell_height-$cell_margin)/10, $dip_1[$order['dip_1']].' '.$dip_2[$order['dip_2']], $draw_borders, 2, 'L');
	$pdf->SetX($x + $cell_margin);

	$pdf->SetFontSize(8);
	$pdf->MultiCell($cell_width-2*$cell_margin, ($cell_height-$cell_margin)/13, substr(iconv('UTF-8', 'windows-1252',  preg_replace( '/\r|\n/', ' ', $order['comment'])), 0, 150), 'T');

	$pdf->SetXY($x+$cell_width, $y);
}


$pdf->AddPage();
$slot = 0;
while ($order = mysqli_fetch_assoc($orders)) {
	if($order['slot'] == 1 && $slot == 0){
		$pdf->AddPage();
		$slot++;
	}
	if(!empty($order) && $order['position'] > 1){
		print_position($order);
	} else if(!empty($order)){
		print_order($order);
		print_position($order);
	}

}



$pdf->Output('I', 'etiketten');

?>

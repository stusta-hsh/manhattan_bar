<?php

$date = (isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'));

include('../sql_config.php');
$db = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_dbname);
if(!$db) exit("Database connection error: ".mysqli_connect_error());

require('fpdf181/fpdf.php');

// Datenbankabfrage Bestellungen
$sql = 'SELECT orders.id, houses.shortname AS house, room, slot, comment, patty, cheese, friedonions, pickles, bacon, camembert, beilage, dip_1, dip_2, bier FROM orders INNER JOIN menu_positions ON menu_positions.order_id = orders.id LEFT JOIN houses ON houses.id = orders.house WHERE orders.deleted = 0 AND DATE(orders.date) = ? ORDER BY slot ASC, houses.delivery_order ASC, room ASC';
$sql_query = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($sql_query, 's', $date);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$orders = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

if(empty($orders)){
	echo('Keine Bestellungen ausgwählt');
	exit();
}

$patty = ['Beef', 'Beyond Meat', 'Double-Burger'];
$cheese = ['', ' mit Käse'];
$friedonions = ['', '+Röstz.'];
$pickles = ['', '+Gurken'];
$bacon = ['', '+Bacon'];
$camembert = ['', '+Cam.'];

$beilage = ['', 'Pommes', 'Wedges'];
$dip_1 = ['', '+Mayo'];
$dip_2 = ['', '+Ketchup'];
$bier = ['', 'Augustiner Helles', 'Tegernseer Spezial', 'Schneider Weiße TAP7', 'Schneider Weiße TAP3', 'Kuchlbauer Alte Liebe', 'Weihenstephaner Naturradler', 'Paulaner Spezi', 'Almdudler', 'Club Mate', 'Bulmers Cider', 'Bulmers Cider Pear'];

//PDF-variables
$pdf = new FPDF();
$pdf->AddFont('raleway','','Raleway-Medium.php');

//$pdf->SetTitle('Bestellungen '.$date);
$pdf->SetAuthor(ucfirst($_SERVER['PHP_AUTH_USER']));
$pdf->SetCreator('Manhattan WebApp');

$pdf->SetMargins(0, 0);
$pdf->SetAutoPageBreak(false, 0);

$pdf->SetFont('Raleway', '', 15);

$rows = 8;
$columns = 3;
$draw_borders = 0;
$cell_margin = 5;
$cell_width = $pdf->GetPageWidth()/$columns;
$cell_height = $pdf->GetPageHeight()/$rows;

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

	$pdf->Write($date);

	$pdf->SetXY($x + $cell_margin, $y + $cell_margin);

	$pdf->Cell($cell_width-2*$cell_margin, ($cell_height-2*$cell_margin)/8, $order['house'].', '.iconv('UTF-8', 'windows-1252', $order['room']), $draw_borders, 0);
	$pdf->SetXY($x + $cell_margin, $y + $cell_margin);
	$pdf->Cell($cell_width-2*$cell_margin, ($cell_height-2*$cell_margin)/8, $order['slot'].' #'.$order['id'], 'B', 2, 'R');

	$pdf->Cell($cell_width-2*$cell_margin, ($cell_height-2*$cell_margin)/8, $burger.iconv('UTF-8', 'windows-1252', $cheese[$order['cheese']]), $draw_borders, 2);
	$pdf->SetFontSize(8);
	$pdf->Cell($cell_width-2*$cell_margin, ($cell_height-2*$cell_margin)/8, iconv('UTF-8', 'windows-1252', $friedonions[$order['friedonions']]).' '.$pickles[$order['pickles']].' '.$bacon[$order['bacon']].' '.$camembert[$order['camembert']], $draw_borders, 2, 'R');

	$pdf->SetFontSize(10);
	$pdf->Cell($cell_width-2*$cell_margin, ($cell_height-2*$cell_margin)/8, $beilage[$order['beilage']], $draw_borders, 0);
	$pdf->SetXY($x + $cell_margin, $y + $cell_margin+(($cell_height-2*$cell_margin)/8)*3);
	$pdf->SetFontSize(8);
	$pdf->Cell($cell_width-2*$cell_margin, ($cell_height-2*$cell_margin)/8, $dip_1[$order['dip_1']].' '.$dip_2[$order['dip_2']], $draw_borders, 2, 'R');

	$pdf->SetFontSize(10);
	$pdf->Cell($cell_width-2*$cell_margin, ($cell_height-2*$cell_margin)/8, $bier[$order['bier']], $draw_borders, 2, 'R');
	$pdf->SetFontSize(8);
	$pdf->MultiCell($cell_width-2*$cell_margin, ($cell_height-2*$cell_margin)/10, substr(iconv('UTF-8', 'windows-1252',  preg_replace( '/\r|\n/', '', $order['comment'])), 0, 200), 'T');

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

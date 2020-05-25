<?php
$page_title='order';

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

$date = (isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'));

?>
<div class='toolbar-background'>
	<div class='toolbar'>
		<span><a href='order_list.php'><i class='fa fa-list'></i><br>Alle Bestellungen</a></span>
		<span><a href='order_print.php'><i class='fa fa-print'></i><br>Liste</a></span>
		<span><a href="<?php echo('order_print_stickers.php?date='.$date) ?>"><i class='fa fa-print'></i><br>Etiketten</a></span>
	</div>
</div>
</body>
</html>

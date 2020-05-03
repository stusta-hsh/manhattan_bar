<?php

// Datenbankverbindung
include('../sql_config.php');
$db = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_dbname);


$year = isset($_GET['y']) ? $_GET['y'] : date('Y');  // Jahr und Monat der Inventur
$month = isset($_GET['m']) ? $_GET['m'] : date('n'); // wenn nicht gesetzt, auf heute setzen

// Jahr und Monat zwei Monate davor
$month2 = $month < 3 ? $month + 10 : $month - 2;
$year2 =  $month < 3 ? $year - 1 : $year;


$lists = mysqli_query($db, 'SELECT * FROM stocktakelists'); // Alle Inventurlisten (Schnapslager, Bierlager, ...)

?>

<html>
<head>
    <link href="hshstyle.css" rel="stylesheet" type="text/css" media="all">

	<title>Manhattan - Admin</title>
</head>
<body>

    <p class='title'> Inventurliste </p>
    <p class='subtitle'> des Manhattan </p>

    <h1> Bargeld </h1>

    <table>
        <tr>
            <td> Kasse </td>
            <td> </td>
        </tr>
        <tr>
            <td> Wechselgeld </td>
            <td> </td>
        </tr>
        <tr>
            <td> Tagesumsätze </td>
            <td> </td>
        </tr>
        <tr>
            <td> Löhne </td>
            <td> </td>
        </tr>
        <tr>
            <td> Geldbeutel Einkäufer </td>
            <td> </td>
        </tr>
        <tr>
            <td> unterer Tresor </td>
            <td> </td>
        </tr>
        <tr>
            <td> Bargeld Gesamt </td>
            <td> </td>
        </tr>
    </table>

    <h1> Inventar </h1>
    <table>

    <?php foreach ($lists as $list) { ?>
        <tr>
            <td> <?php echo $list['name']; ?> </td>
            <td> </td>
        </tr>
    <?php } ?>

    </table>

    <h1> Gesamtwert </h1>

    <table> <tr> <td> Manhattan Gesamt </td> <td> </td> </tr> </table>

    <hr/>

    <?php foreach ($lists as $list) { ?>
        <h1> <?php echo $list['name']; ?> </h1>

        <table>
        <?php
            // Alle Zutaten, die die letzten zwei Monate (+ diesen Monat) in dieser Liste standen, und alle Stocks von ihnen
            $stocks = mysqli_query($db,
            "SELECT ingredients.id, ingredients.name, a.id as stock, a.name as stockname, IFNULL(b.amount, 0) AS amount FROM
                (SELECT DISTINCT stock.ingredient, stock.id, stock.name FROM stock, stocktakes
                WHERE stocktakes.ingredient = stock.ingredient AND list=$list[id] AND date BETWEEN '$year2-$month2-01' AND '$year-$month-01') AS a
                LEFT JOIN
                (SELECT * FROM stocktakes WHERE list=$list[id] AND date='$year-$month-01') AS b
                ON (b.ingredient = a.ingredient AND b.stock = a.id)
                JOIN ingredients ON ingredients.id = a.ingredient");

            foreach ($stocks as $stock) { ?>

                <tr>
                    <td> <?php echo $stock['id'] . $stock['stock']; ?> </td>
                    <td> <?php echo $stock['stockname'] ?> </td>
                    <td> </td>
                </tr>

            <?php } ?>
        </table>

        <hr/>
    <?php } ?>

</body>
</html>
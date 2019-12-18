<?php
$page_title='finances';

$year = isset($_GET['y']) ? $_GET['y'] : date('Y');  // Jahr und Monat der Inventur
$month = isset($_GET['m']) ? $_GET['m'] : date('n'); // wenn nicht gesetzt, auf heute setzen

$activelist = isset($_GET['list']) ? $_GET['list'] : 0; // Geöffnete Inventurliste (0 = keine)

include('header.php');
include('finances_header.php');

if ($_POST) {
    if (!isset($_GET['action'])) { // keine action -> Daten speichern
        $sql = "REPLACE INTO stocktakes (date, list, ingredient, stock, amount) VALUES ";
        foreach ($_POST as $key=>$amount) {
            if ($key[0] == 'a' || $amount == 0) continue; // key = addstock
            $ingredient = substr($key, 0, 4);
            $stock = $key[4];
            $sql .= "('$year-$month-01', $activelist, '$ingredient', '$stock', $amount), ";
        }
        $sql = substr($sql, 0, -2); // letztes Komma entfernen
        mysqli_query($db, $sql);
    }
    else {
        if ($_GET['action'] == 'addstock' && isset($_POST['addstock']) && $_POST['addstock'] != '') { // Neuen Stock hinzufügen
            $ingredient = $_POST['addstock'];
            $response = mysqli_query($db, "SELECT id FROM stock WHERE ingredient='$ingredient'"); // Einfach irgendein Stock von der Zutat nehmen und eintragen
            $arbitraryStockID = mysqli_fetch_row($response)[0];
            mysqli_query($db, 
            "INSERT INTO stocktakes (date, list, ingredient, stock, amount) VALUES ('$year-$month-01', $activelist, '$ingredient', '$arbitraryStockID', 0)");
        }
    }
}
?>

	<div class="content">
        <div class='card'>
            <div class="card-title">
                <span class="arrow">
                    <a href="finances_stocktakes.php?y=<?php echo($month==1?$year-1:$year) ?>&m=<?php echo($month==1?'12':$month-1) ?>">
                        <i class='fa fa-chevron-left'></i>
                    </a>
                </span>
                <?php echo(' '.$months[$month-1].' '.$year.' '); ?>
                <span>
                    <a href="finances_stocktakes.php?y=<?php echo($month==12?$year+1:$year) ?>&m=<?php echo($month==12?'1':$month+1) ?>">
                        <i class='fa fa-chevron-right'></i>
                    </a>
                </span>
            </div>
            <div class='card-content'>
                <?php
                $value = mysqli_query($db, "SELECT sum(ingredientvalue) as v
                FROM (SELECT stocktakes.amount * (stock.amount * pricePerUnit + stock.deposit) as ingredientvalue
                    FROM stocktakes, stock, ingredients
                    WHERE stocktakes.date = '$year-$month-01' AND stocktakes.stock = stock.id AND stocktakes.ingredient = stock.ingredient
                        AND stock.ingredient = ingredients.id) tmp");
                echo(mysqli_fetch_row($value)[0]) . ' €'; ?>
            </div>
        </div>

        <form method="post">
            <?php
            $lists = mysqli_query($db, 'SELECT * FROM stocktakelists'); // Alle Inventurlisten (Schnapslager, Bierlager, ...)
            foreach ($lists as $list) {
            ?>
                <div class='card'>
                    <div class='card-title' onclick="location.href=
                    'finances_stocktakes.php?y=<?php echo $year; ?>&m=<?php echo $month; ?>&list=<?php echo $activelist == $list['id'] ? 0 : $list['id'];?>'" >
                        <?php echo $list['name']; ?>
                        <div style="text-align: right">
                            <?php
                            $value = mysqli_query($db,
                            "SELECT sum(ingredientvalue) as v
                                FROM (SELECT stocktakes.amount * (stock.amount * pricePerUnit + stock.deposit) as ingredientvalue
                                    FROM stocktakes, stock, ingredients
                                    WHERE stocktakes.list = $list[id] AND stocktakes.date = '$year-$month-01' AND stocktakes.stock = stock.id AND stocktakes.ingredient = stock.ingredient
                                        AND stock.ingredient = ingredients.id) tmp");
                            echo(mysqli_fetch_row($value)[0]) . ' €';?>
                        </div>
                    </div>
                    <?php if ($activelist == $list['id']) { ?>
                        <div class='card-content'>
                            <table>
                                <?php
                                // Jahr und Monat zwei Monate davor
                                $month2 = $month < 3 ? $month + 10 : $month - 2;
                                $year2 =  $month < 3 ? $year - 1 : $year;

                                // Alle Zutaten, die die letzten zwei Monate (+ diesen Monat) in dieser Liste standen, und alle Stocks von ihnen
                                $stocks = mysqli_query($db,
                                "SELECT ingredients.id, ingredients.name, a.id as stock, a.name as stockname, IFNULL(b.amount, 0) AS amount FROM
                                    (SELECT DISTINCT stock.ingredient, stock.id, stock.name FROM stock, stocktakes
                                    WHERE stocktakes.ingredient = stock.ingredient AND list=$list[id] AND date BETWEEN '$year2-$month2-01' AND '$year-$month-01') AS a
                                    LEFT JOIN
                                    (SELECT * FROM stocktakes WHERE list=$list[id] AND date='$year-$month-01') AS b
                                    ON (b.ingredient = a.ingredient AND b.stock = a.id)
                                    JOIN ingredients ON ingredients.id = a.ingredient");
                                $prev = ''; $alt = false;
                                foreach ($stocks as $stock) {
                                ?>
                                    <?php if ($prev != $stock['id']) { // neue Zeile mit "Überschrift" für die Zutat 
                                        $alt = !$alt;?>
                                        <tr class='stocktake-ingredient<?php if ($alt) echo '-alt'; ?>'>
                                            <td> <?php echo $stock['id']; ?> </td>
                                            <td colspan=2> <?php echo $stock['name']; ?> </td>
                                        </tr>
                                    <?php } ?>
                                    <tr class='stocktake-stock<?php if ($alt) echo '-alt'; ?>'>
                                        <td> <?php echo $stock['id'] . $stock['stock']; ?> </td>
                                        <td style="padding: 6px 4px; text-align: right"> <?php echo $stock['stockname']; ?> </td>
                                        <td>
                                            <input type="number" step="0.05"
                                                name="<?php echo $stock['id'] . $stock['stock'] ?>"
                                                value="<?php echo $stock['amount']; ?>">
                                        </td>
                                    </tr>
                                    <?php $prev = $stock['id'];
                                } ?>
                                <tr>
                                    <td colspan="2">
                                        <select name="addstock" size="1">
                                            <option value=""/>
                                            <?php // alle Stocks, die noch nicht schon drin sind und mindestens ein Stock haben
                                            $ingredients = mysqli_query($db, "SELECT id, name FROM ingredients WHERE NOT EXISTS
                                            (SELECT id FROM stocktakes WHERE stocktakes.ingredient = ingredients.id AND list = '$list[id]' AND date BETWEEN '$year2-$month2-01' AND '$year-$month-01')
                                            AND EXISTS (SELECT id FROM stock WHERE stock.ingredient = ingredients.id)");

                                            foreach ($ingredients as $ingredient) { ?>
                                                <option value="<?php echo $ingredient['id'];?>">
                                                    <?php echo $ingredient['id'] . " - " . $ingredient['name']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="submit" value="Hinzufügen" formaction="finances_stocktakes.php?y=<?php echo $year; ?>&m=<?php echo $month; ?>&list=<?php echo $activelist; ?>&action=addstock">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <input type="submit" value="Speichern" formaction='finances_stocktakes.php?y=<?php echo $year; ?>&m=<?php echo $month; ?>&list=<?php echo $activelist; ?>'>
        </form>
	</div>
</body>
</html>

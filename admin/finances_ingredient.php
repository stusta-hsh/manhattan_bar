<?php

/* ### Seite zum Bearbeiten einer Zutat ###
 * 
 * Parameter:
 * 
 * GET:
 * - id             ID des zu bearbeitenden Produkts. Wird diese nicht gesetzt, wird eine neue Zutat erstellt
 * 
 * POST:
 * - 
 */

$page_title='finances';

include('header.php');
include('finances_header.php');

if($_POST){ // es wurde irgendein Knopf gedrückt

    // alle Variablen laden
    $i_id = $_POST['id']; // Vorsicht, das ist der neue Wert, wenn er geändert wurde.
    $i_name = $_POST['name'];
    $i_unit = $_POST['unit'];
    $i_price = $_POST['price'];

    if (!isset($_GET['action'])) { // keine action -> Daten speichern
        if (isset($_GET['id'])) { // bestehenden Datensatz updaten
            mysqli_query($db, "UPDATE ingredients SET
            id = '$i_id', name='$i_name', unit='$i_unit', pricePerUnit='$i_price'
            WHERE id='" . $_GET['id'] . "';"); // hier nicht p_id (= der neue Wert für die ID, brauchen den alten)
        }
        else { // kein id im GET -> es wurde ein neuer Datensatz angelegt
            mysqli_query($db, "INSERT INTO ingredients (id, name, unit, pricePerUnit) VALUES
            ('$i_id', '$i_name', '$i_unit', '$i_price');");
            header("Location: finances_ingredient.php?id=$i_id");
            exit();
        }
        
        if (isset($_POST['stockid'])) { // es wurde etwas an dem Inventar geändert
            $s_oldid = $_POST['stockoldid'];
            $s_id = $_POST['stockid'];
            $s_name = $_POST['stockname'];
            $s_amount = $_POST['stockamount'];
            $s_deposit = $_POST['stockdeposit'];

            $sql = "REPLACE INTO stock (ingredient, id, name, amount, deposit) VALUES ('$i_id', '$s_id', '$s_name', $s_amount, $s_deposit);";
            if (!mysqli_query($db, $sql)) { // eine Fremdschlüsselüberprüfung schlug fehl -> Stock schon in einer Inventur und kann nicht gelöscht werden
                mysqil_query($db, 
                //echo (
                "UPDATE stock SET id='$s_id', name='$s_name', amount=$s_amount, deposit=$s_deposit WHERE ingredient='$i_id' AND id='$s_oldid';");
            }
        }
        
        if (!(isset($_GET['returntolist']) && $_GET['returntolist'] == 'false')) {
            header('Location: finances_ingredient_list.php');
            exit();
        }
    }
}

else // kein POST -> der Benutzer kommt von der Zutatenliste
if (isset($_GET['id'])) { // es wird eine bestehende Zutat bearbeitet. Ohne Angabe von id wird eine neue Zutat erstellt
    $result = mysqli_query($db, "SELECT * FROM ingredients WHERE id = '" . $_GET['id'] . "'");
    $ingredient = mysqli_fetch_row($result);

    $i_id = $ingredient[0];
    $i_name = $ingredient[1];
    $i_unit = $ingredient[2];
    $i_price = $ingredient[3];
}

if(isset($_GET['action'])) {
    $ingredient = $_GET['id'];
    if($_GET['action'] == 'removestock') { // Inventar löschen
        $stock = $_GET['stock'];
        mysqli_query($db, "DELETE FROM stock WHERE ingredient = '$ingredient' AND id = '$stock'");
    }
    else if ($_GET['action'] == 'delete') { // Produkt inaktiv setzen oder ganz löschen, wenn möglich
        if (mysqli_query($db, "DELETE FROM ingredients WHERE id='$ingredient'")) {       // probieren zu löschen
            mysqli_query($db, "DELETE FROM stock WHERE ingredient='$ingredient'");
        }
        else { // ein update constraint funkt dazwischen
            //mysqli_query($db, "UPDATE ingredients SET active=0 WHERE id='$ingredient'"); // Inaktiv setzen - geht nicht, hat keine active-Spalte
        }
        header('Location: finances_product_list.php'); // zurück auf die Produktliste
        exit();
    }
}

?>
	<div class="content">
        <form method='post' action=''>
            <h3><?php echo isset($_GET['id']) ? $i_name : "Neue Zutat"; // Ohne Angabe von id wird ein neues Produkt erstellt ?></h3>

            <div class="card">
                <label>ID
                    <input name="id" type="text" value="<?php echo $i_id; ?>">
                </label>
                <label>Name
                    <input name="name" type="text" value="<?php echo $i_name; ?>">
                </label>
                <label>Einheit
                    <select name="unit" value="<?php echo $i_unit; ?>">
                        <option value="Liter">Liter</option>
                        <option value="Kilogram">Kilogramm</option>
                        <option value="Piece">Stück</option>
                        <option value="Euro">Euro</option>
                    </select>
                </label>
                <label>Preis
                    <input name="price" type="number" step="0.0001" value="<?php echo $i_price; ?>">
                </label>
            </div>

            <div class="card">
                <div class="card-title">Inventar</div>
                <div class="card-content">
                <table>
                    <tr>
                        <th style="padding: 6px 4px; text-align: left">ID</th>
                        <th style="padding: 6px 4px; text-align: left">Name</th>
                        <th style="padding: 6px 4px; text-align: right">Menge</th>
                        <th style="padding: 6px 4px; text-align: right">Pfand</th>
                        <th/><th/>
                    </tr>

                    <?php
                    $stocks = mysqli_query($db, "SELECT * FROM stock WHERE ingredient = '$i_id'");

                    foreach ($stocks as $stock) {
                        // wenn das Inventar bearbeitet wird, steht seine Zeile einfach ganz unten. Könnte man umgehen, wenn man noch einen GET-Parameter "position" hinzufügt
                        if (isset($_GET['stock']) && $_GET['stock'] == $stock['id']) {
                            $editedstock = $stock; // für unten speichern
                        } else { ?>
                        <tr>
                            <td style="padding: 6px 4px; text-align: left"><?php echo $stock['id'] ?></td>
                            <td style="padding: 6px 4px; text-align: left"><?php echo $stock['name'] ?></td>
                            <td style="padding: 6px 4px; text-align: right"><?php echo $stock['amount'] ?></td>
                            <td style="padding: 6px 4px; text-align: right"><?php echo $stock['deposit'] ?></td>
                            <?php if (!isset($_GET['action'])) { ?>
                                <td> <button class="fa fa-edit" type="input"
                                    formaction="finances_ingredient.php<?php 
                                    if (isset($_GET['id'])) echo '?id=' . $i_id; ?>&action=editstock&stock=<?php echo $stock['id']; ?>">
                                </td>
                                <td> <button class="fa fa-trash" type="input"
                                    formaction="finances_ingredient.php<?php
                                    if (isset($_GET['id'])) echo '?id=' . $i_id; ?>&action=removestock&stock=<?php echo $stock['id']; ?>">
                                </td>
                            <?php } ?>
                        </tr>
                        <?php }
                    } 
                    
                    // wenn eine Zutat bearbeitet wird, wird die letzte Zeile mit vier Formularfeldern erstellt
                    if (isset($_GET['action']) && $_GET['action'] != 'removestock') { ?>
                        <tr>
                            <td><input name="stockid" type="text" maxlength="1" <?php if(isset($editedstock)) echo 'value="' . $editedstock['id'] . '"'; ?> />
                            <td><input name="stockname" type="text" <?php if(isset($editedstock)) echo 'value="' . $editedstock['name'] . '"'; ?> />
                            <td><input name="stockamount" type="number" step="0.01" value="<?php if(isset($editedstock)) echo $editedstock['amount']; else echo '0'; ?>" />
                            <td><input name="stockdeposit" type="number" step="0.01" value="<?php if(isset($editedstock)) echo $editedstock['deposit']; else echo '0'; ?>" />
                        </tr>
                        <input name="stockoldid" type="hidden" value="<?php if(isset($editedstock)) echo $editedstock['id']; ?>"/>
                    <?php } ?>
                </table>

                <?php
                // wenn die Zutat neu erstellt wird oder gerade ein Inventar bearbeitet wird, soll man erstmal speichern, bevor man noch eines hinzufügt
                if (isset($_GET['id']) && !(isset($_GET['action']) && $_GET['action'] != 'removestock')) { ?>
                    <input type="submit" value="neu" formaction="finances_ingredient.php<?php if (isset($_GET['id'])) echo '?id=' . $p_id; ?>&action=addstock"/>
                <?php } ?>
                </div>
            </div>

            <input type="submit" value="Speichern" formaction="finances_ingredient.php<?php
                if (isset($_GET['id'])) echo '?id=' . $i_id;
                if (isset($_GET['action'])) echo '&returntolist=false';
            ?>"/>

            <div class="card">
                <div class='card-title'>Zutat in:</div>
                <div class='card-content'>
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                        </tr>

                        <?php 
                        $products = mysqli_query($db, "SELECT products.id AS id, products.name AS name FROM products, recipies
                        WHERE recipies.product = products.id AND recipies.ingredient = '$i_id'");

                        foreach ($products as $product) { ?>
                            <tr onclick="location.href='finances_product.php?id=<?php echo $product['id'];?>'">
                                <td> <?php echo $product['id'] ?> </td>
                                <td> <?php echo $product['name'] ?> </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </form>
	</div>
</body>
</html>
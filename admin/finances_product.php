<?php

/* ### Seite zum Bearbeiten eines Produkts ###
 * 
 * Parameter:
 * 
 * GET:
 * - id             ID des zu bearbeitenden Produkts. Wird diese nicht gesetzt, wird ein neues Produkt erstellt
 * - action         mögliche Werte:
 *                  - addingredient     Eine neue Zutat wird hinzugefügt.
 *                  - editingredient    Eine bestehende Zutat wird bearbeitet (nur möglich mit GET-Parameter ingredient)
 *                  - removeingredient  Eine bestehende Zutat wird gelöscht (nur möglich mit GET-Parameter ingredient)
 *                  - delete            Das Produkt wird inaktiv gesetzt. Wenn keine Verweise in anderen Tabellen auf dieses Produkt sind,
 *                                      wird es und alle zugehörigen Einträge in der recipies-Tabelle gelöscht.
 *                  - restore           Das inaktive Produkt wird wiederhergestellt. (active auf true gesetzt)
 *                  Ist dieser Parameter gesetzt, werden die Formulardaten nicht gespeichert.
 *                  Lediglich bei 'removeingredient' wird die entsprechende Zeile aus der Tabelle 'recipies' gelöscht.
 * - ingredient     ID der Zutat, die bearbeitet oder gelöscht wird (s.o.)
 * - returntolist   true, wenn zur Produktlistenseite zurückgekehrt werden soll, false wenn nicht.
 * 
 * POST:
 * - die Formulardaten (id, category, name, amount, price, price_staff), die gespeichert werden sollen.
 *   Diese werden erst gespeichert, wenn der GET-Parameter 'action' nicht gesetzt ist. Ansonsten werden sie lediglich wieder in das Formular übernommen.
 * - Daten zum Hinzufügen/Bearbeiten einer Zutat (ingredientid, ingredientamount). Sind diese gesetzt, werden sie in die Tabelle recipies gespeichert.
 */

$page_title='finances';

include('header.php');
include('finances_header.php');

if($_POST){ // es wurde irgendein Knopf gedrückt

    // alle Variablen laden
    $p_id = $_POST['id']; // Vorsicht, das ist der neue Wert, wenn er geändert wurde.
    $p_category = $_POST['category'];
    $p_name = $_POST['name'];
    $p_amount = $_POST['amount'];
    $p_price = $_POST['price'];
    $p_price_staff = $_POST['price_staff'];
    
    if (!isset($_GET['action'])) { // keine action -> Daten speichern
        if (isset($_GET['id'])) { // bestehenden Datensatz updaten
            mysqli_query($db, "UPDATE products SET
            id = '$p_id', category='$p_category', name='$p_name', amount='$p_amount', price='$p_price', price_staff='$p_price_staff'
            WHERE id='" . $_GET['id'] . "';"); // hier nicht p_id (= der neue Wert für die ID, brauchen den alten)
        }
        else { // kein id im GET -> es wurde ein neuer Datensatz angelegt
            mysqli_query($db, "INSERT INTO products (id, category, name, amount, price, price_staff, active) VALUES
            ('$p_id', '$p_category', '$p_name', '$p_amount', '$p_price', '$p_price_staff', 1);");
            header("Location: finances_product.php?id=$p_id");
            exit();
        }

        if (isset($_POST['ingredientid'])) { // es wurde etwas an den Zutaten geändert
            $i_id = $_POST['ingredientid'];
            $i_amount = $_POST['ingredientamount'];

            $sql = "REPLACE INTO recipies (product, ingredient, amount) VALUES ('$p_id', '$i_id', $i_amount);";
            mysqli_query($db, $sql);
        }
        
        if (!(isset($_GET['returntolist']) && $_GET['returntolist'] == 'false')) {
            header('Location: finances_product_list.php');
            exit();
        }
    }
}

else // kein POST -> der Benutzer kommt von der Produktliste
if (isset($_GET['id'])) { // es wird ein bestehendes Produkt bearbeitet. Ohne Angabe von id wird ein neues Produkt erstellt
    $result = mysqli_query($db, "SELECT * FROM products WHERE id = '" . $_GET['id'] . "'");
    $product = mysqli_fetch_row($result);

    $p_id = $product[0];
    $p_category = $product[1];
    $p_name = $product[2];
    $p_amount = $product[3];
    $p_price = $product[4];
    $p_price_staff = $product[5];
    $p_active = $product[6];
}

if(isset($_GET['action'])) {
    $product = $_GET['id'];
    if($_GET['action'] == 'removeingredient') { // Zutat löschen
        $ingredient = $_GET['ingredient'];
        mysqli_query($db, "DELETE FROM recipies WHERE product='$product' AND ingredient = '$ingredient'");
    }
    else if ($_GET['action'] == 'delete') { // Produkt inaktiv setzen oder ganz löschen, wenn möglich
        if (mysqli_query($db, "DELETE FROM products WHERE id='$product'")) {       // probieren zu löschen
            mysqli_query($db, "DELETE FROM recipies WHERE product='$product'");
        }
        else { // ein update constraint funkt dazwischen
            mysqli_query($db, "UPDATE products SET active=0 WHERE id='$product'"); // Inaktiv setzen
        }
        header('Location: finances_product_list.php'); // zurück auf die Produktliste
        exit();
    }
    else if ($_GET['action'] == "restore") { // Produkt wieder aktiv setzen
        mysqli_query($db, "UPDATE products SET active=1 WHERE id='$product'");
        header('Location: finances_product_list.php'); // zurück auf die Produktliste
        exit();
    }
}

?>
	<div class="content">
        <form method='post' action=''>
            <h3><?php echo isset($_GET['id']) ? $p_name : "Neues Produkt"; // Ohne Angabe von id wird ein neues Produkt erstellt ?></h3>
            <div class="card">
                <label>ID
                    <input name="id" type="text" value="<?php echo $p_id ?>">
                </label>
                <label>Kategorie
                    <input name="category" type="text" value="<?php echo $p_category ?>">
                </label>
                <label>Name
                    <input name="name" type="text" value="<?php echo $p_name ?>">
                </label>
                <label>Menge
                    <input name="amount" type="text" value="<?php echo $p_amount ?>">
                </label>
                <label>Preis
                    <input name="price" type="number" step="0.01" value="<?php echo $p_price ?>">
                </label>
                <label>Mitarbeiterpreis
                    <input name="price_staff" type="number" step="0.01" value="<?php echo $p_price_staff ?>">
                </label>
            </div>

            <div class='card'>
                <div class='card-title'>Zutaten</div>
                <div class='card-content'>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Menge</th>
                        <th>Preis</th>
                        <th/><th/>
                    </tr>
                    <?php
                    
                    $ingredients = mysqli_query($db, "SELECT ingredients.id AS id, ingredients.name AS name,
                    recipies.amount AS amount, recipies.amount * ingredients.pricePerUnit AS price
                    FROM recipies, ingredients
                    WHERE recipies.product = '$p_id' AND recipies.ingredient = ingredients.id");
                    
                    foreach ($ingredients as $ingredient) { 
                        // wenn die Zutat bearbeitet wird, steht ihre Zeile einfach ganz unten. Könnte man umgehen, wenn man noch einen GET-Parameter "position" hinzufügt
                        if (isset($_GET['ingredient']) && $_GET['ingredient'] == $ingredient['id']) {
                            $editedingredient = $ingredient; // für unten speichern
                        } else { ?>
                            <tr>
                                <td> <?php echo $ingredient['id']; ?> </td>
                                <td> <?php echo $ingredient['name']; ?> </td>
                                <td> <?php echo $ingredient['amount']; ?> </td>
                                <td> <?php echo $ingredient['price']; ?> </td>
                                <td> <button class="fa fa-edit" formaction="finances_product.php<?php if (isset($_GET['id'])) echo '?id=' . $p_id; ?>&action=editingredient&ingredient=<?php echo $ingredient['id']; ?>"/>
                                </td>
                                <td> <button class="fa fa-trash"
                                    formaction="finances_product.php<?php if (isset($_GET['id'])) echo '?id=' . $p_id; ?>&action=removeingredient&ingredient=<?php echo $ingredient['id']; ?>"/>
                                </td>
                            </tr>
                        <?php }
                    }

                    // wenn eine Zutat bearbeitet wird, wird die letzte Zeile mit zwei Formularfeldern erstellt
                    if (isset($_GET['action']) && $_GET['action'] != 'removeingredient') { ?>
                        <tr>
                            <td colspan="2"> 
                                <select name="ingredientid" size="1">
                                    <?php if (isset($editedingredient)) { // wenn eine bestehende Zutat bearbeitet wird, diese vorselektieren ?>
                                        <option selected value="<?php echo $editedingredient['id']; ?>">
                                            <?php echo $editedingredient['id'] . " - " . $editedingredient['name']; ?>
                                        </option>
                                    <?php }
                                    // alle Zutaten, die noch nicht schon drin sind
                                    $ingredients = mysqli_query($db, "SELECT id, name FROM ingredients WHERE NOT EXISTS
                                    (SELECT ingredient FROM recipies WHERE ingredients.id = ingredient AND product = '$p_id')");

                                    foreach ($ingredients as $ingredient) { ?>

                                        <option value="<?php echo $ingredient['id'];?>">
                                            <?php echo $ingredient['id'] . " - " . $ingredient['name']; ?>
                                        </option>

                                    <?php } ?>
                                </select>
                            </td>
                            <td colspan="2">
                                <?php 
                                $result = mysqli_query($db, "SELECT amount FROM recipies WHERE product = '$p_id' AND ingredient = '$i_id'");
                                $amount = mysqli_fetch_row($result);
                                ?>
                                <input name="ingredientamount" type="number" step="0.01"
                                    <?php if (isset($editedingredient)) echo 'value="' . $editedingredient['amount'] . '"'; ?>
                                />
                            </td>
                        </tr>
                    <?php } ?>
                </table>

                <?php
                // wenn das Produkt neu erstellt wird oder gerade eine Zutat bearbeitet wird, soll man erstmal speichern, bevor man noch eine hinzufügt
                if (isset($_GET['id']) && !(isset($_GET['action']) && $_GET['action'] != 'removeingredient')) { ?>
                    <input type="submit" value="neue Zutat" formaction="finances_product.php<?php if (isset($_GET['id'])) echo '?id=' . $p_id; ?>&action=addingredient"/>
                <?php } ?>
                </div>
            </div>

            <input type="submit" value="Speichern" formaction="finances_product.php<?php
                if (isset($_GET['id'])) echo '?id=' . $p_id;
                if (isset($_GET['action'])) echo '&returntolist=false';
            ?>"/>
        </form>
        <?php if (isset($_GET['id'])) { // Löschen / wiederherstellen
            if ($p_active) { ?>
                <a href="finances_product.php?id=<?php echo $_GET['id']; ?>&action=delete">Produkt löschen</a>
            <?php }
            else { ?>
                <a href="finances_product.php?id=<?php echo $_GET['id']; ?>&action=restore">Produkt wiederherstellen</a>
            <?php }
        } ?>
	</div>
</body>
</html>
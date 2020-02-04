<?php
$page_title='finances';

include('header.php');

// Datenbankabfrage Liste aller Mitarbeiter
$sql = 'SELECT ingredients.id, ingredients.name FROM ingredients';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$ingredients = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

include('finances_header.php');

?>
	<div class="content">
		<h3>Zutaten</h3>
		<table class="ingredients_list">
            <tr>
                <th>ID</th>
                <th>Zutat</th>
                <th>Preis pro Einheit</th>
            </tr>
            <?php foreach($ingredients as $ingredient){ ?>
				<tr>
                    <td style="text-align: left"><?php echo($ingredient['id']);?></a></td>
                    <td style="text-align: left"><?php echo($ingredient['name']);?></a></td>
                    <td style="text-align: left"><?php  ?></a></td>
				</tr>
			<?php } ?>
        </table>
	</div>
</body>
</html>

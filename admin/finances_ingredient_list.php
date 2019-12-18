<?php
$page_title='finances';

include('header.php');
include('finances_header.php');

$ingredients = mysqli_query($db,
'SELECT ingredients.id, ingredients.name, ROUND(ingredients.pricePerUnit, 2) AS price,
    (EXISTS (SELECT products.id FROM recipies, products WHERE product=products.id AND ingredient=ingredients.id AND products.active = 1) ) AS active
FROM ingredients');

?>
	<div class="content">
		<h3>Zutaten</h3>
		<table class="ingredients_list">
            <tr>
                <th style="text-align: left">ID</th>
                <th style="text-align: left">Zutat</th>
                <th style="text-align: right">Preis pro Einheit</th>
            </tr>
            <?php foreach($ingredients as $ingredient){
                if ($ingredient['active'] == 1) { ?>
                    <tr onclick="location.href='finances_ingredient.php?id=<?php echo $ingredient['id']; ?>'">
                        <td style="padding: 6px 4px; text-align: left"><?php echo($ingredient['id']); ?></a></td>
                        <td style="padding: 6px 4px; text-align: left"><?php echo($ingredient['name']); ?></a></td>
                        <td style="padding: 6px 4px; text-align: right"><?php echo($ingredient['price']); ?></a></td>
                    </tr>
                <?php }
            } 
            foreach($ingredients as $ingredient){
                if ($ingredient['active'] == 0) { ?>
                    <tr style="background-color: #ddd" onclick="location.href='finances_ingredient.php?id=<?php echo $ingredient['id'];?>'">
                        <td style="padding: 6px 4px; text-align: left"><?php echo($ingredient['id']); ?></a></td>
                        <td style="padding: 6px 4px; text-align: left"><?php echo($ingredient['name']); ?></a></td>
                        <td style="padding: 6px 4px; text-align: right"><?php echo($ingredient['price']); ?></a></td>
                    </tr>
                <?php }
            } 
            ?>
        </table>

        <a href="finances_ingredient.php"><button> Neue Zutat </button></a>
	</div>
</body>
</html>

<?php
$page_title='finances';

include('header.php');
include('finances_header.php');

$products = mysqli_query($db,
'SELECT products.id, products.name, products.price, products.active,
    (SELECT ROUND(products.price - sum(ingredients.pricePerUnit * recipies.amount), 2)
        FROM ingredients, recipies
        WHERE recipies.ingredient = ingredients.id AND recipies.product = products.id)
    AS marge
FROM products');

?>
	<div class="content">
		<h3>Produkte</h3>
		<table>
            <tr>
                <th style="text-align: left">ID</th>
                <th style="text-align: left">Produkt</th>
                <th style="text-align: right">Preis</th>
                <th style="text-align: right" colspan="2">Marge</th>
            </tr>
            <?php foreach($products as $product){ 
                if ($product['active'] == 1) { ?>
                <tr onclick="location.href='finances_product.php?id=<?php echo $product['id']; ?>'">
                    <td style="text-align: left; padding: 6px 4px"><?php echo($product['id']); ?></td>
                    <td style="text-align: left; padding: 6px 4px"><?php echo($product['name']); ?></td>
                    <td style="text-align: right; padding: 6px 4px"><?php echo($product['price']); ?></td>
                    <td style="text-align: right; padding: 6px 4px"><?php echo($product['marge']); ?></td>
                    <td style="text-align: right; padding: 6px 4px"><?php echo((int)($product['marge'] * 100 / $product['price']) . '%'); ?></td>
                </tr>
            <?php }
            } 
            foreach($products as $product){ 
                if ($product['active'] == 0) { ?>
                <tr style="background-color: #ddd" onclick="location.href='finances_product.php?id=<?php echo $product['id']; ?>'">
                    <td style="text-align: left; padding: 6px 4px"><?php echo($product['id']); ?></td>
                    <td style="text-align: left; padding: 6px 4px"><?php echo($product['name']); ?></td>
                    <td style="text-align: right; padding: 6px 4px"><?php echo($product['price']); ?></td>
                    <td style="text-align: right; padding: 6px 4px"><?php echo($product['marge']); ?></td>
                    <td style="text-align: right; padding: 6px 4px"><?php echo((int)($product['marge'] * 100 / $product['price']) . '%'); ?></td>
                </tr>
            <?php }
            }?>
        </table>

        <a href="finances_product.php"><button> Neues Produkt </button></a>
	</div>
</body>
</html>

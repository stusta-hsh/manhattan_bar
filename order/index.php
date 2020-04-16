<!DOCTYPE html>
<?php

// if (date('w') != 4 || (date('H') >= 18 && date('i') > 35)) { exit("Leider zu spät."); } // Ab 18:35 nicht mehr anzeigen

include('../sql_config.php');
$db = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_dbname);
if(!$db) exit("Database connection error: ".mysqli_connect_error());

// Datenbankabfrage Häuser
$sql = 'SELECT id, name, alias FROM houses ORDER BY no ASC';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$houses = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

if ($_POST) {

	$name = ''; $house = 0; $room = ''; $phone = ''; $comment = '';

	foreach ($_POST as $key=>$value) {
		switch ($key) {
			case 'name': $name = $value; break;
			case 'house': $house = $value; break;
			case 'room': $room = $value; break;
			case 'phone': $phone = $value; break;
			case 'comment': $comment = $value; break;
			default: break; // die Bestellpositionen erstmal weglassen
		}
	}

	$sql_query = mysqli_prepare($db, "INSERT INTO orders (name, house, room, phone, comment) VALUES (?, ?, ?, ?, ?)");
	mysqli_stmt_bind_param($sql_query, 'sisss', $name, $house, $room, $phone, $comment);

	if (mysqli_stmt_execute($sql_query))
	{
		$id = mysqli_insert_id($db);
		$sql = "INSERT INTO menu_positions (order_id, position, patty, cheese, salad, tomato, onion, sauce, friedonions, pickles, bacon, camembert, beilage, bier) VALUES ";
		for ($i = 1; $i <= 10; $i++) {
			$positionexists = false;
			$patty = 0; $cheese = 0; $salad = 0; $tomato = 0; $onion = 0; $sauce = 0; $friedonions = 0; $pickles = 0; $bacon = 0; $camembert = 0; $side = 0; $bier = 0;
			foreach ($_POST as $key=>$value) {
				list($position, $a) = explode('-', $key);
				if ($position == $i) {
					$positionexists = true;
					switch ($a) {
						case 'patty': $patty = $value; break;
						case 'side': $side = $value; break;
						case 'bier': $bier = $value; break;
						case 'c': $cheese = $value; break;
						case 's': $salad = $value; break;
						case 't': $tomato = $value; break;
						case 'o': $onion = $value; break;
						case 'x': $sauce = $value; break;
						case 'f': $friedonions = $value; break;
						case 'p': $pickles = $value; break;
						case 'b': $bacon = $value; break;
						case 'y': $camembert = $value; break;
					}
				}
			}
			if ($positionexists) { $sql .= "($id, $i, $patty, $cheese, $salad, $tomato, $onion, $sauce, $friedonions, $pickles, $bacon, $camembert, $side, $bier), "; }
		}
		$sql = substr($sql, 0, -2); // Das letzte Komma entfernen
		mysqli_query($db, $sql);

		header("Location: complete.php?id=$id"); exit;
	}
	mysqli_stmt_close($sql_query);
}
?>


<html>
<head>
	<link href="style.css" rel="stylesheet" type="text/css" media="all">
	<link rel="stylesheet" href="../fonts/fork-awesome/css/fork-awesome.min.css">

	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">

	<title> Manhattan - Bestellung </title>
</head>

<body class="content">
	<div class="logo-background">
		<div class='logo'>
			<a href='index.php'><img src='../images/logo.png' alt='Manhattan' width='100%'></a>
		</div>
	</div>
	<div class="content">
	<form method='post' class='order-form'>

		<p>Auch in Zeiten der häuslichen Isolation wollen wir euch weiter jeden Donnerstag mit leckeren Burgern versorgen!</p>
		<p>
			Hier könnt ihr bis 18:30 Uhr eure Bestellung abgeben. Ab 19:00 Uhr bereiten wir die Burger frisch zu und bringen sie euch bis an die Zimmertür. Bitte habt Verständnis, dass wir für das Liefern einen kleinen Betrag verlangen. Getränke werden gekühlt in Flaschen geliefert.
		</p>
		<p>
			Die Bezahlung erfolgt <b>ausschließlich</b> kontaktlos und im Voraus via PayPal. Nach Abschluss der Bestellung seht ihr den zu zahlenden Betrag und einen PayPal-Link. Wählt bei der Bezahlung bitte unbedingt "Geld an Freunde und Familie senden", damit keine PayPal-Gebühr anfällt.
		</p>
		<p>
			<b>Nur Bestellungen, die bis 18:30 Uhr bezahlt sind, werden auch zubereitet und ausgeliefert!</b>
		</p>

		<!--
		<p> Auch in der Coronazeit wollen wir vom Manhattan euch weiter mit Burgern versorgen. </p>
		<p>
			Hier könnt ihr <em>bis 19 Uhr</em> eure Bestellung abgeben. Danach bereiten wir eure Burger frisch zu und bringen sie euch bis an die Zimmertür.
			Bitte habt Verständnis, dass wir für das Liefern einen kleinen Betrag verlangen. Getränke werden in Flaschen geliefert.
		</p>
		<p>
			Die Bezahlung erfolgt <b>ausschließlich</b> kontaktlos über PayPal.
			Nach Abschluss der Bestellung seht ihr nochmal euren Betrag und einen PayPal-Link, über den ihr den angegebenen Betrag an uns sendet.
			Gebt bei der Bezahlung bitte Freunde & Familie an (wir vom Manhattan sind nicht nur eure Nachbarn, sondern auch eure Freunde, wenn nicht sogar eine zweite Familie &#x1F609;), damit wir keine Gebühren zahlen müssen.
			Denkt daran, eure Bestellung vor 19 Uhr zu bezahlen, da wir sie sonst nicht bearbeiten können.
		</p>
		<p> Eine Barzahlung können wir leider nicht annehmen. </p>
		-->

		<div class='card'>
			<div class='card-title'>Deine Bestellung</div>
			<div class='card-content' style="background-color: #f5f5f5">
				<div id="order_list">
					<div id="position_1" class="order-position">
						<!--<div id="order_position_title" class="order-position-title">#</div>-->
						<div class="order-form-card-row">
							<label class="flex-300">Burger
								<select name="1-patty" id="burger_1" onchange="calculate_price(); update_ingredients(this.id);"">
									<option value='0'>Hamburger (4,00€)</option>
									<!--<option value='0'>Cheeseburger (4,00€)</option>-->
									<option value='1'>Beyond Meat&#8482;-Burger (5,50€)</option>
									<option value='2'>Double-Burger (5,50€)</option>
								</select><br>
							</label>
							<!-- <p class='fa fa-trash' onclick="delete_click(this)"/> -->
						</div>
						<p class="hint" id="ingredients_burger_1" style="text-align: right; margin-top: -5px;">mit Beef-Patty, Salat, Tomaten, Zwiebeln und Burgersauce</p>
						<!--<div class="order-form-card-row">
                            <label><input type='checkbox' value="1" name="1-c" id=checkCheese_1>Käse</label>
                            <label><input type='checkbox' value="1" checked name="1-s" id=checkSalad_1>Salat</label>
                            <label><input type='checkbox' value="1" checked name="1-t" id=checkTomato_1>Tomate</label>
                            <label><input type='checkbox' value="1" checked name="1-o" id=checkOnions_1>Zwiebeln</label>
                            <label><input type='checkbox' value="1" checked name="1-x" id=checkSauce_1>Sauce</label>
    					</div>-->
						<div class="order-form-card-row">
							<label><input type='checkbox' value="1" name="1-c" id=checkCheese_1>Käse</label>
							<input type='hidden' value="1" checked name="1-s" id=checkSalad_1>
							<input type='hidden' value="1" checked name="1-t" id=checkTomato_1>
							<input type='hidden' value="1" checked name="1-o" id=checkOnions_1>
							<input type='hidden' value="1" checked name="1-x" id=checkSauce_1>
							<label><input type='checkbox' value="1" name="1-f" id=checkRoastedOnions_1>Röstzwiebeln</label>
							<label><input type='checkbox' value="1" name="1-p" id=checkPickle_1>Essiggurke</label>
							<label><input type='checkbox' value="1" name="1-b" id=checkBacon_1  class=check-Bacon onclick="calculate_price()">Bacon (+0,50€)</label>
							<label><input type='checkbox' value="1" name="1-y" id=checkCamembert_1 class=check-Camembert onclick="calculate_price()">Camembert (+0,50€)</label>
						</div>
						<div class="order-form-card-row">
							<label class="flex-200">Beilage (+1,40€)
								<select name="1-side" onchange="calculate_price()">
									<option value="1">Pommes</option>
									<option value="2">Wedges</option>
									<option value="0">keine Beilage</option>
								</select>
							</label>
							<label class="flex-200">Getränk (+1,40€)
								<select name="1-bier" onchange="calculate_price()">
									<option value="1">Augustiner Hell</option>
									<option value="2">Tegernseer Spezial</option>
									<option value="3">Schneider Weisse TAP7</option>
									<option value="4">Schneider Weisse TAP3 (alkoholfrei)</option>
									<option value="5">Kuchlbauer Alte Liebe</option>
									<option value="6">Weihenstephaner Natur Radler</option>
									<option value="7">Paulaner Spezi</option>
									<option value="8">Almdudler</option>
									<option value="9">Club Mate</option>
									<option value="0">kein Getränk</option>
								</select>
							</label>
						</div>
						<div class="order-position-price">
							<a type=number step="0.01" id='price_order_position_1' class=price-order-position>6.80</a> €
						</div>
					</div>
				</div>
				<div class="add-order-position-button" onclick="add(event)">
					<i class="fa fa-plus-circle" aria-hidden="true"></i> Menü hinzufügen
				</div>
				
				<div id="order-total" class="order-total">
					Bestellung: <a type = number id='price_order'>6.80</a> €<br>
					Lieferung: + <a id='price_delivery'>0.50</a> €
					<hr>
					Gesamt: <a id="price_total">7.30</a> €
				</div>
				
			</div>
		</div>

		<div class='card order-form-cad'>
			<div class='card-title'> Deine Daten </div>
			<div class='card-content'>
				<div class="order-form-card-row">
					<label class="flex-300">Name *
						<input id='fname' type='text' name='name' onchange='enableSubmit(this)'/>
					</label>
					<br>
				</div>
				<div class="order-form-card-row">
					<label class="flex-200">Haus
						<select id='fhouse' name="house" onchange='calculate_price()'>
						<?php foreach($houses as $house){ if($house['id'] != 2){?>
								<option value='<?php echo $house['id'] ?>' <?php if($house['name']=='HSH') echo 'selected' ?>><?php echo $house['name']; if(!empty($house['alias']))echo(' ('.$house['alias'].')'); ?></option>
							<?php }} ?>
						</select><br>
					</label>

					<label class="flex-100">Zimmer / WG *
						<input id='froom' type='text' name='room' onchange='enableSubmit(this)'/>
					</label>
				</div>

				<label>Handynummer (optional, bei Rückfragen oder Lieferung)
					<input id='fphone' type='tel' name='phone'/>
				</label>

				<br/>
				<label>Anmerkungen
					<textarea rows="4" id='fcomment' name="comment" maxlength="300" placeholder="Hinweise zur Bestellung oder Lieferung"></textarea>
				</label>

				<p class='hint'> Innerhalb des HSH kostet die Lieferung 0,50 €, in die übrige Studentenstadt 1 €. </p>
			</div>
		</div>


		<input type='checkbox' id='paypal_check' onchange='enableSubmit(this)'/>
		<label for='paypal_check'> Ich habe ein PayPal-Konto * </label>
		<br>
		<input id='submit_button' type='submit' value='Bestellung absenden' disabled="disabled" onmousedown='submit_click()'>

		<p class='hint'>
			Mit Abgabe deiner Bestellung gibst du dein Einverständnis, dass wir im Manhattan deine Bestellung unter bestmöglicher Berücksichtigung der Hygienemaßnahmen zubereiten und dir an die Tür bringen. Weder wir noch das Studentenwerk können haftbar gemacht werden.<br>
			Außerdem bist du damit einverstanden, dass die von dir angegebenen Daten zum Zweck der Essenszubereitung und -auslieferung von uns gespeichert und verwendet werden.
		</p>
	</form>
	</div>
</body>

<script>
	var order_total = 0.00;
	var product_count = 1;

	function enableSubmit(e) { // Bei unvollständigen Angaben Submit-Button deaktivieren
		document.getElementById('submit_button').disabled =
			!(document.getElementById('fname').value != "" &&
			document.getElementById('froom').value != "" &&
		 	document.getElementById('paypal_check').checked);
	}

	function submit_click() { // Anzeigen, warum man nicht submitten kann
		if (document.getElementById('submit_button').disabled) {
			var hint = document.getElementById('hint');
			if (document.getElementById('fname').value == "") { hint.innerHTML = 'Bitte gib deinen Namen ein.' }
			else if (document.getElementById('froom').value == "") { hint.innerHTML = 'Bitte gib deine Zimmernummer ein.' }
			else if (document.getElementById('fname').value == "") { hint.innerHTML = 'Bitte bestätige, dass du Paypal hast.' }
		}
	}

	function update_ingredients(id){
		var ingredients = document.getElementById("ingredients_"+id);
		var burger = document.getElementById(id).value;
		if (burger == 0) {
			ingredients.innerHTML = "mit Beef-Patty, Salat, Tomaten, Zwiebeln und Burgersauce";
		} else if (burger == 1) {
			ingredients.innerHTML = "mit veganem Patty, Salat, Tomaten, Zwiebeln und Burgersauce";
		} else {
			ingredients.innerHTML = "mit doppeltem Beef-Patty, Salat, Tomaten, Zwiebeln und Burgersauce";
		}
	}

	function add(e) {
		e.preventDefault();
		if (product_count < 5) {
			//var order_table = document.getElementById('order_table');
			//var product_select = document.getElementById('product_select');

			var order_list = document.getElementById('order_list');
			var first_position = document.getElementById('position_1');
			var new_position = first_position.cloneNode(true);
			new_position.id = "position_" + ++product_count;
			order_list.appendChild(new_position);

            //var childArr = Array.prototype.slice.call(new_position.childNodes);

			new_position.innerHTML = first_position.innerHTML.replace(/name=\"1/g, "name=\"" + product_count);
			new_position.innerHTML = first_position.innerHTML.replace(/id=\"ingredients_burger_1/, "id=\"ingredients_burger_" + product_count);

            var selectElements = new_position.getElementsByTagName('select');
            var old = 0;
    		for (i = 0; i < selectElements.length; i++) {
				if(String(selectElements[i].id).includes("burger")) {
					var old = selectElements[i].id;
					selectElements[i].id = old.substring(0, old.length - 1) + "" + product_count;
				}
			}

			var checkElements = new_position.getElementsByTagName('input');
			//window.alert(checkElements[0].id);
			for (i = 0; i < checkElements.length; i++) {
				if(String(checkElements[i].id).includes("check")) {
					var old = checkElements[i].id;
					checkElements[i].id = old.substring(0, old.length - 1) + "" + product_count;
				}
			}

            var price_order_position = document.getElementById('price_order_position_1');
            var old = price_order_position.id;
            price_order_position.id = old.substring(0, old.length - 1) + "" + product_count;
			update_ingredients("burger_" + product_count);

			//var newrow = order_table.insertRow(++product_count);
			//var c0 = newrow.insertCell(0);
			//var c1 = newrow.insertCell(1);

			//c0.innerHTML = "<input name=\'" + product_count + "-" + product_select.value +"\' type=\'number\' min=\'0\' step=\'1\' value=\'1\'/>"
			//c1.innerHTML = product_select.selectedOptions[0].text;
		} else {
            window.alert("Es können maximal 5 Menüs auf einmal bestellt werden.");
        }
        calculate_price();

	}

	function calculate_price(){
        var price_order_position = 0;
		var price_order = 0;
        
		var price_delivery = 0.5;

        var burgers = [];
        var supplements = [];
        var drinks = [];
        var bacons = document.getElementsByClassName('check-Bacon');
        var camemberts = document.getElementsByClassName('check-Camembert');
        var selectElements = document.getElementsByTagName('select');

    		for (i = 0; i < selectElements.length; i++) {
				if(String(selectElements[i].id).includes("burger")) {
                    burgers[burgers.length] = String(selectElements[i].options[selectElements[i].selectedIndex].innerHTML);
                }
                else if(String(selectElements[i].name).includes("side")) {
                    supplements[supplements.length] = String(selectElements[i].options[selectElements[i].selectedIndex].innerHTML);
                }
                else if(String(selectElements[i].name).includes("bier")) {
                    drinks[drinks.length] = String(selectElements[i].options[selectElements[i].selectedIndex].innerHTML);
                }
            }

        var prices_order_positions = document.getElementsByClassName('price-order-position');
        for(i=0; i<prices_order_positions.length; i++){
            price_order_position = 0;
            if(burgers[i].includes('Cheeseburger') || burgers[i].includes('Hamburger')){
                price_order_position += 4;

            }
            else if(burgers[i].includes('Beyond Meat') || burgers[i].includes('Double-Burger')){
                price_order_position += 5.5;
            }

            if(! supplements[i].includes('keine Beilage')) price_order_position += 1.4;
            if(! drinks[i].includes('kein Getränk')) price_order_position += 1.4;

            if(bacons[i].checked) price_order_position += 0.5;
            if(camemberts[i].checked) price_order_position += 0.5;


            prices_order_positions[i].innerHTML = price_order_position.toFixed(2);
            price_order += price_order_position;
        }

        if(document.getElementById('fhouse').value == 1) price_delivery = 0.5;
        else price_delivery = 1;


        document.getElementById('price_delivery').innerHTML = price_delivery.toFixed(2);
		document.getElementById('price_total').innerHTML = (price_order + price_delivery).toFixed(2);
        document.getElementById('price_order').innerHTML = price_order.toFixed(2);

		//var form_fields = document.querySelectorAll('*[name]');
		//for (field of form_fields) {
		//	if (field.name == 'house') {
		//		if (field.value == 1) { price_delivery = 0.5; } else { price_delivery = 1; }
		//	}
		//}

		//
	}
</script>

</html>

<?php

include('../sql_config.php');
$db = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_dbname);
if(!$db) exit("Database connection error: ".mysqli_connect_error());

// Datenbankabfrage Settings
$sql = 'SELECT title, value FROM settings';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$results = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

$settings = [];
foreach($results as $result){
	$settings[$result['title']] = $result['value'];
}

// Datenbankabfrage Häuser
$sql = 'SELECT id, name, alias FROM houses ORDER BY no ASC';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$houses = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

if ($_POST) {
	//print_r($_POST);
	$name = ''; $house = 0; $room = ''; $phone = ''; $slot = ''; $comment = '';

	foreach ($_POST as $key=>$value) {
		switch ($key) {
			case 'name': $name = $value; break;
			case 'house': $house = $value; break;
			case 'room': $room = intval($value) != 0 && !strpos($value, "/") ? sprintf("%04d", $value) : $value; break;
			case 'phone': $phone = $value; break;
			case 'timeslot': $slot = $value; break;
			case 'comment': $comment = $value; break;
			default: break; // die Bestellpositionen erstmal weglassen
		}
	}


	$sql_query = mysqli_prepare($db, "INSERT INTO orders (name, house, room, phone, slot, comment) VALUES (?, ?, ?, ?, ?, ?)");
	mysqli_stmt_bind_param($sql_query, 'sissis', $name, $house, $room, $phone, $slot, $comment);

	if (mysqli_connect_errno()) { echo("Connect failed: " . mysqli_connect_error()); }

	if (mysqli_stmt_execute($sql_query))
	{
		$id = mysqli_insert_id($db);
		$sql = "INSERT INTO menu_positions (order_id, position, patty, cheese, salad, tomato, onion, sauce, friedonions, pickles, bacon, camembert, beilage, dip_1, dip_2, bier) VALUES ";
		for ($i = 1; $i <= $settings['order_max_position']; $i++) {
			$positionexists = false;
			$patty = 0; $cheese = 0; $salad = 0; $tomato = 0; $onion = 0; $sauce = 0; $friedonions = 0; $pickles = 0; $bacon = 0; $camembert = 0; $side = 0; $dip_1 = 0; $dip_2 = 0; $bier = 0;
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
						case 'dip_1': $dip_1 = $value; break;
						case 'dip_2': $dip_2 = $value; break;
					}
				}
			}
			if ($positionexists) { $sql .= "($id, $i, $patty, $cheese, $salad, $tomato, $onion, $sauce, $friedonions, $pickles, $bacon, $camembert, $side, $dip_1, $dip_2, $bier), "; }
		}
		$sql = substr($sql, 0, -2); // Das letzte Komma entfernen
		mysqli_query($db, $sql);

		if ($patty = 1) { header("Location: survey.php?id=$id"); exit; }
		else { header("Location: complete.php?id=$id"); exit; }
	}
	//echo mysqli_stmt_error($sql_query);
	mysqli_stmt_close($sql_query);
}

if (date('w') != $settings['order_weekday'] || (date('H:i') < date('H:i', strtotime($settings['order_opentime']))) || (date('H:i') >= date('H:i', strtotime($settings['order_closetime'])))) { exit("Leider zu spät."); } // Ab 17:00 nicht mehr anzeigen
?>

<!DOCTYPE html>
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
			Hier könnt ihr bis 17:00 Uhr eure Bestellung abgeben. Dann bereiten wir die Burger frisch zu und bringen sie euch an die Zimmertür. Für die Lieferung verlangen wir einen kleinen Betrag. Getränke werden gekühlt in Flaschen geliefert.
		</p>
		<p>
			Die Bezahlung erfolgt <b>ausschließlich</b> kontaktlos und im Voraus via PayPal. Nach Abschluss der Bestellung seht ihr den zu zahlenden Betrag und einen PayPal-Link. Wählt bei der Bezahlung unbedingt "Geld an Freunde und Familie senden", damit keine PayPal-Gebühr anfällt.
		</p>
		<p>
			<b>Nur Bestellungen, die bis 17:00 Uhr bezahlt sind, werden auch zubereitet und ausgeliefert! Da die Anzahl der Bestellungen begrenzt ist, zahlt bitte innerhalb von 15 Minuten nach Bestellabgabe.</b>
		</p>

		<div class='card'>
			<div class='card-title'>Deine Bestellung</div>
			<div class='card-content' style="background-color: #f5f5f5">
				<div id="order_list">
					<div id="position_1" class="order-position">
						<!--<div id="order_position_title" class="order-position-title">#</div>-->
						<div class="order-form-card-row">
							<label class="flex-300">Burger
								<select name="1-patty" id="burger_1" onchange="calculate_price(); update_ingredients(this.id);">
									<option value='0'>Hamburger (4,00€)</option>
									<!--<option value='0'>Cheeseburger (4,00€)</option>-->
									<option value='1'>Beyond Meat&#8482;-Burger (6,00€)</option>
									<option value='2'>Double-Burger (5,50€)</option>
								</select><br>
							</label>
							<!-- <p class='fa fa-trash' onclick="delete_click(this)"/> -->
						</div>
						<p class="hint ingredient" id="ingredients_burger_1">mit Beef-Patty, Salat, Tomaten, Zwiebeln und Burgersauce (nicht vegan)</p>
						<div class="order-form-card-row" style="justify-content: start">
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
									<option value="1">Pommes frites</option>
									<option value="2">Potato Wedges</option>
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
									<!-- <option value="8">Almdudler</option> -->
									<option value="9">Club Mate</option>
									<option value="10">Bulmers Cider (2.50€)</option>
									<option value="11">Bulmers Cider Pear (2.50€)</option>
									<option value="0">kein Getränk</option>
								</select>
							</label>
						</div>
						<div class="order-form-card-row" style="justify-content: start">
							<label><input type='checkbox' value="1" name="1-dip_1" id=checkDip1_1 class=check-Ketchup onclick="calculate_price()">Ketchup (+0,10 €)</label>
							<label><input type='checkbox' value="1" name="1-dip_2" id=checkDip2_1 class=check-Mayo onclick="calculate_price()">Mayonnaise (+0,10 €)</label>
						</div>
						<div class="order-position-price">
							<a type=number step="0.01" id='price_order_position_1' class=price-order-position>6.80</a> €
						</div>
					</div>
				</div>
				<div class="add-order-position-button" onclick="add(event)" id="add-position">
					<i class="fa fa-plus-circle" aria-hidden="true"></i> Menü hinzufügen
				</div>

				<div id="order-total" class="order-total">
					Bestellung: <a type = number id='price_order'>6.80</a> €<br>
					Lieferung: + <a id='price_delivery'>0.50</a> €
					<hr>
					Gesamt: <a id="price_total">7.30</a> €
				</div>
				<p class='hint'> Innerhalb des HSH kostet die Lieferung 0,50 €, in die übrige Studentenstadt 1 €. </p>

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
					<label class="flex-200">Haus *
						<select id='fhouse' name="house" onchange='calculate_price()'>
						<?php foreach($houses as $house){ if($house['id'] != 2){?>
								<option value='<?php echo $house['id'] ?>' <?php if($house['id']=='1') echo 'selected' ?>><?php echo $house['name']; if(!empty($house['alias']))echo(' ('.$house['alias'].')'); ?></option>
							<?php }} ?>
						</select>
					</label>

					<label class="flex-100">Zimmer / WG *
						<input id='froom' type='text' name='room' onchange='enableSubmit(this)'/>
					</label>
				</div>

				<div class="order-form-card-row">
					<label class="flex-200">Handynummer (optional)
						<input id='fphone' type='tel' name='phone'/>
					</label>
					<label class="flex-100">Lieferzeitraum *
						<select name='timeslot' id="timeslot" onchange='enableSubmit(this)'>
							<option value="">Bitte wählen</option>
							<option <?php if(mysqli_fetch_row(mysqli_query($db, "SELECT (COUNT(slot) < ".$settings['order_max_slot'].") as free FROM orders WHERE deleted = 0 AND DATE(date) = '" . date('Y-m-d') . "' AND slot = 0"))[0] == 0) echo 'disabled';?> value="0">17:00 - 18:30</option>
							<option <?php if(mysqli_fetch_row(mysqli_query($db, "SELECT (COUNT(slot) < ".$settings['order_max_slot'].") as free FROM orders WHERE deleted = 0 AND DATE(date) = '" . date('Y-m-d') . "' AND slot = 1"))[0] == 0) echo 'disabled';?> value="1">18:30 - 20:00</option>
						</select>
					</label>
				</div>
				<label>Anmerkungen
					<textarea rows="4" id='fcomment' name="comment" maxlength="150" placeholder="Hinweise zur Bestellung oder Lieferung"></textarea>
				</label>
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
	var position_count = 1;

	function enableSubmit(e) { // Bei unvollständigen Angaben Submit-Button deaktivieren
		document.getElementById('submit_button').disabled =
			!(document.getElementById('fname').value != "" &&
			document.getElementById('froom').value != "" &&
			document.getElementById('timeslot').value != "" &&
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
			ingredients.innerHTML = "mit Beef-Patty, Salat, Tomaten, Zwiebeln und Burgersauce (nicht vegan)";
		} else if (burger == 1) {
			ingredients.innerHTML = "mit veganem Patty, Salat, Tomaten, Zwiebeln und Burgersauce (nicht vegan)";
		} else {
			ingredients.innerHTML = "mit doppeltem Beef-Patty, Salat, Tomaten, Zwiebeln und Burgersauce (nicht vegan)";
		}
	}

	function add(e) {
		e.preventDefault();
		if (position_count < <?php echo $settings["order_max_position"] ?>) {
			var order_list = document.getElementById('order_list');
			var first_position = document.getElementById('position_1');
			var new_position = first_position.cloneNode(true);
			new_position.id = "position_" + ++position_count;
			order_list.appendChild(new_position);

			<?php /*
				To add a new order position, copy the first order position and modify it as explained:
				In HTML-Forms, the 'name' attribute of a from element defines the value key transmitted in HTTP. In order to distinguish the
				order position the form element belongs to, the name attribute is structured like this:
					name='[order-position]-[element-name]'
				Consequently, the [order-position] of the copied position needs to be replaced with the new position number.
				This is implemented here with the JS-string-replace-function, taking a regular expression (surrounded by slashes),
				which matches should be replaced. The g specifies, that all matches should be replaced, not only the first one.
				This implementation is simple, but quite weak, e.g. whitespace around the = breaks the system.
			*/ ?>

			new_position.innerHTML = first_position.innerHTML.replace(/name=\"1/g, "name=\"" + position_count);

			new_position.innerHTML = new_position.innerHTML.replace(/id=\"ingredients_burger_1/, "id=\"ingredients_burger_" + position_count);
			new_position.innerHTML = new_position.innerHTML.replace(/id=\"price_order_position_\d/, "id=\"price_order_position_" + position_count);
			new_position.innerHTML = new_position.innerHTML.replace(/id=\"burger_\d/, "id=\"burger_" + position_count);

			var checkElements = new_position.getElementsByTagName('input');
			for (i = 0; i < checkElements.length; i++) {
				if(String(checkElements[i].id).includes("check")) {
					var old = checkElements[i].id;
					checkElements[i].id = old.substring(0, old.length - 1) + "" + position_count;
				}
			}
			update_ingredients("burger_" + position_count);

			if (position_count == <?php echo $settings["order_max_position"] ?>) {
				var add = document.getElementById("add-position");
				add.parentNode.removeChild(add); //shorter would be add.remove() but this is not supported by older browsers
			}
        	calculate_price();
		}
	}

	function calculate_price(){
        var price_order_position = 0;
		var price_order = 0;

		var price_delivery = 0.5;

        var burgers = [];
        var supplements = [];
        var drinks = [];
		var ketchups = document.getElementsByClassName('check-Ketchup');
        var mayos = document.getElementsByClassName('check-Mayo');
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
            if(burgers[i].includes('Hamburger')){
                price_order_position += 4;

            }
            else if(burgers[i].includes('Beyond Meat')) {
				price_order_position += 6;
			}
			else if(burgers[i].includes('Double-Burger')){
                price_order_position += 5.5;
            }

            if(! supplements[i].includes('keine Beilage')) price_order_position += 1.4;
            if(! drinks[i].includes('kein Getränk')) price_order_position += 1.4;

			if(drinks[i].includes('Bulmers Cider')) price_order_position += 1.1;

            if(bacons[i].checked) price_order_position += 0.5;
            if(camemberts[i].checked) price_order_position += 0.5;
			if(mayos[i].checked) price_order_position += 0.1;
            if(ketchups[i].checked) price_order_position += 0.1;


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

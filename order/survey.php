<!DOCTYPE html>
<?php

include('../sql_config.php');
$db = mysqli_connect($sql_host, $sql_username, $sql_password, $sql_dbname);
if(!$db) exit("Database connection error: ".mysqli_connect_error());
?>


<html>
<head>
	<link href="style.css" rel="stylesheet" type="text/css" media="all">
	<link rel="stylesheet" href="../fonts/fork-awesome/css/fork-awesome.min.css">

	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">

	<title> Manhattan - Bestellung </title>

	<style>
	</style>
</head>

<body>
	<div class="logo-background">
		<div class='logo'>
			<img src='../images/logo.png' alt='Manhattan' width='100%'>
		</div>
	</div>

	<div class='content'>

		<p> Hallo lieber Burgerliebhaber und Manhattan-Fan, </p> 
        <p> da du einen Beyond-Meat-Burger bestellt hast haben wir eine kurze Frage an dich: </p>

		<p><b> Wie wichtig ist dir die Marke Beyond Meat bei unseren Burgern? </b></p>
        <p>
            Bist du überzeugt von unseren hochwertigen Patties der Marke Beyond Meat oder 
            würdest du auch vergleichbare Patties von anderen Herstellern mit ähnlicher Qualität bestellen,
            die preislich etwas günstiger ausfallen?
        </p>

		<form>
			<table>
				<tr>
					<td> <input type='radio' name='answer' value='0' id='answer0'/> </td>
					<td> <input type='radio' name='answer' value='1' id='answer1'/> </td>
					<td> <input type='radio' name='answer' value='2' id='answer2'/> </td>
					<td> <input type='radio' name='answer' value='3' id='answer3'/> </td>
					<td> <input type='radio' name='answer' value='4' id='answer4'/> </td>
				</tr>
				<tr>
					<td>
						<label class='hint' for='answer0' style='vertical-align: top'>Nicht so wichtig. Ich würde auch einen anderen veganen Burger bestellen</label>
					</td>
					<td>
						<label class='hint' for='answer1' style='vertical-align: top'>Eher nicht</label>
					</td>
					<td>
						<label class='hint' for='answer2' style='vertical-align: top'>Unentschlossen</label>
					</td>
					<td>
						<label class='hint' for='answer3' style='vertical-align: top'>Eher schon</label>
					</td>
					<td>
						<label class='hint' for='answer4' style='vertical-align: top'>Sehr wichtig. Ich möchte keinen anderen Burger als Beyond Meat</label>
					</td>
				</tr>
			</table>
			<br/>
			<textarea name='comment' placeholder='Kommentar'></textarea>
			<br/>
			<input type='submit' value='Abschicken'/>
			<br/>
			<a href='complete.php'>Überspringen</a>
		</form>
	</div>
</body>

</html>

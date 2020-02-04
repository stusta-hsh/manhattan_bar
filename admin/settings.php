<!--<link href="form.css" rel="stylesheet" type="text/css" media="all">-->

<?php
$page_title='settings';

include('header.php');

// Datenbankabfrage Settings
$sql = 'SELECT * FROM settings';
$sql_query = mysqli_prepare($db, $sql);
if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
mysqli_stmt_execute($sql_query);
$results = mysqli_stmt_get_result($sql_query);
mysqli_stmt_close($sql_query);

$settings = [];
foreach($results as $result){
	$settings[$result['title']] = $result['value'];
}

// Änderungen in die Datenbank schreiben
if($_POST){
	foreach($_POST as $title => $value){
		$sql = 'UPDATE settings SET value = ? WHERE title = ?';
		$sql_query = mysqli_prepare($db, $sql);
		if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
		mysqli_stmt_bind_param($sql_query, 'ss', $value, $title);
		mysqli_stmt_execute($sql_query);
		mysqli_stmt_close($sql_query);
	}
	header('Location: settings.php');
	exit();
}

?>
	<div class="content">
		<div class="card">
			<div class="card-title">Webseite</div>
			<div class="card-content">
				<div class="edit_employee_form">
					<form method='post' action=''>
						<div class="form-card-row">
							<label class="flex-100">Design
								<select name="stylesheet_id">
									<option value='0' <?php if($settings['stylesheet_id']==0)echo'selected' ?>>Standard</option>
									<option value='1' <?php if($settings['stylesheet_id']==1)echo'selected' ?>>Winter</option>
								</select>
							</label>
						</div>

						<div class="form-card-row">
							<label class="flex-300">Urlaubstext
								<textarea rows="4" name="away_text"><?php echo $settings['away_text'] ?></textarea>
							</label>
						</div>

						<div class="form-card-row">
							<label class="flex-300">Text im Footer
								<textarea rows="4" name="footer_text"><?php echo $settings['footer_text'] ?></textarea>
							</label>
						</div>

						<div class="form-card-row">
							<label class="flex-100">
								<input name="facebook_icon" type="hidden" value="0">
								<input name="facebook_icon" type="checkbox" value="1" <?php if($settings['facebook_icon']==1)echo'checked' ?>>
								Facebook-Icon
							</label>
							<label class="flex-300">
								Facebook-URL
								<input type="url" name="facebook_url" value="<?php echo $settings['facebook_url'] ?>">
							</label>
						</div>

						<div class="form-card-row">
							<label class="flex-100">
								<input name="messenger_icon" type="hidden" value="0">
								<input name="messenger_icon" type="checkbox" value="1" <?php if($settings['messenger_icon']==1)echo'checked' ?>>
								Messenger-Icon
							</label>
							<label class="flex-300">
								Messenger-URL
								<input type="url" name="messenger_url" value="<?php echo $settings['messenger_url'] ?>">
							</label>
						</div>

						<div class="form-card-row">
							<label class="flex-100">
								<input name="instagram_icon" type="hidden" value="0">
								<input name="instagram_icon" type="checkbox" value="1" <?php if($settings['instagram_icon']==1)echo'checked' ?>>
								Instagram-Icon
							</label>
							<label class="flex-300">
								Instagram-URL
								<input type="url" name="instagram_url" value="<?php echo $settings['instagram_url'] ?>">
							</label>
						</div>

						<div class="form-card-row">
							<label class="flex-100">
								<input name="email_icon" type="hidden" value="0">
								<input name="email_icon" type="checkbox" value="1" <?php if($settings['email_icon']==1)echo'checked' ?>>
								E-Mail-Icon
							</label>
							<label class="flex-300">
								E-Mail-Adresse
								<input type="email" name="email_url" value="<?php echo $settings['email_url'] ?>">
							</label>
						</div>

						<div class="form-card-row">
							<label class="flex-100">
								<input name="wiki_icon" type="hidden" value="0">
								<input name="wiki_icon" type="checkbox" value="1" <?php if($settings['wiki_icon']==1)echo'checked' ?>>
								Wiki-Icon
							</label>
							<label class="flex-300">
								Wiki-URL
								<input type="url" name="wiki_url" value="<?php echo $settings['wiki_url'] ?>">
							</label>
						</div>
						<br>
						<input type='submit' value='Anwenden'></input>
					</form>
				</div>
			</div>
		</div>
	</div>

</body>
</html>

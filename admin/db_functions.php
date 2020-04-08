<?php

function db_query($sql) {
	global $db;
	$sql_query = mysqli_prepare($db, $sql);
	if (!$sql_query) die('ERROR: Failed to prepare SQL:<br>'.$sql);
	mysqli_stmt_execute($sql_query);
	$result = mysqli_stmt_get_result($sql_query);
	mysqli_stmt_close($sql_query);
	return $result;
}

function get_openings($range_start, $range_end) {
	$sql = 	   'SELECT	id, date_from, date_to, name AS event
				  FROM	openings AS o
				  		LEFT JOIN events AS e
						ON o.event = e.id
				 WHERE	date_from BETWEEN "'.$range_start.'" AND "'.$range_end.'"
			  ORDER BY	date_from ASC';

	return db_query($sql);
}

function get_shifts($opening_id) {
	$sql = 	   'SELECT	date_from, date_to, name AS job
				  FROM	shifts AS s
				  		LEFT JOIN jobs AS j
						ON s.job = j.id
				 WHERE	opening = '.$opening_id.'
			  ORDER BY	date_from ASC';

	return db_query($sql);
}

function get_shifts_as_array($opening_id) {
	$shifts = get_shifts($opening_id);
	$shift_array = [];
	foreach ($shifts as $shift) {
		$shift_array = [
			'date_from' => $shift['date_from'];
		];
	}
}

function get_schedule() {

	$openings = get_openings('2020-01-01 00:00:00', '2021-01-01 00:00:00');

	//$shifts = db_query($sql);

	$schedule = [];
	foreach ($openings as $opening) {
		//$schedule[date('Y-m-d', strtotime($opening['start_time']))] = [
		$schedule[] = [
			'date_from' => $opening['date_from'],
			'date_to' => $opening['date_to'],
			'event' => $opening['event'],
			'shifts' => [

			],
		];
	}
	return $schedule;
}

?>

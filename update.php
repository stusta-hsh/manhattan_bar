<?php
$statickey="cKXUKymbVy6M4N6hfeDc3k5u";
if ($_GET["key"] === $statickey)
{
	$sql_config = parse_ini_file('sql_config.ini');

    $status = intval($_GET["status"]);

    $conn = new mysqli($sql_config['host'], $sql_config['username'], $sql_config['password'], $sql_config['dbname']);
    if ($conn->connect_error)
    {
        die("Connection failed: " . $conn->connect_error);
    }

    if (!($stmt = $conn->prepare("INSERT INTO `openstatus` (`STATUS`) VALUES (?)")))
    {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }


    if ($stmt->bind_param("i", $status) === false)
    {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    if (!$stmt->execute())
    {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    echo "OK";
}

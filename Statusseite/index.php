 <?php
$servername = "localhost";
$username = "e00038";
$password = "hfDAJSDWy7vR5Pmd";
$dbname = "e00038a";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT date, status FROM openstatus ORDER BY date DESC LIMIT 1";
$result = $conn->query($sql);


if ($result->num_rows == 0) {
    die("db result empty!");
}

$row = $result->fetch_assoc();
$status = $row["status"];
$lastrefreshed = $row["date"];
echo "<!--Last status: ". $status. "  updated: ". $lastrefreshed. " -->";

$lrd = strtotime($lastrefreshed);
$diff = time() - $lrd;

if ($status != 0 && $diff > 86400)
{
    echo "\n<!--WARNING: Assuming CLOSED because the last status update is older than a day! -->";
    $status = 0;
}


if ($status == 1)
{
    //echo "open";
    $image = "manhattan.png";
    $bcolor = "#06ac2d";
    $desc = "Aktuell offen";
    $titlestatus = "Geöffnet";
}
else if ($status == 2)
{
    $image = "manhattan.png";
    $bcolor = "green";
    $desc = "Aktuell offen<br><font color=\"#06ac2d\">Dachterrasse geöffnet</font>";
    $titlestatus = "Dachterrasse geöffnet";
}
else
{
    $image = "manhattan_closed.png";
    $bcolor = "gray";
    $desc = "<font color=\"gray\">Gerade geschlossen</font>";
    $titlestatus = "Geschlossen";
}

$conn->close();
?>

<html>
<head>
    <meta http-equiv="refresh" content="300" />
    <style>
        body {
            border: 7px solid <?php echo $bcolor; ?>;
        }

        * {
            margin: 0;
            padding: 0;
        }
        .imgbox {
            display: grid;
            height: 100%;
        }
        .center-fit {
            max-width: 80%;
            max-height: 80vh;
            margin: auto;
        }
    </style>
    <title>Manhattan - <?php echo $titlestatus; ?></title>
</head>
<body>
<div class="imgbox">
<br><br><br>
<center>
<font size="+5">
<?php echo $desc; ?>
</font>
</center>
<img class="center-fit" src="<?php echo $image; ?>">
<center><font color="gray">Zuletzt aktualisiert: <?php echo $lastrefreshed; ?></font></center>
</div>
</body> 
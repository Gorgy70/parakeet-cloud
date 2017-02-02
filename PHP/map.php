<?
include 'database.php';
include 'dexcodes.php';
include 'passcode.php';

$google_maps_url = "https://maps.google.com/?q=";


$transmitter_code = $_REQUEST["trans"];
$password_code = $_REQUEST["password"];
$transmitter_id = 0 | ($dexcode[$transmitter_code[0]] << 20) | ($dexcode[$transmitter_code[1]] << 15) |
                  ($dexcode[$transmitter_code[2]] << 10) | ($dexcode[$transmitter_code[3]] << 5) | ($dexcode[$transmitter_code[4]]);

//echo "transmitter_id = ".$transmitter_id."<BR>\n";

$number_of_records = 1;

if ($require_passcode and !$password_code) {
  echo "require_passcode is set to True";
  exit;
}

$sql = new mysqli($host, $user,$password,$database);

if ($mysqli->connect_error) {
   echo 'Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error . "<br/>\n";
   exit;
}


$stmt = $sql->stmt_init();
$stmt->prepare("CALL GET_TRANSMITTER_DATA (?,?,?)");
if ($stmt->errno) {
   echo 'Statement Error (' . $stmt->errno . ') ' . $stmt->error . "<br/>\n";
   exit;
}

$stmt->bind_param('iii',$transmitter_id,$password_code,$number_of_records);
if ($stmt->errno) {
   echo 'Statement Error (' . $stmt->errno . ') ' . $stmt->error . "<br/>\n";
   exit;
}

$stmt->execute();
if ($stmt->errno) {
   echo 'Statement Error (' . $stmt->errno . ') ' . $stmt->error . "<br/>\n";
   exit;
}

/* Определить переменные для результата */
$stmt->bind_result($data_time,$raw_value,$filtered_value,$dex_battery,$prev_time,$battery_perc,$battery_mvolt,$cpu_temp,$geolocation);

    /* Выбрать значения */
$i = 0;
while ($stmt->fetch()) {
  break;
}

    /* Завершить запрос */
$stmt->close();

if ($geolocation) {
  header("Location: ".$google_maps_url.$geolocation);
}

?>


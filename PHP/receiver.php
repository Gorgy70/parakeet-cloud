<?
//header( "Content-type: text/plain; charset=utf-8" ); 

include 'database.php';

$data_time = $_REQUEST["rr"];
$transmitter_id = $_REQUEST["zi"];
$password_code = $_REQUEST["pc"];
$raw_value  = $_REQUEST["lv"];
$filtered_value = $_REQUEST["lf"];
$dex_battery = $_REQUEST["db"];
$prev_time = $_REQUEST["ts"];
$battery_perc = $_REQUEST["bp"];
$battery_mvolt = $_REQUEST["bm"];
$cpu_temp = $_REQUEST["ct"];
$geolocation = $_REQUEST["gl"];
$max_records = 1000;


if ((!$raw_value or $raw_value==0 or $filtered_value==0 or $prev_time==0) and $transmitter_id != 10858926) {
  echo "ERR";
  exit;
}

if ($transmitter_id  == 0) {
  echo "ERR - no transmitter id - upgrade";
  exit;
}

$data_time = time() - $prev_time/1000;
$prev_time = 0;

$sql = new mysqli($host, $user,$password,$database);

if ($mysqli->connect_error) {
   echo 'Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error . "<br/>\n";
   exit;
}


$stmt = $sql->stmt_init();

$stmt->prepare("CALL ADD_TRANSMITTER_DATA (?,?,?,?,?,?,?,?,?,?,?,?)");
if ($stmt->errno) {
   echo 'Statement Error (' . $stmt->errno . ') ' . $stmt->error . "<br/>\n";
   exit;
}

$stmt->bind_param('iiiiiiiiiisi',$transmitter_id,$password_code,$data_time,$raw_value,$filtered_value,$dex_battery,$prev_time,$battery_perc,$battery_mvolt,$cpu_temp,$geolocation,$max_records);
if ($stmt->errno) {
   echo 'Statement Error (' . $stmt->errno . ') ' . $stmt->error . "<br/>\n";
   exit;
}

$stmt->execute();
if ($stmt->errno) {
   echo 'Statement Error (' . $stmt->errno . ') ' . $stmt->error . "<br/>\n";
   exit;
}

echo "!ACK 0!";

?>

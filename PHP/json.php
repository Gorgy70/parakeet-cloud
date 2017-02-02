<?php
header( "Content-type: text/plain; charset=utf-8" ); 
include 'database.php';
include 'dexcodes.php';
include 'passcode.php';

//echo "json <br>";
$transmitter_code = $_REQUEST["trans"];
$password_code = $_REQUEST["password"];
$number_of_records = $_REQUEST["n"];


$transmitter_id = 0 | ($dexcode[$transmitter_code[0]] << 20) | ($dexcode[$transmitter_code[1]] << 15) |
                  ($dexcode[$transmitter_code[2]] << 10) | ($dexcode[$transmitter_code[3]] << 5) | ($dexcode[$transmitter_code[4]]);

//echo "transmitter_id = ".$transmitter_id."<BR>\n";

if (!$number_of_records) {
  $number_of_records = 1;
}
elseif ($number_of_records > 100) {
  $number_of_records = 100;
}

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
  $s1 = "";
  $prev_time = time() - $data_time;
  if ($i > 0) {
    $s1 .= "\n";
  }
  else {
    $i = 1;
  }
  $s1 .= '{"TransmitterId": "'.$transmitter_id.'", "_id": 1, "CaptureDateTime": "'.$data_time.'000",  "RelativeTime": "'.$prev_time.'000", "RawValue": "'.$raw_value.'", ';
  $s1 .= '"TransmissionId": "0", "BatteryLife": "'.$dex_battery.'" ,"UploaderBatteryLife": "'.$battery_perc.'" ,"FilteredValue": "'.$filtered_value.'", ';
  $s1 .= '"GeoLocation": "'.$geolocation.'"}';
//  echo $s1;
  echo utf8_encode($s1);
}

    /* Завершить запрос */
$stmt->close();

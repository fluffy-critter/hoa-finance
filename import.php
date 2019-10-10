<?
require_once('db.php');
require_once('login.php');

?>
<html><head><title>CSV import</title></head></body>
<?
requireLogin();

$fname=$_GET['filename'];
$csvFile = fopen($fname, 'r') or die("Couldn't open $fname");

while (!feof($csvFile)) {
  $record = fgetcsv($csvFile);
  if (count($record) > 1) {
    $name=$record[0];
    $dateslash=$record[1];
    $dateparts=explode('/',$dateslash);
    $date=$dateparts[2] . '-' . $dateparts[0] . '-' . $dateparts[1];
    $amounts = array();
    for ($i = 0; $i <= 8; $i++) {
      if ($record[$i + 2] != '') {
	$amounts[$i] = $record[$i + 2];
      }
    }
    
    echo "date $date  $name<br>";
    addTransaction($date, $name, $amounts, false)
      or die("Couldn't add transaction: " . implode(' | ', $record));
  }
 }


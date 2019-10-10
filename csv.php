<?
require_once("db.php");
require_once("currency.php");

header('Content-type: text/csv');
header('Content-disposition: attachment; filename=' . curYear() . curMonth()
       . '.csv');

$whereargs = new whereArgs();
$accts = getAccountNames();
if ($_GET["annual"])
  $whereargs->setMonth(curYear(), 1, false);
else
  $whereargs->setMonth(curYear(), curMonth());

$totals = array();
$transactions = getTransactions($whereargs);

$alist = array_keys($accts);
echo 'Date,Note';
foreach ($alist as $num) {
  echo ',' . $num . '-' . $accts[$num];
}
echo "\n";

foreach ($transactions as $row) {
  echo $row['date'] . ',' . $row['note'];
  foreach ($alist as $acct) {
    $val = $row['val'][$acct];
    echo ',' . $val;
    $totals[$acct] += $val;
  }
  echo "\n";
}

echo ',Total';
foreach ($alist as $num) {
  echo ',' . $totals[$num];
}
echo "\n";
?>
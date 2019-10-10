<?
require_once("db.php");
require_once("currency.php");

$whereargs = new whereArgs();

$accts = getAccountNames();
if (isset($_GET['aid'])) {
  $aid = $_GET['aid'];
  $whereargs->addClause('money.account_id=?', 'i', array($aid));
  $alist = array($aid);
 } else {
  $alist = array_keys($accts);
 }


?>
<html>
<head>
<title>HOA monthly statement, <?= date("F Y", mktime(0,0,0,curMonth(),1,curYear())) ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?

  include("menu.php");

?>
<h1>HOA monthly statement, <?= date("F Y", mktime(0,0,0,curMonth(),1,curYear())) ?></h1>
<form method="GET" action=".">
  Show month:
<select name="date">
<?
  $months = getMonths();
foreach ($months as $month) {
  $parts = explode('-', $month);
  echo '<option value="' . $month . '"';
  if ($parts[0] == curYear() && $parts[1] == curMonth()) {
    echo ' selected';
  }
  echo '>' . date("F Y", mktime(0,0,0,$parts[1],1,$parts[0]))
    . '</option>';
}
?>
<input type="submit" value="Go">
</form>

<table><tr class="head"><th colspan=2>Date</th>
  <?;

if ($aid) {
  echo '<th>Check #</th>';
 }

foreach ($alist as $num)
{
  echo '<th>' . $accts[$num] . '</th>';
}

?>
</tr>
<?

$whereargs->setMonth(curYear(), curMonth());

$totals = array();
$transactions = getTransactions($whereargs);


$even = true;
foreach ($transactions as $row) {
  $even = !$even;
  ?><tr class="<?=$even?'even':'odd'?>"><th><?=$row['date']?></th>
    <td class="note"><?=$row['note']?></td><?;

  foreach ($alist as $acct) {
    $val = $row['val'][$acct];
    if ($aid) {
      echo '<td>' . $row['check'][$acct] . '</td>';
    }
    echo '<td>' . currency($val) . '</td>';
    $totals[$acct] += $val;
  }
  ?></tr><?;
}
?>

<tr class="foot"><th colspan=2>Total</th>
<?
foreach ($alist as $acct) {
  if ($aid) {
    echo '<td></td>';
  }
  if ($totals[$acct] < 0) {
    ?><td class="negative"><?;
  } else {
    ?><td><?;
  }
  ?><?=currency($totals[$acct],true)?></td><?;
}
?>
</tr>

</table>

</body></html>

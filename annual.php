<?
require_once("db.php");
require_once("currency.php");

$whereargs = new whereArgs();

$accts = getAccountNames();
$aid=0;
$alist=array(0);

?>
<html>
<head>
<title>HOA annual statement, <?= date("Y", mktime(0,0,0,curMonth(),1,curYear())) ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?

  include("menu.php");

?>
<h1>HOA annual statement, <?= date("Y", mktime(0,0,0,curMonth(),1,curYear())) ?></h1>

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

$whereargs->setMonth(curYear(), 1, false);
$whereargs->addClause("note NOT LIKE ?", 's', array('Opening balance'));
$whereargs->addClause("money.account_id=?",'i',array($aid));
#$whereargs->addClause("money.amount > 0",'',array());
#$whereargs->addClause("(note NOT LIKE ? OR tdate < ?)", 'ss',
#		      array('Opening balance',
#			    date("Y-m-d", mktime(0,0,0,2,1,curYear()))));


$totals = array();
$transactions = getTransactions($whereargs,
				"transaction.note ASC, transaction.tdate ASC");


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

<?
require_once('db.php');
require_once('login.php');
requireLogin(false);

if (!$_POST['m'] || !$_POST['y']) {
  die("Missing month/year of commit");
 }

$commitMonth = $_POST['m'];
$commitYear = $_POST['y'];

$startDate = date("Y-m-d", mktime(0,0,0,$commitMonth,1,$commitYear));
$endDate = date("Y-m-d", mktime(0,0,0,$commitMonth+1,1,$commitYear));

# add opening balance
{
  $wa = new whereArgs();
  $wa->setMonth($commitYear,$commitMonth);
  $transactions = getTransactions($wa);
  $totals = array();
  foreach ($transactions as $row) {
    foreach ($row['val'] as $acct => $val) {
      $totals[$acct] += $val;
    }
  }

  if (!addTransaction($endDate, "Opening balance", $totals, false)) {
    die("Couldn't set opening balance");
  }
}

# add dues
{
  if (!addTransaction($endDate, "Monthly dues",
		      array(1 => -321,
			    2 => -335,
			    3 => -324,
			    4 => -324,
			    5 => -335,
			    6 => -324,
			    7 => -335,
			    8 => -335), false)) {
    die("Couldn't set monthly dues");
  }
}

# apply treasurer credit
{
  if (!addTransaction($endDate, "Treasurer credit",
		      array(7 => 150), false)) {
    die("Couldn't add treasurer credit");
  }
}

# add dues received
{
  if (!addTransaction($endDate, "Dues received",
		      array(0 => 0), false)) {
    die("Couldn't add dues received");
  }
}

header('Location: transact.php?y=' . $commitYear . '&m=' . $commitMonth);
  

?>

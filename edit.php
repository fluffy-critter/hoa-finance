<?
require_once("db.php");
require_once("login.php");
requireLogin(false);

$tid = $_POST["tid"];
$aid = $_POST["aid"];
$mid = $_POST["mid"];
$amount = $_POST["amount"];
$checknum = $_POST["checknum"];
doLog("tid=$tid aid=$aid amount=$amount checknum=$checknum");

switch($_POST["op"]) {
case 'new':
  if (addTransaction($_POST["date"],
		     $_POST["note"],
		     array($aid => $_POST["amount"]),
		     array($aid => $_POST["checknum"]))) {
    header("Location: transact.php");
  } else {
    die("addTransaction failed");
  }
  break;
case 'madd':
case 'medit':
  if (setAmount($tid, $aid, $amount, $checknum, $_POST["sum0"])) {
    header("Location: transact.php");
  } else {
    die("setAmount failed");
  }
  break;
case 'del':
  if (deleteTransaction($tid)) {
    header("Location: transact.php");
  } else {
    die("deleteTransaction failed");
  }
  break;
default:
  echo 'unknown op ' . $_POST["op"];
  echo '<ul>';
  foreach ($_POST as $key => $val)
    echo '<li>' . $key . ' &rarr; ' . $val . '</li>';
  echo '</ul>';
}

?>

<?
require_once("db.php");
require_once("login.php");
require_once("currency.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"> 
<html><head><title>Transaction Editor</title>
<link rel="stylesheet" href="style.css">
</head><body>
<?

include("menu.php");
requireLogin();

$whereargs = new whereArgs();
$whereargs->setMonth(curYear(), curMonth(), false);

$accounts = getAccountNames();
$transactions = getTransactions($whereargs);

if ($_POST["id"]) {
  $editid = $_POST['tid'];
 } else if ($_GET['tid']) {
  $editid = $_GET['tid'];
 }

?>
<table>
<tr class="head"><th>Action</th><th>Date</th><th>Note</th>
  <?;
foreach ($accounts as $name) {
  ?><th><?=$name?></th><?;
 }
?>
</tr>
<?

$even = true;
foreach ($transactions as $id => $row) {
  $even = !$even;
  if ($id == $editid) {
    $class = 'selected';
  } else {
    $class = $even?'even':'odd';
  }

  ?><tr class="<?=$class?>"><th>
     <a href="?op=edit&amp;tid=<?=$id?>" title="Edit">E</a>
     <a href="?op=del&amp;tid=<?=$id?>" title="Delete">D</a>
     </th>
<td><?=$row['date']?></td><td><?=$row['note']?></td>
<?;
  foreach ($accounts as $aid => $aname) {
    echo '<td>';
    if ($row['mid'][$aid]) {
      echo '<a href="?op=medit&amp;tid=' . $id . '&amp;mid=' . $row['mid'][$aid]
	. '&amp;aid=' . $aid . '">'
	. currency($row['val'][$aid]) . '</a>';
    } else {
      echo '<span class="op">[<a href="?op=madd&amp;tid=' . $id
	. '&amp;aid=' . $aid
	. '" title="Add amount">add</a>]</span>';
    }
    echo '</td>';
  }
      
  ?></tr><?;
 }

?>
</table>
  <?;

echo '<form method="POST" action="edit.php">';

$getvals = $_GET;

function accountDropdown() {
  global $accounts, $getvals;
  echo '<select name="aid">';
   foreach ($accounts as $aid => $name) {
     echo '<option value="' . $aid . '"';
     if ($aid == $getvals['aid']) echo ' selected';
     echo '>' . $name . '</option>';
   }
   echo '</select>';

   unset($getvals['aid']);
}

function transactFields() {
echo '
<li>
<label for="text_date">Date:</label>
<input type="text" name="date" id="text_date" value="' . date('Y-m-d') . '">
</li>
<li>
<label for="text_note">Note:</label>
<input type="text" name="note" id="text_note">
</li>
';
}

function amountFields($info = array()) {
  echo '<li>
<label for="text_amount">Amount:</label>
<input type="text" name="amount" id="text_amount" value="' . ($info?$info['amount']:'0.00') . '">
</li><li>
<label for="text_check">Check number:</label>
<input type="text" name="checknum" id="text_checknum"' . ($info?' value="'.$info['checknum'].'"':'') .'>
</li>
';
}

/*
echo 'op = ' . $_GET["op"] . ' params = ';
foreach ($getvals as $k => $v) {
  echo "'$k' => '$v', ";
}
*/

switch ($_GET["op"]) {
 case '':
   echo '<input type="hidden" name="op" value="new">';
   accountDropdown();
   echo '<ul>';
   transactFields();
   amountFields();
   echo '</ul>';
   break;
 case 'madd':
 case 'medit':
   accountDropdown();
   echo '<ul>';
   if ($_GET["mid"]) {
     amountFields(getMoney($_GET["mid"]));
   } else {
     amountFields();
   }
   if ($aid != 0) {
     echo '<li><input type="checkbox" name="sum0" value="1" id="check_update" checked>
	<label for="check_update">Update reserves</label></li>';
   }
   echo '</ul>';
   break;
case 'del':
  echo 'Please confirm deletion of selected row';
  break;
 }

foreach ($getvals as $k => $v) {
  echo '<input type="hidden" name="' . $k . '" value="' . $v . '">';
}
echo '
<input type="submit" value="Submit">
</form>';


?>

Close out month: <form method="POST" action="monthly.php">
<input type="text" name="y" value="<?=curYear()?>" length=5>
<input type="text" name="m" value="<?=curMonth()?>" length=3>
<input type="submit" value="Go"></form>

</body></html>

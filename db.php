<?

$logFile = fopen("logs/" . date("Y-m"), "a");
function doLog($msg) {
  global $logFile;
  fwrite($logFile, $_SERVER['PHP_SELF'] . '|' . date("Y-m-d h:i:s") . '|' . $msg . "\n");
}

// FIXME
$db = new mysqli(...)
if (mysqli_connect_errno()) {
  die("Connection failed: " . mysqli_connect_error());
 }
$db->autocommit(FALSE);


class whereArgs {
  protected $params = array('');
  protected $clause = '';

  public function getParams() { return $this->params; }
  public function getClause() {
    if ($this->clause) {
      return ' WHERE ' . $this->clause;
    }
    return '';
  }

  public function addClause($csql, $ctypes, $cparams) {
    if ($this->clause) { $this->clause .= ' AND '; }
    $this->clause .= $csql;
    $this->params[0] .= $ctypes;
    $this->params = array_merge($this->params, $cparams);
  }

  public function setMonth($year, $month, $end = true) {
    $startDate = mktime(0,0,0,$month,1,$year);
    $endDate = mktime(0,0,0,$month + 1, 1, $year);
    if ($end) {
      $args = array(date('Y-m-d', $startDate), date('Y-m-d', $endDate));
      $where = '(tdate >= ? AND tdate < ?)';
      $this->addClause($where, "ss", $args);
    } else {
      $args = array(date('Y-m-d', $startDate));
      $where = 'tdate >= ?';
      $this->addClause($where, "s", $args);
    }
  }

}

function dbPrepare($sql) {
  global $db;
  doLog("dbPrepare($sql)");
  $st = $db->prepare($sql);
  if (!$st) {
    die("Couldn't prepare $sql: " . $db->error);
  }
  return $st;
}

function dbBindParams($st, $args, $lbl='') {
  doLog("dbBindParams(" . implode(' | ', $args ) . ',' . $lbl . ')');
  if (!call_user_func_array(array($st,'bind_param'), $args)) {
    die("Couldn't bind (" . implode(' | ', $args) . '): '
	. $st->error . ($lbl?' ('.$lbl.')':''));
  }
}

function dbExecute($st, $lbl='') {
  doLog("dbExecute($lbl)");
  if (!$st->execute()) {
    die("Couldn't execute: " . $st->error . ($lbl?' ('.$lbl.')':''));
  }
}

function getAccountNames() {
  doLog("getAccountNames");

  global $db;

  $q = $db->query("SELECT id,name FROM account ORDER BY id");
  if (!$q) {
    die("Query failed: " . $db->error);
  }

  $ret = array();
  while ($row = $q->fetch_row()) {
    $ret[$row[0]] = $row[1];
  }
  return $ret;
}

function getMonths() {
  doLog("getMonths");
  global $db;
  $q = $db->query("SELECT DISTINCT left(tdate,7) FROM transaction ORDER BY tdate DESC");
  if (!$q) {
    die("Query failed: " . $db->error);
  }

  $ret = array();
  while ($row = $q->fetch_row()) {
    $ret[] = $row[0];
  }
  return $ret;
}

function getTransactions($where=false,$order=false, $addSelect=false) {
  doLog("getTransactions()");

  global $db;

  $sql = 'SELECT transaction.id, transaction.tdate,
	transaction.note, money.id, money.checknum,
        money.account_id, money.amount';
  if ($addSelect) {
    $sql .= ', ' . $addSelect;
  }
  $sql .= ' FROM transaction
	LEFT JOIN money ON (money.transaction_id=transaction.id)';
  if ($where) {
    $sql .= $where->getClause();
  }
  $sql .= ' ORDER BY ' . ($order?$order:'transaction.tdate');

  $st = dbPrepare($sql);
  if ($where && $where->getClause()) {
    dbBindParams($st, $where->getParams());
  }

  if (!$st->execute()) {
    die("query failed: " . $db->error);
  }

  if (!$st->bind_result($id, $date, $note, $mid, $check, $account, $value)) {
    die("bind failed: " . $db->error);
  }
  
  $ret = array();
  while ($st->fetch()) {
    if ($date) {
      $ret[$id]['date'] = $date;
    }
    if ($note) {
      $ret[$id]['note'] = $note;
    }
    if ($mid) {
      $ret[$id]['mid'][$account] = $mid;
    }
    if ($check) {
      $ret[$id]['check'][$account] = $check;
    }
    if ($value) {
      $ret[$id]['val'][$account] = $value;
    }
  }
  return $ret;
}

function getMoney($mid) {
  doLog("getMoney($mid)");
  $sql = 'SELECT id, account_id, amount, checknum FROM money WHERE id=?';
  $st = dbPrepare($sql);
  dbBindParams($st, array("i", $mid));
  if (!$st->execute()) {
    die("query failed: " . $db->error);
  }
  if (!$st->bind_result($id, $aid, $amt, $checknum)) {
    die("bind failed: " . $db->error);
  }

  $st->fetch();
  $ret = array("id" => $id, "aid" => $aid, "amount" => $amt, "checknum" => $checknum);
  return $ret;
}
  

function setAmounts($tid, $amounts, $checks) {
  doLog("setAmounts($tid)");
  
  global $db;

  $params = array('');
  $sql = false;
  foreach ($amounts as $acct => $amount) {
    if (!$sql) {
      $sql = 'INSERT INTO money (transaction_id,account_id,amount,checknum) '
	. 'VALUES (?,?,?';
    } else {
      $sql .= ',(?,?,?';
    }
    $params[0] .= 'iis';
    $params[] = $tid;
    $params[] = $acct;
    $params[] = $amount;

    if ($checks && $checks[$acct]) {
      $params[0] .= 's';
      $params[] = $checks[$acct];
      $sql .= ',?';
    } else {
      $sql .= ',NULL';
    } 

    $sql .= ')';
  }
  if ($sql) {
    $st = dbPrepare($sql);
    dbBindParams($st, $params);
    dbExecute($st, "adding amounts");
    return true;
  }
  return false;
}

function addTransaction($date, $note, $amounts, $checks) {
  doLog("addTransaction($date,$note)");

  global $db;

  $st = $db->prepare("INSERT INTO transaction (tdate,note) VALUES (?,?)");
  if (!$st) {
    die("Couldn't prepare statement: " . $db->error);
  }

  if (!$st->bind_param("ss", $date, $note)) {
    die("Couldn't bind parameters: " . $db->error);
  }

  if (!$st->execute()) {
    die("Couldn't create transaction: " . $db->error);
  }

  $tid = $db->insert_id;
  if (setAmounts($tid, $amounts, $checks)) {
    $db->commit();
    return true;
  }

  echo "Discarding empty transaction";
  $db->rollback();
  return false;
}

function changeTransaction($tid, $date, $note, $amounts, $checks) {
  doLog("changeTransaction($tid,$date,$note)");

  global $db;

  $st = dbPrepare("UPDATE transaction SET tdate=?, note=? WHERE id=?");
  dbBindParams($st, array("ss",$date,$note,$tid));
  dbExecute($st);

  $st = dbPrepare("DELETE money WHERE transaction_id=?");
  dbBindParams($st, array('i',$tid));
  dbExecute($st, "delete transaction amounts");

  if (setAmounts($tid, $amounts, $checks)) {
    $db->commit();
    return true;
  }

  echo "Discarding empty transaction";
  $db->rollback();
  return false;
} 

function setAmount($tid, $aid, $amount, $checknum, $update) {
  doLog("setAmount(tid=$tid,aid=$aid,amount=$amount,checknum=$checknum,update=$update)");

  if (!$tid) {
    die("missing tid");
  }
  if (!$aid) {
    die("missing aid");
  }

  global $db;

  $st = dbPrepare("DELETE FROM money WHERE transaction_id=? AND account_id=?");
  dbBindParams($st, array('ii',$tid,$aid));
  dbExecute($st, "nulling old amount");

  if (!setAmounts($tid, array($aid=>$amount),
		 $checknum?array($aid=>$checknum):array())) {
    echo "Couldn't set check amount";
    return false;
  }

  if ($update) {
    $st = dbPrepare("DELETE FROM money WHERE transaction_id=? and account_id=0");
    dbBindParams($st, array('i',$tid), 'deleting old total');
    dbExecute($st, "nulling old sum");

    $st = dbPrepare("SELECT SUM(amount) FROM money WHERE transaction_id = ?
                            AND account_id != 0");
    dbBindParams($st, array('i',$tid), 'calculating sum');
    dbExecute($st);
    if (!$st->bind_result($total)) {
      die("bind failed: " . $db->error);
    }
    $numrows = 0;
    while ($st->fetch()) {
      ++$numrows;
    }
    if ($numrows != 1) {
      die("Got $numrows results for sum of tid=$tid, expected 1");
    }

    $st = dbPrepare("INSERT INTO money (transaction_id,account_id,amount) 
                     VALUES (?,0,?)");
    dbBindParams($st, array('is', $tid, $total), 'inserting total');
    dbExecute($st, "inserting new sum");
  }

  $db->commit();
  return true;
}

function deleteTransaction($tid) {
  doLog("deleteTransaction(tid=$tid,aid=$aid)");
  if (!$tid) {
    die("missing tid");
  }

  global $db;

  $st = dbPrepare("DELETE FROM money WHERE transaction_id=?");
  dbBindParams($st, array('i',$tid));
  dbExecute($st, "deleting transaction money");

  $st = dbPrepare("DELETE FROM transaction WHERE id=?");
  dbBindParams($st, array('i',$tid));
  dbExecute($st);
  $db->commit();
  return true;
}

?>

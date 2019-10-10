<?

function currency($val,$force=false) {
  if ($val || $force) {
    return sprintf("$%.2f", $val);
  }
  return '';
}

function curMonth() {
  if ($_GET['m']) {
    return (int)($_GET['m']);
  }
  if ($_GET['date']) {
    $parts = explode('-', $_GET['date']);
    return (int)$parts[1];
  }
  return date('m');
}

function curYear() {
  if ($_GET['y']) {
    return (int)($_GET['y']);
  }
  if ($_GET['date']) {
    $parts = explode('-', $_GET['date']);
    return (int)$parts[0];
  }
  return date('Y');
}

?>
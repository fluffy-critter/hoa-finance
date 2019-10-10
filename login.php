<?

if (!session_start()) {
  die('Could not start session');
 }

if ($_GET["logout"] == 1) {
  $_SESSION["loggedin"] = 0;
}


function requireLogin($showLogout = true) {
  // FIXME
  if ($_POST["username"] === 'username'
      && $_POST["password"] === 'password') {
    $_SESSION["loggedin"] = true;
  }

  if ($_SESSION["loggedin"]) {
    if ($showLogout) {
      ?><a href="<?=$_SELF?>?logout=1">Log out</a><?;
    }
    return true;
  } else {
    ?>
<html><head>
<title>Login required</title>
<link rel="stylesheet" href="style.css">
</head><body>
<form id="login" method="POST" action="<?=$_SERVER['PHP_SELF']?>">
<input type="text" name="username">
<input type="password" name="password">
<input type="submit" value="Log in">
</form>
</body></html>
      <?;
    exit(0);
  }
}
?>

<?PHP

include("./password_protect.php");
include("./first.php");

if (isset($_GET['q'])) $q=trim($_GET['q']);

/* Usernames */
if (isset($_SESSION['username'])) $username = $_SESSION['username'];
if (!(isset($username))) header('location: ./password_protect.php?logout=yes');
if (!(is_dir('./tasks/'.$username))) mkdir('./tasks/'.$username);

if (file_exists('./allbooks/'.$username.'/'.$q)) unlink('./allbooks/'.$username.'/'.$q);
else die ('<h1>Fail</h1><p>You just tried to delete a list that does not exist...</p>');

header ('location: ./?tools=1');

?>

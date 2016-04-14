<?PHP

include("./password_protect.php");
include("./first.php");

if (isset($_POST['q'])) $q=$_POST['q'];
if (isset($_POST['content'])) $content=$_POST['content'];

/* Usernames */
if (isset($_SESSION['username'])) $username = $_SESSION['username'];
if (!(isset($username))) header('location: ./password_protect.php?logout=yes');

if (!(isset($q))) die ('Please specify which project you want to write notes for...');

$fixedq = str_replace ('?', '', str_replace ('.', '', str_replace ('!', '', str_replace ('?', '', str_replace (':', '', str_replace (' ', '_', $q))))));

$handle = fopen('./projectnotes/'.$username.'/'.$fixedq.'.htm', 'w');
fwrite($handle, cleanup_str($content));
fclose($handle);

header ('Location: ./projects.php?q='.$q);

?>

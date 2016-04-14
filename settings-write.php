<?PHP

/* This file writes the specified values to the settings file (.xml) of the respective logged in user */

include("./first.php");
include("./password_protect.php");

if (isset($_GET['q'])) $q=cleanup_str($_GET['q']);
if (isset($_GET['name'])) $name=cleanup_str($_GET['name']);
if (isset($_GET['email'])) $email=cleanup_str($_GET['email']);
if (isset($_GET['emailpublic'])) $emailpublic=cleanup_str($_GET['emailpublic']);
if (isset($_GET['homepage'])) $homepage=cleanup_str($_GET['homepage']);
if (isset($_GET['description'])) $description=cleanup_str($_GET['description']);
if (isset($_GET['job'])) $job=cleanup_str($_GET['job']);
if (isset($_GET['image'])) $image=cleanup_str($_GET['image']);
if (isset($_GET['affiliation'])) $affiliation=cleanup_str($_GET['affiliation']);
if (isset($_GET['city'])) $city=cleanup_str($_GET['city']);
if (isset($_GET['description'])) $description=cleanup_str($_GET['description']);
if (isset($_GET['css'])) $css=cleanup_str($_GET['css']);
if (isset($_GET['helpyesno'])) $helpyesno=cleanup_str($_GET['helpyesno']);

/* Usernames */
if (isset($_SESSION['username'])) $username = $_SESSION['username'];
if (!(isset($username))) header('location: ./password_protect.php?logout=yes');
if (!(is_dir('./topics/'.$username))) mkdir('./topics/'.$username);

$handle = fopen('./settings/'.$username.'.xml', 'w');

fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);
fwrite($handle, '<xml>'.PHP_EOL);

fwrite($handle, ' <lastBuildDate>'.date("r", time()).'</lastBuildDate>'.PHP_EOL);
	 
	fwrite($handle, '<name>'.$name.'</name>'.PHP_EOL);
	fwrite($handle, '<email>'.$email.'</email>'.PHP_EOL);
	fwrite($handle, '<emailpublic>'.$emailpublic.'</emailpublic>'.PHP_EOL);
	fwrite($handle, '<homepage>'.$homepage.'</homepage>'.PHP_EOL);
	fwrite($handle, '<job>'.$job.'</job>'.PHP_EOL);
	fwrite($handle, '<image>'.$image.'</image>'.PHP_EOL);
	fwrite($handle, '<affiliation>'.$affiliation.'</affiliation>'.PHP_EOL);
	fwrite($handle, '<city>'.$city.'</city>'.PHP_EOL);
	fwrite($handle, '<description>'.$description.'</description>'.PHP_EOL);
	fwrite($handle, '<css>'.$css.'</css>'.PHP_EOL);
	fwrite($handle, '<helpyesno>'.$helpyesno.'</helpyesno>'.PHP_EOL);
	
fwrite($handle, '</xml>'.PHP_EOL);
  
fclose($handle);

if (isset($q)) header ('location: ./?q='.$q.'&settings=1');
else header ('location: ./?settings=1');

?>

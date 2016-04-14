<?PHP

include("./password_protect.php");
include("./first.php");

if (isset($_GET['q'])) $q=$_GET['q'];
if (isset($_GET['relation'])) $relation=$_GET['relation'];
if (isset($_GET['r'])) $r=$_GET['r'];

/* Usernames */
if (isset($_SESSION['username'])) $username = $_SESSION['username'];
if (!(isset($username))) header('location: ./password_protect.php?logout=yes');

$content = file_get_contents('./projects/'.$username.'_projrelations.xml');
$x = new SimpleXmlElement($content);

	if (((!(isset($q)))or($q == ''))and(!(isset($relation)))) die('Sorry, an essential value was not specified');
	 
	$handle = fopen('./projects/'.$username.'_projrelations.xml', 'w');
  
  fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);
  fwrite($handle, '<xml version="2.0">'.PHP_EOL);
  
  fwrite($handle, ' <lastBuildDate>'.date("r", time()).'</lastBuildDate>'.PHP_EOL);
	
	fwrite($handle, PHP_EOL);
	
	fwrite($handle, '<item>'.PHP_EOL);
	
		fwrite($handle, '  <from>'.cleanup_str($q).'</from>'.PHP_EOL);
		fwrite($handle, '  <relation>'.cleanup_str($relation).'</relation>'.PHP_EOL);
		fwrite($handle, '  <to>'.cleanup_str($r).'</to>'.PHP_EOL);
		fwrite($handle, '  <created>'.date("r", time()).'</created>'.PHP_EOL);
				
	fwrite($handle, '</item>'.PHP_EOL);
			
	foreach($x->item as $entry) {
		
		fwrite($handle, ' <item>'.PHP_EOL);
		fwrite($handle, '  <from>'.$entry->from.'</from>'.PHP_EOL);
		fwrite($handle, '  <relation>'.$entry->relation.'</relation>'.PHP_EOL);
		fwrite($handle, '  <to>'.$entry->to.'</to>'.PHP_EOL);
		fwrite($handle, '  <created>'.$entry->created.'</created>'.PHP_EOL);
		fwrite($handle, ' </item>'.PHP_EOL);
		fwrite($handle, PHP_EOL);
	
	}
	
	fwrite($handle, PHP_EOL);
	fwrite($handle, '</xml>'.PHP_EOL);
  
	fclose($handle);

chmod ('./projects/'.$username.'_projrelations.xml', 0777);

header ('location: ./projects.php?q='.$q);

?>

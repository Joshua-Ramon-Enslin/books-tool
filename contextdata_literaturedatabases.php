<?PHP

/* Using this file new literature databases can be added to the list provided in the "Additional Functions" menu. The list is saved in an .xml file. */

include("./password_protect.php");
include("./first.php");

/* Get and clean up GET variables. $title refers to the title of the database/search engine. $link refers to the general link. $var refers to the search variable used by the website's search function. */
if (isset($_GET['title'])) $title=cleanup_str(str_replace(' ', '_', $_GET['title']));
if (isset($_GET['link'])) $link=cleanup_str($_GET['link']);
if (isset($_GET['var'])) $var=cleanup_str($_GET['var']);

/* Usernames */
if (isset($_SESSION['username'])) $username = $_SESSION['username'];
if (!(isset($username))) header('location: ./password_protect.php?logout=yes');

/* Read the existing xml file */
$content = file_get_contents('./contextdata/literaturedatabases.xml');
$x = new SimpleXmlElement($content);

if (isset($title)){

	$handle = fopen('./contextdata/literaturedatabases.xml', 'w');
  
	fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);
	fwrite($handle, '<xml>'.PHP_EOL);
  
	fwrite($handle, '<channel>'.PHP_EOL);
	fwrite($handle, ' <lastBuildDate>'.date("r", time()).'</lastBuildDate>'.PHP_EOL);
	
	fwrite($handle, PHP_EOL.PHP_EOL);

	/* Write newly added information to the database */	
	fwrite($handle, '<item>'.PHP_EOL);
	
		fwrite($handle, '  <title>'.cleanup_str($title).'</title>'.PHP_EOL);
		fwrite($handle, '  <link>'.cleanup_str($link).'</link>'.PHP_EOL);
		fwrite($handle, '  <var>'.cleanup_str($var).'</var>'.PHP_EOL);
		fwrite($handle, '  <created>'.date("r", time()).'</created>'.PHP_EOL);
				
	fwrite($handle, '</item>'.PHP_EOL);
	
	/* Write already existing data to the file. If an website/search engine/literature database equals the one that has just been added, skip it. */
	foreach($x->channel->item as $entry) {
		
		if (strval($entry->title) != strval($title)) {
			fwrite($handle, ' <item>'.PHP_EOL);
			fwrite($handle, '  <title>'.$entry->title.'</title>'.PHP_EOL);
			fwrite($handle, '  <link>'.$entry->link.'</link>'.PHP_EOL);
			fwrite($handle, '  <var>'.$entry->var.'</var>'.PHP_EOL);
			fwrite($handle, '  <created>'.$entry->created.'</created>'.PHP_EOL);
			fwrite($handle, ' </item>'.PHP_EOL);
			fwrite($handle, PHP_EOL);
		}

	}
	
	fwrite($handle, PHP_EOL);
	fwrite($handle, '</channel>'.PHP_EOL);
	fwrite($handle, '</xml>'.PHP_EOL);
  
	fclose($handle);
}

chmod ('./contextdata/literaturedatabases.xml', 0755);

/* Return to the page for additional functions */
header ('location: ./?additionalfunctions=1');

?>

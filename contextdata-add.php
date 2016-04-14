<?PHP

/* This file creates new xml files (or overwrites them if already existent) for data used in the lists section. Journals/Publishers/Authors can be described here. */

include("./first.php");
include("./password_protect.php");


/* Get GET variables, clean them and then split them if necessary */
if (isset($_GET['q'])) $q=cleanup_str($_GET['q']);
if (isset($_GET['kind'])) $kind=cleanup_str($_GET['kind']);
if (isset($_GET['publ'])) $publisher=explode('|', cleanup_str($_GET['publ']));
if (isset($_GET['publobeh'])) $publishedonbehalf=explode('|', cleanup_str($_GET['publobeh']));
if (isset($_GET['issn'])) $issn=explode('|', cleanup_str($_GET['issn']));
if (isset($_GET['start'])) $start=explode('|', cleanup_str($_GET['start']));
if (isset($_GET['end'])) $end=explode('|', cleanup_str($_GET['end']));
if (isset($_GET['website'])) $website=explode('|', cleanup_str($_GET['website']));
if (isset($_GET['wikipedia'])) $wikipedia=explode('|', cleanup_str($_GET['wikipedia']));
if (isset($_GET['publwebs'])) $publisherwebsite=explode('|', cleanup_str($_GET['publwebs']));

/* Usernames */
if (isset($_SESSION['username'])) $username = $_SESSION['username'];
if (!(isset($username))) header('location: ./password_protect.php?logout=yes');

/* Open the respective file */
$handle = fopen('./contextdata/'.$kind.'/'.str_replace(' ', '_', $q).'.xml', 'w');

/* Write out XML */
fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);
fwrite($handle, '<xml>'.PHP_EOL);

fwrite($handle, ' <lastBuildDate>'.date("r", time()).'</lastBuildDate>'.PHP_EOL);
	 
	foreach ($publisher as $publishers) fwrite($handle, '<publisher>'.$publishers.'</publisher>'.PHP_EOL);
	foreach ($publishedonbehalf as $publishedonbehalfof) fwrite($handle, '<publishedonbehalfof>'.$publishedonbehalfof.'</publishedonbehalfof>'.PHP_EOL);
	foreach ($issn as $issns) fwrite($handle, '<issn>'.$issns.'</issn>'.PHP_EOL);
	fwrite($handle, '<years>'.PHP_EOL);
	foreach ($start as $starty) fwrite($handle, '<start>'.$starty.'</start>'.PHP_EOL);
	foreach ($end as $endy) fwrite($handle, '<end>'.$endy.'</end>'.PHP_EOL);
	fwrite($handle, '</years>'.PHP_EOL);
	foreach ($website as $websites) fwrite($handle, '<website>'.$websites.'</website>'.PHP_EOL);
	foreach ($wikipedia as $wikipedias) fwrite($handle, '<wikipedia>'.$wikipedias.'</wikipedia>'.PHP_EOL);
	foreach ($publisherwebsite as $publwebss) fwrite($handle, '<publisherwebsite>'.$publwebss.'</publisherwebsite>'.PHP_EOL);
	
fwrite($handle, '</xml>'.PHP_EOL);
  
fclose($handle);

/* Return to the lists function */
if (isset($q)) header ('location: ./lists.php?q='.$kind);

?>

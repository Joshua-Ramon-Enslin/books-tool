<?PHP

/* Using this file, users can add texts */

include("./password_protect.php");
include("./first.php");

if (isset($_GET['q'])) $q=$_GET['q'];
if (isset($_GET['guid'])) $guid=$_GET['guid'];
if (isset($_GET['progress'])) $progress=$_GET['progress'];
if (isset($_GET['title'])) $title=$_GET['title'];
if (isset($_GET['note'])) $note=$_GET['note'];
if (isset($_GET['quote'])) $quote=$_GET['quote'];
if (isset($_GET['pageno'])) $pageno=$_GET['pageno'];
if (isset($_GET['quotecomment'])) $quotecomment = $_GET['quotecomment'];
else $quotecomment = '';
if (isset($_GET['quoteprivate'])) $quoteprivate = $_GET['quoteprivate'];
else $quoteprivate = '';
if (isset($_GET['delete'])) $delete=$_GET['delete'];
if (isset($_GET['duedate'])) $duedate=$_GET['duedate'];

/* Usernames */
if (isset($_SESSION['username'])) $username = $_SESSION['username'];
if (!(isset($username))) header('location: ./password_protect.php?logout=yes');

if (((!(isset($guid)))or($guid == ''))and(!(isset($title)))) die('Sorry, an essential value was not specified');

$content = file_get_contents('./tasks/'.$username.'.xml');
$x = new SimpleXmlElement($content);

if (isset($guid)){
	 
	$handle = fopen('./tasks/'.$username.'.xml', 'w');
  
  fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);
  fwrite($handle, '<rss version="2.0">'.PHP_EOL);
  
  fwrite($handle, '<channel>'.PHP_EOL);
  fwrite($handle, ' <lastBuildDate>'.date("r", time()).'</lastBuildDate>'.PHP_EOL);
	
	fwrite($handle, PHP_EOL.PHP_EOL);
  

	fwrite($handle, PHP_EOL);
	
	foreach($x->channel->item as $entry) {
		if ($entry->guid == $guid){
			
			fwrite($handle, '<item>'.PHP_EOL);
				if (isset($title)) fwrite($handle, '  <title>'.cleanup_str($title).'</title>'.PHP_EOL);
				else fwrite($handle, '  <title>'.$entry->title.'</title>'.PHP_EOL);
				if (isset($progress)) fwrite($handle, '  <progress>'.cleanup_str($progress).'</progress>'.PHP_EOL);
				else fwrite($handle, '  <progress>'.$entry->progress.'</progress>'.PHP_EOL);
				fwrite($handle, '  <guid>'.cleanup_str($guid).'</guid>'.PHP_EOL);
				fwrite($handle, '  <created>'.$entry->created.'</created>'.PHP_EOL);
				if (isset($progress)) fwrite($handle, '  <edit><before>'.$entry->progress.'</before><after>'.cleanup_str($progress).'</after><pubDate>'.date("r", time()).'</pubDate></edit>'.PHP_EOL);
				foreach ($entry->edit as $edit){
					fwrite($handle, '  <edit><before>'.$edit->before.'</before><after>'.$edit->after.'</after><pubDate>'.$edit->pubDate.'</pubDate></edit>'.PHP_EOL);
				}
				if ((isset($quote))and(!(isset($_GET['deletequote'])))) fwrite($handle, '  <quote><quote>'.str_replace(PHP_EOL, '[[br /]]', cleanup_str($quote)).'</quote><pageno>'.cleanup_str($pageno).'</pageno><comment>'.cleanup_str($quotecomment).'</comment><private>'.$quoteprivate.'</private><pubDate>'.date("r", time()).'</pubDate></quote>'.PHP_EOL);
				foreach ($entry->quote as $edit){
					if (cleanup_str($edit->quote) != $quote) fwrite($handle, '  <quote><quote>'.$edit->quote.'</quote><pageno>'.$edit->pageno.'</pageno><comment>'.$edit->comment.'</comment><private>'.$edit->private.'</private><pubDate>'.$edit->pubDate.'</pubDate></quote>'.PHP_EOL);
				}
				if (isset($note)) fwrite($handle, '  <note>'.cleanup_str($note).'</note>'.PHP_EOL);
				else fwrite($handle, '  <note>'.cleanup_str($entry->note).'</note>'.PHP_EOL);
				if (isset($duedate)) fwrite($handle, '  <due>'.cleanup_str($duedate).'</due>'.PHP_EOL);
				else fwrite($handle, '  <due>'.cleanup_str($entry->due).'</due>'.PHP_EOL);
			fwrite($handle, '</item>'.PHP_EOL);
			
		}
		else {
			fwrite($handle, ' <item>'.PHP_EOL);
				fwrite($handle, '  <title>'.$entry->title.'</title>'.PHP_EOL);
				fwrite($handle, '  <progress>'.$entry->progress.'</progress>'.PHP_EOL);
				fwrite($handle, '  <guid>'.$entry->guid.'</guid>'.PHP_EOL);
				fwrite($handle, '  <created>'.$entry->created.'</created>'.PHP_EOL);
				foreach ($entry->edit as $edit){
					fwrite($handle, '  <edit><before>'.$edit->before.'</before><after>'.$edit->after.'</after><pubDate>'.$edit->pubDate.'</pubDate></edit>'.PHP_EOL);
				}
				foreach ($entry->quote as $edit){
					fwrite($handle, '  <quote><quote>'.$edit->quote.'</quote><pageno>'.$edit->pageno.'</pageno><comment>'.$edit->comment.'</comment><private>'.$edit->private.'</private><pubDate>'.$edit->pubDate.'</pubDate></quote>'.PHP_EOL);
				}
				fwrite($handle, '  <note>'.cleanup_str($entry->note).'</note>'.PHP_EOL);
				fwrite($handle, '  <due>'.cleanup_str($entry->due).'</due>'.PHP_EOL);
			fwrite($handle, ' </item>'.PHP_EOL);
			fwrite($handle, PHP_EOL);
		}
	}
	
	fwrite($handle, PHP_EOL);
	fwrite($handle, '</channel>'.PHP_EOL);
	fwrite($handle, '</rss>'.PHP_EOL);
  
	fclose($handle);
}
else if (isset($title)){
	 
	$handle = fopen('./tasks/'.$username.'.xml', 'w');
  
  fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);
  fwrite($handle, '<rss version="2.0">'.PHP_EOL);
  
  fwrite($handle, '<channel>'.PHP_EOL);
  fwrite($handle, ' <lastBuildDate>'.date("r", time()).'</lastBuildDate>'.PHP_EOL);
	
	fwrite($handle, PHP_EOL.PHP_EOL);
  

	fwrite($handle, PHP_EOL);
	
	fwrite($handle, '<item>'.PHP_EOL);
	
		fwrite($handle, '  <title>'.cleanup_str($title).'</title>'.PHP_EOL);
		fwrite($handle, '  <progress>0</progress>'.PHP_EOL);
		$newguid =intval($x->channel->item[0]->guid)+ intval(1);
		fwrite($handle, '  <guid>'.$newguid.'</guid>'.PHP_EOL);
		fwrite($handle, '  <created>'.date("r", time()).'</created>'.PHP_EOL);
				
	fwrite($handle, '</item>'.PHP_EOL);
			
	foreach($x->channel->item as $entry) {
		
		fwrite($handle, ' <item>'.PHP_EOL);
		fwrite($handle, '  <title>'.$entry->title.'</title>'.PHP_EOL);
		fwrite($handle, '  <progress>'.$entry->progress.'</progress>'.PHP_EOL);
		fwrite($handle, '  <guid>'.$entry->guid.'</guid>'.PHP_EOL);
		fwrite($handle, '  <created>'.$entry->created.'</created>'.PHP_EOL);
		foreach ($entry->edit as $edit){
			fwrite($handle, '  <edit><before>'.$edit->before.'</before><after>'.$edit->after.'</after><pubDate>'.$edit->pubDate.'</pubDate></edit>'.PHP_EOL);
		}
		foreach ($entry->quote as $edit){
			fwrite($handle, '  <quote><quote>'.$edit->quote.'</quote><pageno>'.$edit->pageno.'</pageno><comment>'.$edit->comment.'</comment><private>'.$edit->private.'</private><pubDate>'.$edit->pubDate.'</pubDate></quote>'.PHP_EOL);
		}
		fwrite($handle, '  <note>'.cleanup_str($entry->note).'</note>'.PHP_EOL);
		fwrite($handle, '  <due>'.cleanup_str($entry->due).'</due>'.PHP_EOL);
		fwrite($handle, ' </item>'.PHP_EOL);
		fwrite($handle, PHP_EOL);
	
	}
	
	fwrite($handle, PHP_EOL);
	fwrite($handle, '</channel>'.PHP_EOL);
	fwrite($handle, '</rss>'.PHP_EOL);
  
	fclose($handle);
}

/* Set file permissions and go to the page of the added text */
chmod ('./tasks'.$username.'.xml', 0700);

header ('location: ./?q='.$title);

?>

<?PHP

include("./password_protect.php");
include("./first.php");

if (isset($_GET['q'])) $q=$_GET['q'];
if (isset($_GET['guid'])) $guid=$_GET['guid'];
if (isset($_GET['title'])) $title=$_GET['title'];
if (isset($_GET['assoquot'])) $assoquot=$_GET['assoquot'];
if (isset($_GET['assoquottext'])) $assoquottext=$_GET['assoquottext'];
if (isset($_GET['assoquotpageno'])) $assoquotpageno=$_GET['assoquotpageno'];
if (isset($_GET['assoquotcomment'])) $assoquotcomment=$_GET['assoquotcomment'];
else $assoquotcomment == '';
if (isset($_GET['assoquotprivate'])) $assoquotprivate=$_GET['assoquotprivate'];
else $assoquotprivate == '';
if (isset($_GET['assotext'])) $assotext=$_GET['assotext'];
if (isset($_GET['assotextcomment'])) $assotextcomment=$_GET['assotextcomment'];
if (isset($_GET['assodatasourcetitle'])) $assodatasourcetitle=$_GET['assodatasourcetitle'];
if (isset($_GET['assodatasourcelink'])) $assodatasourcelink=$_GET['assodatasourcelink'];
if (isset($_GET['assodatasourcecomment'])) $assodatasourcecomment=$_GET['assodatasourcecomment'];
if (isset($_GET['link'])) $link=$_GET['link'];

/* Usernames */
if (isset($_SESSION['username'])) $username = $_SESSION['username'];
if (!(isset($username))) header('location: ./password_protect.php?logout=yes');

$content = file_get_contents('./projects/'.$username.'.xml');
$x = new SimpleXmlElement($content);

	if (((!(isset($guid)))or($guid == ''))and(!(isset($title)))) die('Sorry, an essential value was not specified');

if (isset($guid)){
	 
	$handle = fopen('./projects/'.$username.'.xml', 'w');
  
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
				if (isset($link)) fwrite($handle, '  <link>'.cleanup_str($link).'</link>'.PHP_EOL);
				else fwrite($handle, '  <link>'.str_replace('&', '--and--', $entry->link).'</link>'.PHP_EOL);
				fwrite($handle, '  <guid>'.cleanup_str($guid).'</guid>'.PHP_EOL);
				fwrite($handle, '  <created>'.$entry->created.'</created>'.PHP_EOL);
				if (isset($assotext)) fwrite($handle, '  <assotext><id>'.cleanup_str($assotext).'</id><comment>'.cleanup_str($assotextcomment).'</comment><pubDate>'.date("r", time()).'</pubDate></assotext>'.PHP_EOL);
				foreach ($entry->assotext as $edit){
					if (!(cleanup_str($assotext) == strval($edit->id))) fwrite($handle, '  <assotext><id>'.$edit->id.'</id><comment>'.$edit->comment.'</comment><pubDate>'.$edit->pubDate.'</pubDate></assotext>'.PHP_EOL);
				}
				if ((isset($assoquot))and(!(isset($_GET['deletequote'])))) fwrite($handle, '  <quote><quote>'.cleanup_str($assoquot).'</quote><source>'.cleanup_str($assoquottext).'</source><pageno>'.cleanup_str($assoquotpageno).'</pageno><private>'.$assoquotprivate.'</private><comment>'.cleanup_str($assoquotcomment).'</comment><pubDate>'.date("r", time()).'</pubDate></quote>'.PHP_EOL);
				foreach ($entry->quote as $edit){
					if (cleanup_str($edit->quote) != $assoquot) fwrite($handle, '  <quote><quote>'.$edit->quote.'</quote><source>'.$edit->source.'</source><pageno>'.$edit->pageno.'</pageno><comment>'.$edit->comment.'</comment><private>'.$edit->private.'</private><pubDate>'.$edit->pubDate.'</pubDate></quote>'.PHP_EOL);
				}

				if ((isset($assodatasourcetitle))and(!(isset($_GET['deletedatasource'])))) fwrite($handle, '  <datasource><title>'.cleanup_str($assodatasourcetitle).'</title><link>'.cleanup_str($assodatasourcelink).'</link><comment>'.cleanup_str($assodatasourcecomment).'</comment><pubDate>'.date("r", time()).'</pubDate></datasource>'.PHP_EOL);
				foreach ($entry->datasource as $edit){
					if (cleanup_str($edit->title) != $assodatasourcetitle) fwrite($handle, '  <datasource><title>'.$edit->title.'</title><link>'.$edit->link.'</link><comment>'.$edit->comment.'</comment><pubDate>'.$edit->pubDate.'</pubDate></datasource>'.PHP_EOL);
				}
			fwrite($handle, '</item>'.PHP_EOL);
			
		}
		else {
			fwrite($handle, ' <item>'.PHP_EOL);
				fwrite($handle, '  <title>'.$entry->title.'</title>'.PHP_EOL);
				fwrite($handle, '  <link>'.str_replace('&', '--and--', $entry->link).'</link>'.PHP_EOL);
				fwrite($handle, '  <guid>'.$entry->guid.'</guid>'.PHP_EOL);
				fwrite($handle, '  <created>'.$entry->created.'</created>'.PHP_EOL);
				foreach ($entry->assotext as $edit){
					fwrite($handle, '  <assotext><id>'.$edit->id.'</id><comment>'.$edit->comment.'</comment><pubDate>'.$edit->pubDate.'</pubDate></assotext>'.PHP_EOL);
				}
				foreach ($entry->quote as $edit){
					fwrite($handle, '  <quote><quote>'.$edit->quote.'</quote><source>'.$edit->source.'</source><pageno>'.$edit->pageno.'</pageno><comment>'.$edit->comment.'</comment><private>'.$edit->private.'</private><pubDate>'.$edit->pubDate.'</pubDate></quote>'.PHP_EOL);
				}
				foreach ($entry->datasource as $edit){
					fwrite($handle, '  <datasource><title>'.$edit->title.'</title><link>'.$edit->link.'</link><comment>'.$edit->comment.'</comment><pubDate>'.$edit->pubDate.'</pubDate></datasource>'.PHP_EOL);
				}
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
	 
	$handle = fopen('./projects/'.$username.'.xml', 'w');
  
  fwrite($handle, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);
  fwrite($handle, '<rss version="2.0">'.PHP_EOL);
  
  fwrite($handle, '<channel>'.PHP_EOL);
  fwrite($handle, ' <lastBuildDate>'.date("r", time()).'</lastBuildDate>'.PHP_EOL);
	
	fwrite($handle, PHP_EOL.PHP_EOL);
  

	fwrite($handle, PHP_EOL);
	
	fwrite($handle, '<item>'.PHP_EOL);
	
		fwrite($handle, '  <title>'.cleanup_str($title).'</title>'.PHP_EOL);
		$newguid =intval($x->channel->item[0]->guid)+ intval(1);
		fwrite($handle, '  <guid>'.$newguid.'</guid>'.PHP_EOL);
		fwrite($handle, '  <created>'.date("r", time()).'</created>'.PHP_EOL);
				
	fwrite($handle, '</item>'.PHP_EOL);
			
	foreach($x->channel->item as $entry) {
		
		fwrite($handle, ' <item>'.PHP_EOL);
		fwrite($handle, '  <title>'.$entry->title.'</title>'.PHP_EOL);
		fwrite($handle, '  <link>'.str_replace('&', '--and--', $entry->link).'</link>'.PHP_EOL);
		fwrite($handle, '  <guid>'.$entry->guid.'</guid>'.PHP_EOL);
		fwrite($handle, '  <created>'.$entry->created.'</created>'.PHP_EOL);
		foreach ($entry->assotext as $edit){
			fwrite($handle, '  <assotext><id>'.$edit->id.'</id><comment>'.$edit->comment.'</comment><pubDate>'.$edit->pubDate.'</pubDate></assotext>'.PHP_EOL);
		}
		foreach ($entry->quote as $edit){
			fwrite($handle, '  <quote><quote>'.$edit->quote.'</quote><source>'.$edit->source.'</source><pageno>'.$edit->pageno.'</pageno><comment>'.$edit->comment.'</comment><private>'.$edit->private.'</private><pubDate>'.$edit->pubDate.'</pubDate></quote>'.PHP_EOL);
		}
		foreach ($entry->datasource as $edit){
			fwrite($handle, '  <datasource><title>'.$edit->title.'</title><link>'.$edit->link.'</link><comment>'.$edit->comment.'</comment><pubDate>'.$edit->pubDate.'</pubDate></datasource>'.PHP_EOL);
		}
		fwrite($handle, ' </item>'.PHP_EOL);
		fwrite($handle, PHP_EOL);
	
	}
	
	fwrite($handle, PHP_EOL);
	fwrite($handle, '</channel>'.PHP_EOL);
	fwrite($handle, '</rss>'.PHP_EOL);
  
	fclose($handle);
}

chmod ('./projects/'.$username.'.xml', 0777);
 
header ('location: ./projects.php');

?>

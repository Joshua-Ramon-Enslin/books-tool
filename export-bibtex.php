<?PHP

if (isset($_GET['q'])) $q = $_GET['q'];
if ((isset($_GET['peek']))and($_GET['peek'] != '')) $peek = $_GET['peek'];

if ((isset($_GET['s']))and(trim($_GET['s']) != '')) { $s = $_GET['s']; $searchstatus = True; }
if ((isset($_GET['au']))and(trim($_GET['au']) != '')) { $au = $_GET['au']; $searchstatus = True; }
if ((isset($_GET['ti']))and(trim($_GET['ti']) != '')) { $ti = $_GET['ti']; $searchstatus = True; }
if ((isset($_GET['jo']))and(trim($_GET['jo']) != '')) { $jo = $_GET['jo']; $searchstatus = True; }
if ((isset($_GET['pb']))and(trim($_GET['pb']) != '')) { $pb = $_GET['pb']; $searchstatus = True; }
if (isset($_GET['timestart'])) { $timestart = $_GET['timestart']; $searchstatus = True; }
else $timestart = date('1970-01-01');
if (isset($_GET['timeend'])) { $timeend = $_GET['timeend']; $searchstatus = True; }
else $timeend = (intval(date('Y'))+1).'-'.date('m-d');

/* Set general times */
$genstartTimeStamp = strtotime($timestart);
$genendTimeStamp = strtotime($timeend);

if (isset($_GET['p'])) $p = $_GET['p'];
else $p = 1;

if (!(isset($peek))) include ('password_protect.php');
include ('first.php');

$pagperpage = 10;

$tasklists = array();

/* Usernames */
if (isset($_SESSION['username'])) $username = $_SESSION['username'];
if (isset($peek)) $username = $peek;
if (!(isset($username))) header('location: ./password_protect.php?logout=yes');
if (!(is_dir('./allbooks/'))) mkdir('./allbooks/');
if (!(is_dir('./media/'))) mkdir('./media/'); if (!(is_dir('./media/books/'))) mkdir('./media/books/'); if (!(is_dir('./media/sources/'))) mkdir('./media/sources/');
if (!(file_exists('./tasks/'.$username.'.xml'))) { $handle = fopen('./tasks/'.$username.'.xml', 'w'); fwrite($handle, '<rss><channel></channel></rss>'.PHP_EOL); fclose($handle); }

/* Create archive & settings files if not existent */
if (!(file_exists('./settings/'.$username.'.xml'))) { $handle = fopen('./settings/'.$username.'.xml', 'w'); fwrite($handle, '<rss></rss>'.PHP_EOL); fclose($handle); }

if (!(isset($peek))) {
	$settingscontent = file_get_contents('./settings/'.$username.'.xml');
	$settingsxml = new SimpleXmlElement($settingscontent);
}

checkdirrec('./allbooks', array('bib'));
asort($tasklists);

$content = file_get_contents('./tasks/'.$username.'.xml');
$tasks = new SimpleXmlElement($content);

if (!isset($_GET['all'])) {
	$taskselecter = array();
	if (isset($q)) $taskselecter[] = $q;
	else {
		foreach ($tasks->channel->item as $entry) $taskselecter[] = strval($entry->title);
	}
}

include ('readbibtex.php');

header("Content-Type: text/plain; charset=utf-8"); 

if (!(isset($_GET['all']))){ 
	foreach ($bibentries as $key => $value) {
		
	echo '@'.$bibentries[$key]['kind'].'{'.$key.','.PHP_EOL;

		if (isset($value['title'])and($value['title'] != '')) echo '  Title = {'.$value['title'].'},'.PHP_EOL;
		if (isset($value['name'])and($value['name'] != '')) echo '  Author = {'.$value['name'].'},'.PHP_EOL;
		if (isset($value['editor'])and($value['editor'] != '')) echo '  Editor = {'.$value['editor'].'},'.PHP_EOL;
		if (isset($value['journal'])and($value['journal'] != '')) echo '  Journal = {'.$value['journal'].'},'.PHP_EOL;
		if (isset($value['year'])and($value['year'] != '')) echo '  Year = {'.$value['year'].'},'.PHP_EOL;
		if (isset($value['number'])and($value['number'] != '')) echo '  Number = {'.$value['number'].'},'.PHP_EOL;
		if (isset($value['pages'])and($value['pages'] != '')) echo '  Pages = {'.$value['pages'].'},'.PHP_EOL;
		if (isset($value['volume'])and($value['volume'] != '')) echo '  Volume = {'.$value['volume'].'},'.PHP_EOL;
		if (isset($value['doi'])and($value['doi'] != '')) echo '  Doi = {'.$value['doi'].'},'.PHP_EOL;
		if (isset($value['isbn'])and($value['isbn'] != '')) echo '  Isbn = {'.$value['isbn'].'},'.PHP_EOL;
		if (isset($value['publisher'])and($value['publisher'] != '')) echo '  Publisher = {'.$value['publisher'].'},'.PHP_EOL;
		if (isset($value['booktitle'])and($value['booktitle'] != '')) echo '  Booktitle = {'.$value['booktitle'].'},'.PHP_EOL;
		if (isset($value['school'])and($value['school'] != '')) echo '  School = {'.$value['school'].'},'.PHP_EOL;
		if (isset($value['chapter'])and($value['chapter'] != '')) echo '  Chapter = {'.$value['chapter'].'},'.PHP_EOL;
		if (isset($value['abstract'])and($value['abstract'] != '')) echo '  Abstract = {'.$value['abstract'].'},'.PHP_EOL;
		if (isset($value['keywords'])and($value['keywords'] != '')) { echo '  Keywords = {'; foreach ($value['keywords'] as $keyword) echo $keyword.PHP_EOL; echo '},'.PHP_EOL; }

	echo '}'.PHP_EOL;
		
	}
}
else {
	foreach ($bibentries as $value) {
		
	echo '@'.$value['kind'].'{'.$value['identifier'].','.PHP_EOL;

		if (isset($value['title'])and($value['title'] != '')) echo '  Title = {'.$value['title'].'},'.PHP_EOL;
		if (isset($value['name'])and($value['name'] != '')) echo '  Author = {'.$value['name'].'},'.PHP_EOL;
		if (isset($value['editor'])and($value['editor'] != '')) echo '  Editor = {'.$value['editor'].'},'.PHP_EOL;
		if (isset($value['journal'])and($value['journal'] != '')) echo '  Journal = {'.$value['journal'].'},'.PHP_EOL;
		if (isset($value['year'])and($value['year'] != '')) echo '  Year = {'.$value['year'].'},'.PHP_EOL;
		if (isset($value['number'])and($value['number'] != '')) echo '  Number = {'.$value['number'].'},'.PHP_EOL;
		if (isset($value['pages'])and($value['pages'] != '')) echo '  Pages = {'.$value['pages'].'},'.PHP_EOL;
		if (isset($value['volume'])and($value['volume'] != '')) echo '  Volume = {'.$value['volume'].'},'.PHP_EOL;
		if (isset($value['doi'])and($value['doi'] != '')) echo '  Doi = {'.$value['doi'].'},'.PHP_EOL;
		if (isset($value['isbn'])and($value['isbn'] != '')) echo '  Isbn = {'.$value['isbn'].'},'.PHP_EOL;
		if (isset($value['publisher'])and($value['publisher'] != '')) echo '  Publisher = {'.$value['publisher'].'},'.PHP_EOL;
		if (isset($value['booktitle'])and($value['booktitle'] != '')) echo '  Booktitle = {'.$value['booktitle'].'},'.PHP_EOL;
		if (isset($value['school'])and($value['school'] != '')) echo '  School = {'.$value['school'].'},'.PHP_EOL;
		if (isset($value['chapter'])and($value['chapter'] != '')) echo '  Chapter = {'.$value['chapter'].'},'.PHP_EOL;
		if (isset($value['abstract'])and($value['abstract'] != '')) echo '  Abstract = {'.$value['abstract'].'},'.PHP_EOL;
		if (isset($value['keywords'])and($value['keywords'] != '')) { echo '  Keywords = {'; foreach ($value['keywords'] as $keyword) echo $keyword.PHP_EOL; echo '},'.PHP_EOL;
		}

	echo '}'.PHP_EOL;
		
	}	
}

?>




<?PHP

?>

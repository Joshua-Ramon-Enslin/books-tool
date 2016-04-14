<?PHP

/* Get GET variables */
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

/* Load the settings file (if not viewed by another user) */
if (!(isset($peek))) {
	$settingscontent = file_get_contents('./settings/'.$username.'.xml');
	$settingsxml = new SimpleXmlElement($settingscontent);
}

/* Get all files containing bibliographical information */
checkdirrec('./allbooks', array('bib'));
asort($tasklists);

/* Read the user's reading information */
$content = file_get_contents('./tasks/'.$username.'.xml');
$tasks = new SimpleXmlElement($content);

/* Create array with all the texts a user has added. Bibliographical information will only be read for these texts. */
$taskselecter = array();
if (isset($q)) $taskselecter[] = $q;
else {
	foreach ($tasks->channel->item as $entry) $taskselecter[] = strval($entry->title);
}
include ('readbibtex.php');

header("Content-Type: text/plain; charset=utf-8"); 

/* Create output variable, then enter information to it */
$alltasks = array();
foreach ($tasks->channel->item as $entry){
	
	if (!isset($bibentries[strval($entry->title)])) continue;

	$entryedits = array();
	$entryquotes = array();

	/* Check if the user whose information is output is also logged in as the same user. If not, only limited information on the reading progress will be output. */
	if (!(isset($peek))) {
		foreach ($entry->edit as $edit){
			$entryedits[] = array (
				'before' => strval($edit->before),
				'after' => strval($edit->after),
				'pubDate' => strval($edit->pubDate)
			);
		}
	}
	else $entryedits[] = '';
	
	/* Enter all quotes from the text to a variable */
	foreach ($entry->quote as $quote){
		$entryquotes[] = array (
			'quote' => reconstruct_html(strval($quote->quote)),
			'pagenumber' => strval($quote->pageno),
			'pubDate' => strval($quote->pubDate)
		);
	}
	
	/* Add information on the text and the previously created variables for quotes and changes made to reading progress to the output variable */
	$alltasks[strval($entry->title)] = array(
		'id' => strval($entry->title),
		'progress' => strval($entry->title),
		'created' => strval($entry->created),
		'edits' => $entryedits,
		'quotes' => $entryquotes
	);
	
	unset ($entryedits);
	unset ($entryquotes);
}

if (isset($q)){
	$alltasks = array(0 => $alltasks[$q]);
}

/* Output */
echo json_encode ($alltasks);	
?>

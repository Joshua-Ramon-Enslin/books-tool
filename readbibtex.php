<?PHP

/* This file can be included to extract bibliographic data from a plain text BibTeX, .bib, file. */

if (!(isset($username))){

	include ('password_protect.php');
	include ('first.php');

	$tasklists = array();

	/* Usernames */
	if (isset($_SESSION['username'])) $username = $_SESSION['username'];
	if (!(isset($username))) header('location: ./password_protect.php?logout=yes');
	if (!(is_dir('./allbooks/'))) mkdir('./allbooks/');
	if (!(file_exists('./tasks/'.$username.'.xml'))) { $handle = fopen('./tasks/'.$username.'.xml', 'w'); fwrite($handle, '<rss></rss>'.PHP_EOL); fclose($handle); }

	checkdirrec('./allbooks/', array('bib'));
	asort($tasklists);
}

function splitbib ($str, $selection = array()) {

	if (count($selection) > 0) $filterinfo = True;
	else $filterinfo = False;

	$str = trim($str);
	$toreturn = array();
	$pos1 = 0; $pos2 = 0;

	$toreturn['kind'] = substr($str, 1, $pos1 = strpos($str, '{') - 1);
	$pos2 = strpos($str, ',');
	$toreturn['identifier'] = substr($str, $pos1 + 2, $pos2 - $pos1 - 2);

	$noofthings = substr_count($str, '},');
	for ($i = 0; $i <= $noofthings; $i++) {
	
		$pos1 = strpos($str, '=', $pos2) + 3;	
		$pos3 = strpos($str, '}', $pos2);

		$key = strtolower(trim(substr($str, $pos2 + 2, $pos1 - $pos2 - 5)));
		if ($filterinfo == False or in_array($key, $selection)) $toreturn [$key] = substr($str, $pos1, $pos3 - $pos1);

		$pos2 = $pos3 + 1;
	
	}
	if (isset($toreturn['keywords'])) $toreturn['keywords'] = explode(PHP_EOL, $toreturn['keywords']);
	return ($toreturn);

}

$bibentries = array();

if (!isset($taskselecter)) { $taskselecter = array(); $taskselectall = True; }
else $taskselectall = False;

if (!isset($selecter)) $selecter = 'all';
if ($selecter == 'all' or $selecter == 'keywordsplusone') $selection = array();
else if ($selecter == 'author') $selection = array('identifier', 'title', 'author');
else if ($selecter == 'keywords') $selection = array('identifier', 'keywords');
else if ($selecter == 'basicinfo') $selection = array('identifier', 'title', 'author', 'journal', 'pages', 'isbn', 'kind', 'abstract', 'year', 'publisher', 'keywords');
else if ($selecter == 'basicinfoplus') $selection = array('identifier', 'title', 'author', 'journal', 'pages', 'isbn', 'kind', 'abstract', 'year', 'publisher', 'keywords');
else if ($selecter == 'onlyjournal') $selection = array('journal');
else if ($selecter == 'onlyname') $selection = array('author');
else if ($selecter == 'onlypublisher') $selection = array('publisher');
else if ($selecter == 'onlytitle') $selection = array('title');

if (isset($_GET['selfromlist'])) $selfromlist = True;
else $selfromlist = False;

if (!isset($showonlyunread)) $showonlyunread = False;

if (!isset($searchstatus)) $searchstatus = False;

foreach ($tasklists as $file) {
	
	$filebasename = basename($file);

	if (isset($sources) and !in_array($filebasename, $sources)) continue;
	if (!file_exists($file.'.bib')) continue;

	$content = str_replace('\&', 'and', str_replace('–', '-', file_get_contents('./'.$file.'.bib')));

	$pos1 = strpos($content, PHP_EOL.'@');
	$pos2 = strpos($content, PHP_EOL.'@', $pos1 + 2);


	$noofthings = substr_count($content, PHP_EOL.'@');

	for ($i = 0; $i < $noofthings; $i++) {

		# Get identifier
		$identifier = substr($content, strpos($content, '{', $pos1) + 1, strpos($content, ',', $pos1) - strpos($content, '{', $pos1) - 1).'_'.$filebasename;

		if ($showonlyunread == False) { 

		if ($taskselectall == True or in_array($identifier, $taskselecter)) {
			$bibentries[$identifier] = splitbib(substr($content, $pos1, $pos2 - $pos1), $selection);
			
			if ($searchstatus == True) {
				if (isset($s) and ((!isset($bibentries[$identifier]['keywords'])) or (!in_array($s, $bibentries[$identifier]['keywords'])))) { unset($bibentries[$identifier]); }
				if (isset($au) and ((!isset($bibentries[$identifier]['author'])) or (strpos(' '.$bibentries[$identifier]['author'], $au) == False))) { unset($bibentries[$identifier]); }
				if (isset($ti) and ((!isset($bibentries[$identifier]['title'])) or (strpos(' '.$bibentries[$identifier]['title'], $ti) == False))) { unset($bibentries[$identifier]); }
				if (isset($jo) and ((!isset($bibentries[$identifier]['journal'])) or (strpos(' '.$bibentries[$identifier]['journal'], $jo) == False))) { unset($bibentries[$identifier]); }
				if (isset($pb) and ((!isset($bibentries[$identifier]['publisher'])) or (strpos(' '.$bibentries[$identifier]['publisher'], $pb) == False))) { unset($bibentries[$identifier]); }
			}
		}
		else if ($selfromlist == True) $bibentries[$identifier] = splitbib(substr($content, $pos1, $pos2 - $pos1), array('title'));
		
		}
		else {

		if (!in_array($identifier, $taskselecter)) {

			$bibentries[$identifier] = splitbib(substr($content, $pos1, $pos2 - $pos1), $selection);

			/* In the case of a search for only unread books, the search status is True either way */
			if (isset($s) and ((!isset($bibentries[$identifier]['keywords'])) or (!in_array($s, $bibentries[$identifier]['keywords'])))) { unset($bibentries[$identifier]); }
			if (isset($au) and ((!isset($bibentries[$identifier]['author'])) or (strpos(' '.$bibentries[$identifier]['author'], $au) == False))) { unset($bibentries[$identifier]); }
			if (isset($ti) and ((!isset($bibentries[$identifier]['title'])) or (strpos(' '.$bibentries[$identifier]['title'], $ti) == False))) { unset($bibentries[$identifier]); }
			if (isset($jo) and ((!isset($bibentries[$identifier]['journal'])) or (strpos(' '.$bibentries[$identifier]['journal'], $jo) == False))) { unset($bibentries[$identifier]); }
			if (isset($pb) and ((!isset($bibentries[$identifier]['publisher'])) or (strpos(' '.$bibentries[$identifier]['publisher'], $pb) == False))) { unset($bibentries[$identifier]); }

		}

		}

		$pos1 = $pos2;
		$pos2 = strpos($content, PHP_EOL.'@', $pos1 + 2);
	
	}
}

ksort ($bibentries);

?>

<?PHP

foreach ($tasks->channel->item as $entry){
	$entedits = array();

	if (!isset($bibentries[strval($entry->title)]['pages']) or !isset($bibentries[strval($entry->title)]['author'])) continue;


	if (isset($bibentries[strval($entry->title)]['keywords'])) {

	if (isset($entdate)) unset ($entdate); $entdate = strtotime(cleandate($entry->created));
	if ((isset($genstartTimeStamp) and isset($genendTimeStamp)) and !(($entdate < $genendTimeStamp)and($entdate > $genstartTimeStamp))) continue;
					
	if ((isset($_GET['unfinishedonly']))and($_GET['unfinishedonly'] == 1)){
	if (strpos(' '.$bibentries[strval($entry->title)]['pages'], '-') > 0) {
		$allpages = explode('-', $bibentries[strval($entry->title)]['pages']);
		$completepage = $allpages[1];
	}
	else $completepage = $bibentries[strval($entry->title)]['pages'];
	if ((isset($bibentries[strval($entry->title)]['pages']))and(strval($entry->progress) == $completepage)) continue;
	}

	foreach ($entry->edit as $entryedits) {
		$entedits[] = array ('before' => intval($entryedits->before), 'after' => intval($entryedits->after), 'pubDate' => strval($entryedits->pubDate), 'difference' => intval($entryedits->after) - intval($entryedits->before));
	}

	if (isset($bibentries[strval($entry->title)]) and strpos(' '.$bibentries[strval($entry->title)]['pages'], '-') > 0) {
		$allpages = explode('-', $bibentries[strval($entry->title)]['pages']);
		$complete = intval($allpages[1]) - intval($allpages[0]);
	}
	else $complete = intval($bibentries[strval($entry->title)]['pages']);

	$allbooks[strval($entry->title)] = array(
		'title' => $bibentries[strval($entry->title)]['author'],
		'categories' => $bibentries[strval($entry->title)]['keywords'],
		'created' => strval($entry->created),
		'pages' => $complete,
		'edits' => $entedits
	);
	$booklengths[] = $complete;
	}
	
	if (isset($entedits)) unset ($entedits); if (isset($complete)) unset ($complete);
	

}

?>

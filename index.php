<?PHP
/* Basic variables */
if (isset($_GET['q'])) $q = $_GET['q'];
if ((isset($_GET['peek']))and($_GET['peek'] != '')) $peek = $_GET['peek'];
if (isset($_GET['edit'])) $edt = $_GET['edit'];


/* Check and get search variables */
if (!isset($q)) {
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

	if (!isset($searchstatus)) $searchstatus = False;

	/* Get pagination variable */
	if (isset($_GET['p'])) $p = $_GET['p'];
	else $p = 1;
	if ((isset($_GET['unfinishedonly']))or(isset($s))or(isset($au))or(isset($ti))or(isset($jo))or(isset($pb))) $pagperpage = 10000;
	else $pagperpage = 10;

	/* URL referral to keep search bar from navigation running */
	if (isset($s) and isset($_GET['searchkey']) and $_GET['searchkey'] != 's') header ('Location: ./?'.$_GET['searchkey'].'='.$s);

}

/* Include essential building blocks */
if (!(isset($peek))) require ('password_protect.php');
require ('first.php');

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
if (!(is_dir('./allbooks/'.$username.'/'))) mkdir('./allbooks/'.$username.'/');

/* Create directory for additional data (on publishers and journals) */
if (!(is_dir('./contextdata/'))) mkdir('./contextdata/');
if (!(is_dir('./contextdata/publishers/'))) mkdir('./contextdata/publisher/');
if (!(is_dir('./contextdata/journal/'))) mkdir('./contextdata/journal/');
if (!(is_dir('./contextdata/authors/'))) mkdir('./contextdata/author/');

if (!(isset($peek))) {
	$settingscontent = file_get_contents('./settings/'.$username.'.xml');
	$settingsxml = new SimpleXmlElement($settingscontent);
}

checkdirrec('./allbooks', array('bib')); 
asort($tasklists);

$content = file_get_contents('./tasks/'.$username.'.xml');
$tasks = new SimpleXmlElement($content);

/* Limit loading bibliographical information to what is needed. */
if (!isset($q)) {

	/* Pagination */
	$totalnopages = round(count($tasks->channel->item) / intval($pagperpage));
	$pagcurrent = array(0 + $pagperpage * ($p - 1), 0 - 1 + $pagperpage * $p);

	if (!isset($s) and !isset($jo)) $deepersearch = 1;

	$taskselecter = array();
	/* Select the entries to be displayed */
	
	$sources = array();
	for ($i = $pagcurrent[0]; $i <= $pagcurrent[1]; $i++) {
		if (isset($tasks->channel->item[$i])) { 
			$taskselecter[] = strval($tasks->channel->item[$i]->title);

			if (!in_array(substr(strval($tasks->channel->item[$i]->title), strrpos(strval($tasks->channel->item[$i]->title), '_') + 1), $sources)) $sources[] = substr(strval($tasks->channel->item[$i]->title), strrpos(strval($tasks->channel->item[$i]->title), '_') + 1);
		}
	}
}
else {
	$taskselecter = array($q);
	$sources = array(substr($q, strrpos($q, '_') + 1));
}

/* Select which part of the bibliographical information will be loaded */
if (!isset($q)) {
	if ($searchstatus == True) $selecter = 'basicinfoplus';
	else $selecter = 'basicinfo';
}

include ('readbibtex.php');

?>
<!DOCTYPE html>
<html manifest="manifest.php">
<head>
	<title><?PHP echo $pageinfo['title']; ?> :: Books :: <?PHP if (isset($q) and isset($bibentries[$q])) echo $bibentries[$q]['title']; ?></title>

	<?PHP 
	if (!isset($settingsxml->css) or $settingsxml->css == '') echo '<link rel="stylesheet" type="text/css" href="main.css" />';
	else echo '<link rel="stylesheet" type="text/css" href="'.strval($settingsxml->css).'" />';
	?>

	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=480, initial-scale=0.7" />
	<link rel="shortcut icon" href="./books.png" /> 

	<?PHP
	/* Tinymce */
	if ((isset($edt))or((isset($_GET['settings'])))){
		echo '<script src="./ext/tinymce4110/js/tinymce/tinymce.min.js"></script>';

		?>
		<script>tinymce.init({ 
			language : "en",
			plugins: ["anchor autolink code hr link image media"],
			skin: "charcoal",
			mode : "specific_textareas",
		    editor_selector : "mceEditor",
			content_css: "css/main.css",
			toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons"

		});</script>
		<?PHP
	}
	?>	
</head>

<body>

	<?PHP include ('navigation.php'); ?>

	<main>
		<?PHP

			if (!(isset($q))) { ?>

				<!-- General options -->
				<div class="optionbuttons">
					<?PHP if (!(isset($peek))) { ?>					
						<a href="./?settings=1" title="<?PHP echo $dict->settings->$lan; ?>">&#9881;</a>
						<a href="./?tools=1" title="<?PHP echo $dict->tools->$lan; ?>">&#128295;</a>
					<?PHP } ?>
					<?PHP 
					echo '<a href="./export-bibtex.php?s='; if (isset($s)) echo $s; if (isset($au)) echo '&au='.$au; if (isset($ti)) echo '&ti='.$ti; if (isset($jo)) echo '&jo='.$jo; if (isset($pb)) echo '&pb='.$pb; echo '&timestart='.$timestart.'&timeend='.$timeend.'&peek='; if (isset($peek)) echo $peek; echo '" title="'.$dict->exportbibtex->$lan.'">EB</a>';	
					echo '<a href="./export-progress.php?s='; if (isset($s)) echo $s; if (isset($au)) echo '&au='.$au; if (isset($ti)) echo '&ti='.$ti; if (isset($jo)) echo '&jo='.$jo; if (isset($pb)) echo '&pb='.$pb; echo '&timestart='.$timestart.'&timeend='.$timeend.'&peek='; if (isset($peek)) echo $peek; echo '" title="'.$dict->exportreadingdata->$lan.'">&#x1F35B;</a>';	
								
					if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) { ?>
					<div class="helphover">
						<h4>Options</h4>
						<p>Here you can find different additional functions. You may for example change your settings or export data through the links provided.</p> 
					</div>
					<?PHP } ?>
				</div>
				
				<?PHP
				/* Headline */
				if (!(isset($s))) echo '<h2>'.$dict->texts->$lan.'</h2>'.PHP_EOL;
				else if (isset($s)) echo '<h2>'.$dict->searchresultsfor->$lan.': <span style="font-style:italic;">'.$s.'</span></h2>'.PHP_EOL;

				if ($searchstatus == True) {
				/* Search form */
				?>
				<form class="advancedsearch" method="get" enctype="multipart/form-data" action="">
				<p><?PHP echo $dict->takingintoaccount->$lan; ?> 
					<input type="date" name="timestart" value="<?PHP echo $timestart ?>" />		
					<?PHP echo $dict->and->$lan; ?>		
					<input type="date" name="timeend" value="<?PHP echo $timeend ?>" />. 
					<?PHP echo $dict->filterbykeyword->$lan; ?>: <input type="text" name="s" value="<?PHP if (isset($s)) echo $s; ?>" placeholder="<?PHP echo $dict->emptynofilter->$lan; ?>" />
					<?PHP echo $dict->authors->$lan; ?>: <input type="text" name="au" value="<?PHP if (isset($au)) echo $au; ?>" placeholder="<?PHP echo $dict->emptynofilter->$lan; ?>" />
					<?PHP echo $dict->title->$lan; ?>: <input type="text" name="ti" value="<?PHP if (isset($ti)) echo $ti; ?>" placeholder="<?PHP echo $dict->emptynofilter->$lan; ?>" />
					<?PHP echo $dict->publisher->$lan; ?>: <input type="text" name="pb" value="<?PHP if (isset($pb)) echo $pb; ?>" placeholder="<?PHP echo $dict->emptynofilter->$lan; ?>" />
					<?PHP echo $dict->journal->$lan; ?>: <input type="text" name="jo" value="<?PHP if (isset($jo)) echo $jo; ?>" placeholder="<?PHP echo $dict->emptynofilter->$lan; ?>" />
					<br />Restrict search to unfinished entries?
					<input type="radio" value="1" name="unfinishedonly" id="unfinishedyes" /><label for="unfinishedyes"><?PHP echo $dict->yes->$lan; ?></label>
					<input type="radio" value="0" name="unfinishedonly" id="unfinishedno" /><label for="unfinishedno"><?PHP echo $dict->no->$lan; ?></label>
					<?PHP if (isset($peek)) echo '<input type="hidden" name="peek" value="'.$peek.'" />'; ?>
					<button type="submit"><?PHP echo $dict->change->$lan; ?></button>
				</p>
				</form>

				<?PHP
				}

				/* If a journal was searched for, that someone has previously entered information on, display that information. */
				if (isset($jo) and file_exists('contextdata/journal/'.str_replace(' ', '_', $jo).'.xml') == True) {
					$journalxml = new SimpleXmlElement(file_get_contents('contextdata/journal/'.str_replace(' ', '_', $jo).'.xml'));
					if (isset($journalxml)) { 
						echo '<div class="searchcontextinfo">';
							echo '<h4>'.$jo.'</h4>';
							echo '<dl>';
								if (isset($journalxml->description) and $journalxml->description != '') echo '<div><dt>Description</dt><dd>'.$journalxml->description.'</dd></div>';
								if (isset($journalxml->publisher) and $journalxml->publisher != '') echo '<div><dt>Publisher</dt><dd><a href="?pb='.$journalxml->publisher.'" title="Find books from this publisher">'.$journalxml->publisher.'</a></dd></div>';
								if (isset($journalxml->issn) and $journalxml->issn != '') echo '<div><dt><abbr title="International Standard Serial Number">ISSN</abbr></dt><dd>'.$journalxml->issn.'</dd></div>';
								if (isset($journalxml->website) and $journalxml->website != '') echo '<div><dt>Website</dt><dd><a href="'.$journalxml->website.'" title="Visit journal website">'.$journalxml->website.'</a></dd></div>';
								if (isset($journalxml->wikipedia) and $journalxml->wikipedia != '') echo '<div><dt>Wikipedia</dt><dd><a href="'.$journalxml->wikipedia.'">'.$journalxml->wikipedia.'</a></dd></div>';
							echo '</dl>';
						echo '</div>';
						unset($journalxml);
					}
				}

				/* Output */
				echo '<ul class="overviewlist">';			
				$i = 0;	
				$allbooks = array();
				foreach ($tasks->channel->item as $entry) {
					$allbooks[] = strval($entry->title);
					if (($i >= $pagcurrent[0])and($i <= $pagcurrent[1])){					
						
					/* Search function filter (slightly different version to be found in rediallbooks.php) */

					if (isset($entdate)) unset ($entdate); $entdate = strtotime(cleandate($entry->created));
					if (!(($entdate < $genendTimeStamp)and($entdate > $genstartTimeStamp))) continue;
					if ($searchstatus == True and !isset($bibentries[strval($entry->title)])) continue;
					
					if ((isset($_GET['unfinishedonly']))and($_GET['unfinishedonly'] == 1)){
						if (strpos(' '.$bibentries[strval($entry->title)]['pages'], '-') > 0) {
							$allpages = explode('-', $bibentries[strval($entry->title)]['pages']);
							$completepage = $allpages[1];
						}
						else $completepage = $bibentries[strval($entry->title)]['pages'];
						if ((isset($bibentries[strval($entry->title)]['pages']))and(strval($entry->progress) == $completepage)) continue;
					}

					echo '<li>';
					echo '<a href="./?q='.$entry->title; if (isset($peek)) echo '&peek='.$peek; echo '">';
					if (isset($bibentries[strval($entry->title)])){
						echo '<div class="imgdiv">';
						if ($bibentries[strval($entry->title)]['kind'] == 'Book') { 
							if (file_exists('./media/books/'.strval($entry->title).'.jpg')) echo '<img title="'.$dict->bookcoverof->$lan.' '.$bibentries[strval($entry->title)]['title'].'" alt="'.$dict->bookcoverof->$lan.' '.strval($entry->title).'" src="./media/books/'.strval($entry->title).'.jpg" />';
						}
						else if (isset($bibentries[strval($entry->title)]['journal']) and file_exists('./media/sources/'.str_replace(':', '_', str_replace(' ', '_', $bibentries[strval($entry->title)]['journal'])).'.jpg')) echo '<img title="'.$dict->bookcoverof->$lan.' '.$bibentries[strval($entry->title)]['title'].'" alt="'.$dict->bookcoverof->$lan.' '.strval($entry->title).'" src="./media/sources/'.str_replace(':', '_', str_replace(' ', '_', $bibentries[strval($entry->title)]['journal'])).'.jpg" />';
						else if (($bibentries[strval($entry->title)]['kind'] == 'InBook')and(isset($bibentries[strval($entry->title)]['isbn']))and(isset($isbns[($bibentries[strval($entry->title)]['isbn'])]))
								and(file_exists('./media/books/'.strval($isbns[($bibentries[strval($entry->title)]['isbn'])]).'.jpg'))){
								echo '<img title="'.$dict->bookcoverof->$lan.' '.strval($isbns[($bibentries[strval($entry->title)]['isbn'])]).'" alt="Book cover of '.strval($isbns[($bibentries[strval($entry->title)]['isbn'])]).'" src="./media/books/'.strval($isbns[($bibentries[strval($entry->title)]['isbn'])]).'.jpg" />';
						}
						echo '</div>';						
						echo '<div class="contdiv">';	
							echo '<h3>'.$bibentries[strval($entry->title)]['title'].'</h3>';
							echo '<p>';
							if (isset($bibentries[strval($entry->title)]['author'])) echo $bibentries[strval($entry->title)]['author'].' ';
							if (isset($bibentries[strval($entry->title)]['year'])) echo '('.$bibentries[strval($entry->title)]['year'].')';
							echo '</p>'; 
							if (isset($bibentries[strval($entry->title)]['abstract']) and $bibentries[strval($entry->title)]['abstract'] != '') {
								echo '<p id="abstract">'.substr($bibentries[strval($entry->title)]['abstract'], 0, 200).'[...]</p>';
							}
							
						if (isset($bibentries[strval($entry->title)]['pages']) and $bibentries[strval($entry->title)]['pages'] != '') {
							echo '<p style="margin-bottom:0.5em;">'.$dict->progress->$lan.': '.$entry->progress.' / '.$bibentries[strval($entry->title)]['pages'].' ('.$dict->lastupdated->$lan.': <time>'.cleandate($entry->edit->pubDate).'</time>)</p>';
									
?>						
							<div class="drawprogress" style="height:20px;" >
								<?PHP
								/* Calculate percentage of progress */
								$currentpage = intval($entry->progress);
								if ($bibentries[strval($entry->title)]['pages'] != '') {
									if (strpos(' '.$bibentries[strval($entry->title)]['pages'], '-') > 0) {
										$allpages = explode('-', $bibentries[strval($entry->title)]['pages']);
										$complete = intval($allpages[1]) - intval($allpages[0]);
										
										$currentpage = $currentpage - intval ($allpages[0]);
									}
									else $complete = intval($bibentries[strval($entry->title)]['pages']);
								
									$onepage = 100 / intval($complete);
									$currentprogress = $currentpage * $onepage;
								}
								?>

								<div style="width:<?PHP echo $currentprogress; ?>%;" class="<?PHP if (round($currentprogress) == 100) echo 'completelyfinished'; ?>" id="finished"></div>
								<div style="width:<?PHP echo (100 - $currentprogress); ?>%;" id="unfinished"></div>
							</div>
						<?PHP
						}
						if (isset($allpages)) unset ($allpages); if (isset($complete)) unset ($complete); 
						echo '</div>';
					}
					else echo '<h3>'.$entry->title.'</h3><p class="noinfoyet">'.$dict->noinfoyet->$lan.'</p>';
					echo '</a>';
					echo '</li>';
					}
					$i++;						
				}
				unset ($i);
				echo '</ul>';
			
			if (!isset($s) and !isset($au) and !isset($ti) and !isset($jo) and !isset($pb)) {
				/* Pagination: switch page */
				echo '<div class="pagination">';
					if ($p != 1) { echo '<a href="./?p=1'; if (isset($peek)) echo '&peek='.$peek; echo '">1</a>'; }
					if ($p - 2 > 1 and $p - 2 < $totalnopages) { echo '<a href="./?p='.($p - 2); if (isset($peek)) echo '&peek='.$peek; echo '">'.($p - 2).'</a>'; }
					if ($p - 1 > 1 and $p - 1 < $totalnopages) { echo '<a href="./?p='.($p - 1); if (isset($peek)) echo '&peek='.$peek; echo '">'.($p - 1).'</a>'; }
				
					echo '<a href="./?p='.($p); if (isset($peek)) echo '&peek='.$peek; echo '" class="pagination-selected">'.($p).'</a>';
				
					if ($p + 1 > 1 and $p + 1 < $totalnopages) { echo '<a href="./?p='.($p + 1); if (isset($peek)) echo '&peek='.$peek; echo '">'.($p + 1).'</a>'; }
					if ($p + 2 > 1 and $p + 2 < $totalnopages) { echo '<a href="./?p='.($p + 2); if (isset($peek)) echo '&peek='.$peek; echo '">'.($p + 2).'</a>'; }
					if ($p != $totalnopages) { echo '<a href="./?p='.$totalnopages; if (isset($peek)) echo '&peek='.$peek; echo '">'.($totalnopages).'</a>'; }
				echo '</div>';
				}
			}
			else {
	
				$allbooks = array();
				if (isset($entry)) unset($entry);
				foreach ($tasks->channel->item as $item) {
					# $entry contains the reading information
					if (isset($item->title) and strval($item->title) == strval($q)) $entry = $item;
					# $allbooks contains the IDs of all the texts the user has added.
					$allbooks[strval($item->title)] = '';
				}

				/* General options */
				echo '<div class="optionbuttons">'.PHP_EOL;
					if (!(isset($peek))){					
						if (!((isset($_GET['deleteconfirm']))and($_GET['deleteconfirm'] == 1))) echo '<a href="./?q='.$q.'&amp;deleteconfirm=1" title="Delete">-</a>'.PHP_EOL;
						echo '<a href="./?q='.$q.'&amp;settings=1" title="'.$dict->settings->$lan.'">&#9881;</a>';
						echo '<a href="./?tools=1" title="'.$dict->tools->$lan.'">&#128295;</a>';
					}
					echo '<a href="./export-bibtex.php?q='.$q.'&peek='; if (isset($peek)) echo $peek; echo '" title="'.$dict->exportbibtex->$lan.'">EB</a>';		
					echo '<a href="./export-progress.php?q='.$q.'&peek='; if (isset($peek)) echo $peek; echo '" title="'.$dict->exportreadingdata->$lan.'">&#x1F35B;</a>';
				echo '</div>'.PHP_EOL;
				
				?>
				<!-- Output -->
				<ul class="tasklist">
					
					<li class="taskslisting" <?PHP if (isset($entry)) 'id="'.$entry->guid.'"'; ?>>

						<?PHP
						if ((!(isset($peek)))and(in_array($q, array_keys($allbooks)))) echo '<a class="options" href="./?q='.$q.'&amp;edit='.$entry->guid.'#desc'.$entry->guid.'">&#9997;</a>';
						if ((!(isset($peek)))and(!in_array($q, array_keys($allbooks)))) echo '<a class="options" href="./tasks.php?title='.$q.'">+</a>';
						if ((isset($edt))and($edt == strval($entry->guid))) {
							echo '<form class="edittitle" method="get" enctype="multipart/form-data" action="tasks.php">';
								echo '<input type="hidden" name="q" value="'.$q.'" />';
								echo '<input type="hidden" name="guid" value="'.$entry->guid.'" />';
								echo '<input type="text" name="title" placeholder="The title of the task" value="'.strval($entry->title).'" />';
								echo '<button type="submit">'.$dict->send->$lan.'</button>';
							echo '</form>';
						}
						else if (isset($bibentries[$q])) echo '<h2>'.$bibentries[$q]['title'].'</h2>';
						else echo '<h2>'.$q.'</h2>';
						/* echo '<a class="options" href="tasks.php?delete">-</a>'; */


						/* Displaying relevant bibliographical data */
						if (isset($bibentries[$q])) {
							echo '<dl class="bibinfo">';

							echo '<div id="basicinfo">'.PHP_EOL;
								if (isset($bibentries[$q]['author']) and $bibentries[$q]['author'] != '') {
										echo '<dt>'.$dict->authors->$lan.'</dt>'.PHP_EOL;
									echo '<dd><a href="?au='.$bibentries[$q]['author'].'">'.$bibentries[$q]['author'].'</a></dd>'.PHP_EOL;
								}	
								if (isset($bibentries[$q]['editor']) and $bibentries[$q]['editor'] != '') {
									echo '<dt>Editor</dt>'.PHP_EOL;
									echo '<dd>'.$bibentries[$q]['editor'];
									if (strval($bibentries[$q]['kind']) == 'InBook') '(> View book)';
									echo '</dd>'.PHP_EOL;
								}
								if (isset($bibentries[$q]['year']) and $bibentries[$q]['year'] != '') {
									echo '<dt>'.$dict->year->$lan.'</dt>'.PHP_EOL;
									echo '<dd>'.$bibentries[$q]['year'].'</dd>'.PHP_EOL;
								}
								if (isset($bibentries[$q]['kind']) and $bibentries[$q]['kind'] != '') {
									echo '<dt>'.$dict->kindoftext->$lan.'</dt>'.PHP_EOL;
									echo '<dd>'.$bibentries[$q]['kind'].'</dd>'.PHP_EOL;
								}
								if (isset($bibentries[$q]['pages']) and $bibentries[$q]['pages'] != '') {
									echo '<dt>'.$dict->pages->$lan.'</dt>'.PHP_EOL;
									echo '<dd>'.$bibentries[$q]['pages'];
											
									if (strpos(' '.$bibentries[$q]['pages'], '-') > 0) {
										$allpages = explode('-', $bibentries[$q]['pages']);
										echo ' <span style="border-bottom:1px dotted #333;">('.($allpages[1] - $allpages[0] + 1).')</span>';
									}
									echo '</dd>'.PHP_EOL;
								}
							echo '</div>'.PHP_EOL;

							/* Check for cover image */
							if ((file_exists('./media/books/'.$q.'.jpg'))or(isset($bibentries[$q]['journal']) and file_exists('./media/sources/'.str_replace(':', '_', str_replace(' ', '_', $bibentries[$q]['journal'])).'.jpg'))) {
								echo '<div id="cover">';
									echo '<figure>';
									if ($bibentries[$q]['kind'] == 'Book') {
										echo '<img title="'.$dict->bookcoverof->$lan.' '.$bibentries[$q]['title'].'" alt="Book cover of '.$q.'" src="./media/books/'.$q.'.jpg" />';
										echo '<figcaption>'.$dict->bookcover->$lan.'</figcaption>';
									}
									else {
										echo '<img title="'.$dict->coverof->$lan.' '.$bibentries[$q]['journal'].'" alt="Cover of '.$bibentries[$q]['journal'].'" src="./media/sources/'.str_replace(':', '_', str_replace(' ', '_', $bibentries[$q]['journal'])).'.jpg" />';
										echo '<figcaption>'.$dict->journallogo->$lan.'</figcaption>';
									}
									echo '</figure>';
								echo '</div>';
							}
							else if (($bibentries[$q]['kind'] == 'InBook')and(isset($bibentries[$q]['isbn']))and(isset($isbns[($bibentries[$q]['isbn'])]))
								and(file_exists('./media/books/'.strval($isbns[($bibentries[$q]['isbn'])]).'.jpg'))){
								echo '<div id="cover">';
									echo '<figure>';
										echo '<a href="./?q='.strval($isbns[($bibentries[$q]['isbn'])]).'"><img title="'.$dict->bookcoverof->$lan.' '.strval($isbns[($bibentries[$q]['isbn'])]).'" alt="Book cover of '.strval($isbns[($bibentries[$q]['isbn'])]).'" src="./media/books/'.strval($isbns[($bibentries[$q]['isbn'])]).'.jpg" /></a>';
										echo '<figcaption>'.$dict->bookcover->$lan.'</figcaption>';									
									echo '</figure>';
								echo '</div>';
							}
							
							/* If image is not given, check open library */
							else if (($bibentries[$q]['kind'] == 'Book')and isset($bibentries[$q]['isbn']) and $bibentries[$q]['isbn'] != '') {
								echo '<div id="cover"><figure><img src="http://covers.openlibrary.org/b/isbn/'.$bibentries[$q]['isbn'].'-M.jpg" /><figcaption>'.$dict->isthisbookcover->$lan.'?</figcaption>';
								echo '<form class="edittitle" method="get" enctype="multipart/form-data" action="_download-cover.php"><input type="hidden" name="covertodl" value="http://covers.openlibrary.org/b/isbn/'.$bibentries[$q]['isbn'].'-M.jpg" /><input type="hidden" name="id" value="'.$q.'" /><button>Yes</button></form>';
								echo '</figure></div>';
							}
								
							echo '<div id="publicationinfo">'.PHP_EOL;
								if (isset($bibentries[$q]['publisher']) and $bibentries[$q]['publisher'] != '') {
									echo '<dt>'.$dict->publisher->$lan.'</dt>'.PHP_EOL;
									echo '<dd>';
										echo '<a href="?pb='.$bibentries[$q]['publisher'].'">'.$bibentries[$q]['publisher'].'</a>';
										
										if (file_exists('contextdata/publisher/'.str_replace(' ', '_', $bibentries[$q]['publisher']).'.xml') == True) {
											$publisherxml = new SimpleXmlElement(file_get_contents('contextdata/publishers/'.str_replace(' ', '_', $bibentries[$q]['publisher']).'.xml'));
											if (isset($publisherxml)) { 
												print_r ($publisherxml);
												unset($publisherxml);
											}
										}
									echo '</dd>'.PHP_EOL;
								}

								if (isset($bibentries[$q]['journal']) and $bibentries[$q]['journal'] != '') {
									echo '<dt>'.$dict->journal->$lan.'</dt>'.PHP_EOL;
									echo '<dd><a href="?jo='.$bibentries[$q]['journal'].'"><span class="journalinfo">'.$bibentries[$q]['journal'].'</span></a>';
										if (isset($bibentries[$q]['volume']) and $bibentries[$q]['volume'] != '') echo '<span class="journalvol">'.$bibentries[$q]['volume'].'</span>';
										if (isset($bibentries[$q]['volume']) and $bibentries[$q]['number'] != '') echo '<span class="journalno">'.$bibentries[$q]['number'].'</span>';
										if (file_exists('contextdata/journal/'.str_replace(' ', '_', $bibentries[$q]['journal']).'.xml') == True) {
											$journalxml = new SimpleXmlElement(file_get_contents('contextdata/journal/'.str_replace(' ', '_', $bibentries[$q]['journal']).'.xml'));
											if (isset($journalxml)) { 
												echo '<div class="additionalcontextinfo">';
													echo '<h4>'.$bibentries[$q]['journal'].'</h4>';
													echo '<dl>';
										if (isset($journalxml->description) and $journalxml->description != '') { echo '<dt>Description</dt><dd>'.$journalxml->description.'</dd>'; }
										if (isset($journalxml->publisher) and $journalxml->publisher != '') { echo '<dt>Publisher</dt><dd>'; foreach ($journalxml->publisher as $publishers) { echo '<a href="./?pb='.$publishers.'" title="Find books from this publisher">'.$publishers.'</a>'; if ($publishers != end($journalxml->publisher)) echo ', '; } echo '</dd>'; }
										if (isset($journalxml->publishedonbehalfof) and $journalxml->publishedonbehalfof != '') { echo '<dt>Published on behalf of</dt><dd>'.$journalxml->publishedonbehalfof.'</dd>'; }
										if (isset($journalxml->issn) and $journalxml->issn != '') { echo '<dt><abbr title="International Standard Serial Number">ISSN</abbr></dt><dd>'; foreach ($journalxml->issn as $issns) { echo $issns; if ($issns != end($journalxml->issn)) echo ', '; } echo '</dd>'; }
										if (isset($journalxml->website) and $journalxml->website != '') { echo '<dt>Website</dt><dd><a href="'.$journalxml->website.'" title="Visit journal website">'.$journalxml->website.'</a></dd>'; }
										if (isset($journalxml->publisherwebsite) and $journalxml->publisherwebsite != '') { echo '<dt>On Website of Publisher</dt><dd><a href="'.$journalxml->publisherwebsite.'" title="Visit page of the journal on the website of the publisher">'.$journalxml->publisherwebsite.'</a></dd>'; }
										if (isset($journalxml->wikipedia) and $journalxml->wikipedia != '') { echo '<dt>Wikipedia</dt><dd><a href="'.$journalxml->wikipedia.'">'.$journalxml->wikipedia.'</a></dd>'; }
													echo '</dl>';
												echo '</div>';
												unset($journalxml);
											}
										}
									echo '</dd>'.PHP_EOL;
								}
								if (isset($bibentries[$q]['school']) and $bibentries[$q]['school'] != '') {
									echo '<dt>'.$dict->school->$lan.'</dt>'.PHP_EOL;
									echo '<dd>'.$bibentries[$q]['school'].'</dd>'.PHP_EOL;
								}
								if (isset($bibentries[$q]['isbn']) and $bibentries[$q]['isbn'] != '') {
									echo '<dt><abbr title="International Standard Book Number">ISBN</abbr></dt>'.PHP_EOL;
									echo '<dd>'.$bibentries[$q]['isbn'].'</dd>'.PHP_EOL;
								}
								if (isset($bibentries[$q]['loc']) and $bibentries[$q]['loc'] != '') {
									echo '<dt><abbr title="Library of Congress Control Number">LCCN</abbr></dt>'.PHP_EOL;
									echo '<dd>'.$bibentries[$q]['loc'].'</dd>'.PHP_EOL;
								}
							echo '</div>'.PHP_EOL;
							
							if ((isset($bibentries[$q]['doi']) and ($bibentries[$q]['doi'] != ''))or(isset($bibentries[$q]['url']) and ($bibentries[$q]['url'] != ''))) { 
							echo '<div id="furtherinfo">'.PHP_EOL;
								if (isset($bibentries[$q]['doi']) and $bibentries[$q]['doi'] != '') {
									echo '<dt><abbr title="Digital Object Identifier">DOI</abbr></dt>'.PHP_EOL;
									echo '<dd><a href="http://dx.doi.org/'.$bibentries[$q]['doi'].'">'.$bibentries[$q]['doi'].'</a></dd>'.PHP_EOL;
								}
								if (isset($bibentries[$q]['url']) and $bibentries[$q]['url'] != '') {
									echo '<dt><abbr title="Uniform Resource Locator">URL</abbr></dt>'.PHP_EOL;
									echo '<dd><a href="'.$bibentries[$q]['url'].'">'.$bibentries[$q]['url'].'</a></dd>'.PHP_EOL;
								}
							echo '</div>'.PHP_EOL;
							}

							if (isset($bibentries[$q]['abstract']) and $bibentries[$q]['abstract'] != '') {
								echo '<div id="abstract">'.PHP_EOL;
									echo '<dt>'.$dict->abstract->$lan.'</dt>'.PHP_EOL;
									echo '<dd>'.$bibentries[$q]['abstract'].'</dd>'.PHP_EOL;
								echo '</div>'.PHP_EOL;
							}

							if (isset($bibentries[$q]['keywords']) and $bibentries[$q]['keywords'] != '') {
								echo '<div id="desckeyworks">'.PHP_EOL;
									echo '<dt>'.$dict->keywords->$lan.'</dt>'.PHP_EOL;
									foreach ($bibentries[$q]['keywords'] as $keyword) { echo '<dd style="display:inline-block;"><a href="./?s='.$keyword; if (isset($peek)) echo '&peek='.$peek; echo '" rel="tag">'.$keyword.'</a></dd>'.PHP_EOL; }
								echo '</div>'.PHP_EOL;
							}

							echo '</dl>';
						}
						else echo '<p class="noinfoyet">'.$dict->noinfoyet->$lan.'</p>';

						/* End of bibliographical information part */

						if (isset($entry)) {
						/**** Visualization of progress ****/

						echo '<div class="taskstar">';
						
						if (isset($bibentries[$q]) and isset($bibentries[$q]['pages']) and $bibentries[$q]['pages'] != '') {
						if ((isset($edt))and($edt == strval($entry->guid))) {
							echo '<form class="edittitle" method="get" enctype="multipart/form-data" action="tasks.php">';
								echo '<input type="hidden" name="title" value="'.$q.'" />';
								echo '<input type="hidden" name="guid" value="'.$entry->guid.'" />';
								echo '<input type="text" name="progress" placeholder="'.$dict->currentpageno->$lan.'" value="'.strval($entry->progress->after).'" />';
								echo '<button type="submit">'.$dict->send->$lan.'</button>';
							echo '</form>';
						}
						else {

							?>

							<div class="drawprogress" >
								<?PHP
								/* Calculate percentage of progress */
								if ($bibentries[$q]['pages'] != '') {
									if (strpos(' '.$bibentries[$q]['pages'], '-') > 0) {
										$allpages = explode('-', $bibentries[$q]['pages']);
										$complete = intval($allpages[1]) - intval($allpages[0]);
										$startpageminus = $allpages[0];
									}
									else $complete = intval($bibentries[$q]['pages']);
								
									$onepage = 100 / intval($complete);
									
									if (!(isset($startpageminus))) $startpageminus = 0;
									$currentprogress = (intval($entry->progress) - $startpageminus) * $onepage;
									
								}
								?>

								<div style="width:<?PHP echo $currentprogress; ?>%;" class="<?PHP if (round($currentprogress) == 100) echo 'completelyfinished'; ?>" id="finished"></div>
								<div style="width:<?PHP echo (100 - $currentprogress); ?>%;" id="unfinished"></div>

								<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) { ?>
								<div class="helphover">
									<h4>Progress Bar</h4>
									<p>A visualization of your reading progress.</p> 
								</div>
								<?PHP } ?>
							</div>

							<?PHP
							
						}
						}
						else echo '<p class="noinfoyet">'.$dict->nopageinfo->$lan.'</p>';
						echo '</div>';
	
						if (!(isset($startpageminus))) $startpageminus = 0;

						if (!(isset($peek))) {
							echo '<ul class="editinfo">';
							foreach ($entry->edit as $edit){
								if (isset($onepage)) { 
									if ((isset($allpages))and(isset($allpages[0]))and($edit->before == 0)) { echo '<li>'.$dict->progressedfrompage->$lan.' <span style="font-weight:bold;">'.$allpages[0].'</span> <span class="percentage">'; if ($edit->before != 0) echo round(($edit->before - $startpageminus) * $onepage); else echo round($edit->before * $onepage); echo '%</span> '.$dict->topage->$lan.' <span style="font-weight:bold;">'.$edit->after.'</span> <span class="percentage">'.round(($edit->after - $startpageminus) * $onepage).'%</span> '.$dict->on_date->$lan.' <span style="font-weight:bold;">'.cleandate($edit->pubDate).'</span></li>'; }
									else { echo '<li>'.$dict->progressedfrompage->$lan.' <span style="font-weight:bold;">'.$edit->before.'</span> <span class="percentage">'; if ($edit->before != 0) echo round(($edit->before - $startpageminus) * $onepage); else echo round($edit->before * $onepage); echo '%</span> '.$dict->topage->$lan.' <span style="font-weight:bold;">'.$edit->after.'</span> <span class="percentage">'.round(($edit->after - $startpageminus) * $onepage).'%</span> '.$dict->on_date->$lan.' <span style="font-weight:bold;">'.cleandate($edit->pubDate).'</span></li>'; }
								}
								else echo '<li>'.$dict->progressedfrompage->$lan.' <span style="font-weight:bold;">'.$edit->before.'</span> '.$dict->topage->$lan.' <span style="font-weight:bold;">'.$edit->after.'</span> '.$dict->on_date->$lan.' <span style="font-weight:bold;">'.cleandate($edit->pubDate).'</span></li>';
							}
							echo '<li>'.$dict->addedon->$lan.' <span style="font-weight:bold;">'.cleandate($entry->created).'</span></li>';
							echo '</ul>';
						}
						else {
							if (isset($currentprogress)) echo '<p>Reading progress: '.$currentprogress.'%</p>';
						}
						
							
						/* Review */
						if ($entry->note != '' and !isset($edt)) {
							echo '<div class="note">';
								echo '<h3>'.$dict->review->$lan.'</h3>';
								echo '<p>'.reconstruct_html($entry->note).'</p>';						
							echo '</div>';
						}

						if (isset($edt)) {
						echo '<div id="desc'.$entry->guid.'" class="addnote">';
							echo '<h4>'.$dict->addareview->$lan.'</h4>';
							echo '<form method="get" enctype="multipart/form-data" action="tasks.php">';
								echo '<input type="hidden" name="title" value="'.$q.'" />';
								echo '<input type="hidden" name="guid" value="'.$entry->guid.'" />';
								echo '<textarea name="note" class="mceEditor" placeholder="'.$dict->review->$lan.'" >';
								if ($entry->note != '') echo reconstruct_html($entry->note);
								echo '</textarea>';
								echo '<button style="width:100%;" type="submit">'.$dict->send->$lan.'</button>';
							echo '</form>';
						echo '</div>';						
						}

						/* Quotes */
						if ((isset($edt))or(count($entry->quote) > 0)){
							echo '<div class="quotes">';
							echo '<h3>'.$dict->quotes->$lan.'</h3>';
							echo '<ul>';
							if (isset($edt)){
							echo '<li><form method="get" enctype="multipart/form-data" action="tasks.php">';
								echo '<input type="hidden" name="title" value="'.$q.'" />';
								echo '<input type="hidden" name="guid" value="'.$entry->guid.'" />';
								echo '<textarea name="quote" placeholder="'.$dict->quote->$lan.'"></textarea>';
								echo '<input type="text" name="pageno" placeholder="'.$dict->pageno->$lan.'" />';
								echo '<button type="submit">'.$dict->send->$lan.'</button>';
							echo '</form></li>';
							}
							foreach ($entry->quote as $quote) {
								if ((strval($quote->private) != '1')or(!(isset($peek)))) {
								echo '<li>';
									if (strval($quote->private) == '1') echo '<span class="hiddenmarker" title="Quote is hidden from the public">&#128272;</span>';
									echo '<blockquote><p>'.str_replace(PHP_EOL, '</p><p>', reconstruct_html($quote->quote)).'</p></blockquote>';
									if ($quote->pageno != '') echo '<cite>'.$quote->pageno.'</cite>';

									/* Quote options */
									if (!(isset($peek))) {
										echo '<div class="quoteoptions">';
											echo '<a href="./?q='.$q.'&editquotecomment='.str_replace(' ', '_', substr(strval($quote->quote), 0, 150)).'#'.str_replace(' ', '_', substr(strval($quote->quote), 0, 150)).'" title="Comment this quote">C</a>';
											if (strval($quote->private) == '1') echo '<a href="tasks.php?guid='.$entry->guid.'&quote='.$quote->quote.'&pageno='.$quote->pageno.'&quotecomment='.$quote->comment.'&title='.$q.'&quoteprivate=0" title="Make this quote public">P</a>';
											else echo '<a href="tasks.php?guid='.$entry->guid.'&quote='.$quote->quote.'&pageno='.$quote->pageno.'&quotecomment='.$quote->comment.'&title='.$q.'&quoteprivate=1" title="Hide this post from the public">H</a>';
											echo '<a href="tasks.php?guid='.$entry->guid.'&quote='.$quote->quote.'&title='.$q.'&deletequote">-</a>';
										echo '</div>';
									}

									/* Comment on quote */
									if ((isset($quote->comment))and($quote->comment != '')and(!(isset($_GET['editquotecomment'])))) {
										echo '<div class="quotecomment"><h4>Comment</h4><p>'.$quote->comment.'</p></div>';
									}
									if ((isset($_GET['editquotecomment']))and(str_replace(' ', '_', substr(strval($quote->quote), 0, 150)) == $_GET['editquotecomment'])) {
										echo '<div class="quotecomment" id="'.str_replace(' ', '_', substr(strval($quote->quote), 0, 150)).'">';
											echo '<h4>Comment</h4>';
											echo '<form method="get" enctype="multipart/form-data" action="tasks.php">'.PHP_EOL;
												echo '<input type="hidden" name="title" value="'.$q.'" />';
												echo '<input type="hidden" name="guid" value="'.$entry->guid.'" />';
												echo '<input type="hidden" name="quote" value="'.$quote->quote.'" />';
												echo '<input type="hidden" name="pageno" value="'.$quote->pageno.'" />';
												echo '<input type="hidden" name="quoteprivate" value="'.$quote->private.'" />';
												echo '<textarea name="quotecomment">';
													if ((isset($quote->comment))and($quote->comment != '')) echo $quote->comment;
												echo '</textarea>';
												echo '<button type="submit">Send</button>';
											echo '</form>';
										echo '</div>';
									}
								echo '</li>';
								}
							}
							echo '</ul>';
						}
					}
				echo '</li>';
			}
			echo '</ul>';
				
		?>
		
	</main>
	
	<?PHP 
	/* Embed overlay windows for settings etc. */
	if ((!(isset($peek)))and((isset($_GET['settings']))and($_GET['settings'] == 1))) include ('./settings.php'); 
	if ((!(isset($peek)))and((isset($_GET['tools']))and($_GET['tools'] == 1))) include ('./tools.php'); 
	if ((isset($_GET['additionalfunctions']))and($_GET['additionalfunctions'] == 1)) include ('./additionalfunctions.php'); 
	?>
	
		<?PHP
			/* If the overview list is shown, add section for adding new texts to a user's reading list */
			if ((!isset($q))and(!(isset($peek)))){
				echo '<section id="addsec" class="addsec">';
				echo '<h2>'.$dict->startedreadingnew->$lan.'</h2>'.PHP_EOL;
				echo '<form id="addtask" method="get" enctype="multipart/form-data" action="tasks.php">';
					if (!(isset($_GET['selfromlist']))) echo '<input type="text" name="title" placeholder="'.$dict->bibtexid->$lan.'" />';
					else {
						echo '<select name="title">'.PHP_EOL;
							foreach ($bibentries as $key => $bibentry) {
								if (!(in_array(strval($key), $allbooks))) echo '<option value="'.$key.'">'.$key.' ('.$bibentry['title'].')</option>'.PHP_EOL;
							}
						echo '</select>';
					}
					echo '<button type="submit">'.$dict->send->$lan.'</button>';
				echo '</form>';
				if (!(isset($_GET['selfromlist']))) echo '<a style="color:#999;font-style:italic;padding-top:10px;display:block;font-size:0.8em;" href="./?selfromlist">['.$dict->selfromlist->$lan.']</a>';
				?>
				<div class="helphover" style="bottom:10em;">
					<h4>Adding a Text</h4>
					<p>
						To add a text, enter or select the BibTeX key (the string of letters used to identify a work) here.
						If you do not know the BibTeX key of the work you want to add, you can also simply search for the work using the "Find More Texts" function.
					</p>
				</div>

				<?PHP
				echo '</section>';
			}
		?>
	
	
</body>

</html>

<?PHP

/* $q => key :: What should be listed? */
if (isset($_GET['q'])) $q = $_GET['q'];
else $q = 'journal';
if (isset($_GET['peek'])) $peek = $_GET['peek'];

if (!(isset($peek))) include ('password_protect.php');
include ('first.php');

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
if (!(is_dir('./contextdata/publishers/'))) mkdir('./contextdata/publishers/');
if (!(is_dir('./contextdata/journal/'))) mkdir('./contextdata/journal/');
if (!(is_dir('./contextdata/authors/'))) mkdir('./contextdata/authors/');

if (!(isset($peek))) {
	$settingscontent = file_get_contents('./settings/'.$username.'.xml');
	$settingsxml = new SimpleXmlElement($settingscontent);
}

checkdirrec('./allbooks', array('bib')); 
asort($tasklists);

$content = file_get_contents('./tasks/'.$username.'.xml');
$tasks = new SimpleXmlElement($content);

$taskselecter = array();
$sources = array();
foreach ($tasks->channel->item as $entry) {
	$taskselecter[] = strval($entry->title);
	if (!in_array(substr(strval($entry->title), strrpos(strval($entry->title), '_') + 1), $sources)) $sources[] = substr(strval($entry->title), strrpos(strval($entry->title), '_') + 1);
}
$selecter = 'only'.$q;
include ('readbibtex.php');

$finallist = array();

foreach ($tasks->channel->item as $entry) {

	if (isset($bibentries[strval($entry->title)][$q])) {
		if (!(in_array($bibentries[strval($entry->title)][$q], array_keys($finallist))) and $bibentries[strval($entry->title)][$q] != '') $finallist[$bibentries[strval($entry->title)][$q]] = 1;
		else if ($bibentries[strval($entry->title)][$q] != '') $finallist[$bibentries[strval($entry->title)][$q]] = $finallist[$bibentries[strval($entry->title)][$q]] + 1;
	}

}

ksort($finallist);

?>
<!DOCTYPE html>
<html manifest="manifest.php">
<head>
	<title><?PHP echo $pageinfo['title']; ?> :: Books</title>

	<?PHP 
	if (!isset($settingsxml->css) or $settingsxml->css == '') echo '<link rel="stylesheet" type="text/css" href="main.css" />';
	else echo '<link rel="stylesheet" type="text/css" href="'.strval($settingsxml->css).'" />';
	?>

	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=480, initial-scale=0.7" />
	<link rel="shortcut icon" href="./books.png" /> 

</head>

<body>

	<?PHP include ('navigation.php'); ?>

	<main>
		<?PHP

				/* General options */
				echo '<div class="optionbuttons">'.PHP_EOL;
					if (!(isset($peek))){					
						echo '<a href="./?settings=1" title="'.$dict->settings->$lan.'">&#9881;</a>';
						echo '<a href="./?tools=1" title="'.$dict->tools->$lan.'">&#128295;</a>';				
					}

				if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) { ?>
				<div class="helphover">
					<h4>Options</h4>
					<p>Here you can find different additional functions. You may for example change your settings or export data through the links provided.</p> 
				</div>
				<?PHP } 
				echo '</div>'.PHP_EOL;
				
				/* Headline */
				echo '<h2>Lists: '.$q.'</h2>'.PHP_EOL;

				?>

				<!-- Advanced search form -->
				<form class="advancedsearch " method="get" enctype="multipart/form-data" action="">
				<p>
					<input type="radio" value="name" name="q" id="author" /><label for="author"><?PHP echo $dict->name->$lan; ?></label>
					<input type="radio" value="journal" name="q" id="journal" checked /><label for="journal"><?PHP echo $dict->journal->$lan; ?></label>
					<input type="radio" value="publisher" name="q" id="publisher" /><label for="publisher"><?PHP echo $dict->publisher->$lan; ?></label>
					<?PHP if (isset($peek)) echo '<input type="hidden" name="peek" value="'.$peek.'" />'; ?>
					<button type="submit"><?PHP echo $dict->change->$lan; ?></button>
				</p>
				</form>

				<?PHP if ($q == 'journal') { ?>
				<h3></h3>
				<form method="get" enctype="multipart/form-data" action="contextdata-add.php" >
					<input type="hidden" name="kind" value="journal" />
					<input type="text" name="q" placeholder="Name of the journal" value="" />
					<input type="text" name="publ" placeholder="Publisher(s) of the journal" value="" />
					<input type="text" name="publobeh" placeholder="Journal published on behalf of" value="" />
					<input type="text" name="issn" placeholder="ISSN" value="" />
					<input type="text" name="start" placeholder="Start of circulation" value="" />
					<input type="text" name="end" placeholder="Cancellation (if applicable)" value="" />
					<input type="text" name="website" placeholder="Website" value="" />
					<input type="text" name="wikipedia" placeholder="Wikipedia" value="" />
					<input type="text" name="publwebs" placeholder="On publisher's website" value="" />
					<button type="submit"><?PHP echo $dict->send->$lan; ?></button>
				</form>
				<?PHP } ?>

				<?PHP

				if (isset($jo) and file_exists('contextdata/journal/'.str_replace(' ', '_', $jo).'.xml') == True) {
					$journalxml = new SimpleXmlElement(file_get_contents('contextdata/journal/'.str_replace(' ', '_', $jo).'.xml'));
					if (isset($journalxml)) { ?>
						<div class="searchcontextinfo">
							<h4><?PHP echo $jo; ?></h4>
							<dl>
								<?PHP if (isset($journalxml->description) and $journalxml->description != '') echo '<div><dt>Description</dt><dd>'.$journalxml->description.'</dd></div>'; ?>
								<?PHP if (isset($journalxml->publisher) and $journalxml->publisher != '') echo '<div><dt>Publisher</dt><dd><a href="?pb='.$journalxml->publisher.'" title="Find books from this publisher">'.$journalxml->publisher.'</a></dd></div>'; ?>
								<?PHP if (isset($journalxml->issn) and $journalxml->issn != '') echo '<div><dt><abbr title="International Standard Serial Number">ISSN</abbr></dt><dd>'.$journalxml->issn.'</dd></div>'; ?>
								<?PHP if (isset($journalxml->website) and $journalxml->website != '') echo '<div><dt>Website</dt><dd><a href="'.$journalxml->website.'" title="Visit journal website">'.$journalxml->website.'</a></dd></div>'; ?>
								<?PHP if (isset($journalxml->wikipedia) and $journalxml->wikipedia != '') echo '<div><dt>Wikipedia</dt><dd><a href="'.$journalxml->wikipedia.'">'.$journalxml->wikipedia.'</a></dd></div>'; ?>
							</dl>
						</div>
						<?PHP 
						unset($journalxml);
					}
				}

				/* Output */
				?>
				<ul class="overviewlist listslist">

				<?PHP foreach ($finallist as $key => $value) { ?>

					<li>
						<a href="./?s=<?PHP echo $key; ?>"><?PHP echo $key; ?></a>
						<?PHP
						if (file_exists('contextdata/journal/'.str_replace(':', '_', str_replace(' ', '_', $key)).'.xml') == True) {
							$journalxml = new SimpleXmlElement(file_get_contents('contextdata/journal/'.str_replace(':', '_', str_replace(' ', '_', $key)).'.xml'));
							if (isset($journalxml)) { ?>
								<span class="additionalinfomarker">&#x24BE;</span>
								<div class="additionalcontextinfo">
									<h4><?PHP echo $key; ?></h4>
									<dl>
										<?PHP if (isset($journalxml->description) and $journalxml->description != '') { echo '<dt>Description</dt><dd>'.$journalxml->description.'</dd>'; } ?>
										<?PHP if (isset($journalxml->publisher) and $journalxml->publisher != '') { echo '<dt>Publisher</dt><dd>'; foreach ($journalxml->publisher as $publishers) { echo '<a href="./?pb='.$publishers.'" title="Find books from this publisher">'.$publishers.'</a>'; if ($publishers != end($journalxml->publisher)) echo ', '; } echo '</dd>'; } ?>
										<?PHP if (isset($journalxml->publishedonbehalfof) and $journalxml->publishedonbehalfof != '') { echo '<dt>Published on behalf of</dt><dd>'.$journalxml->publishedonbehalfof.'</dd>'; } ?>
										<?PHP if (isset($journalxml->issn) and $journalxml->issn != '') { echo '<dt><abbr title="International Standard Serial Number">ISSN</abbr></dt><dd>'; foreach ($journalxml->issn as $issns) { echo $issns; if ($issns != end($journalxml->issn)) echo ', '; } echo '</dd>'; } ?>
										<?PHP if (isset($journalxml->website) and $journalxml->website != '') { echo '<dt>Website</dt><dd><a href="'.$journalxml->website.'" title="Visit journal website">'.$journalxml->website.'</a></dd>'; } ?>
										<?PHP if (isset($journalxml->publisherwebsite) and $journalxml->publisherwebsite != '') { echo '<dt>On Website of Publisher</dt><dd><a href="'.$journalxml->publisherwebsite.'" title="Visit page of the journal on the website of the publisher">'.$journalxml->publisherwebsite.'</a></dd>'; } ?>
										<?PHP if (isset($journalxml->wikipedia) and $journalxml->wikipedia != '') { echo '<dt>Wikipedia</dt><dd><a href="'.$journalxml->wikipedia.'">'.$journalxml->wikipedia.'</a></dd>'; } ?>
									</dl>
								</div>
								<?PHP 
								unset($journalxml);
							}
						}
						else if (!isset($peek) and $username == $adminusr and isset($_GET['searchmoredata'])) {
	
							if (http_test_existance('https://en.wikipedia.org/wiki/'.str_replace(' ', '_', $key)) != False) { echo '<a class="additionalinfomarker" href="https://en.wikipedia.org/wiki/'.str_replace(' ', '_', $key).'"> &#x21F2; </a>';}

						} ?>
					</li>

				<?PHP } ?>
				</ul>
	
		
	</main>
	
	<?PHP 
	if ((!(isset($peek)))and((isset($_GET['settings']))and($_GET['settings'] == 1))) include ('./settings.php'); 
	if ((!(isset($peek)))and((isset($_GET['tools']))and($_GET['tools'] == 1))) include ('./tools.php'); 
	?>
	
	
</body>

</html>

<?PHP

/* Peek variable: Does somebody but the user or the user themselves open this file? */
if ((isset($_GET['peek']))and($_GET['peek'] != '')) $peek = $_GET['peek'];

/* Search variables */
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

if ((isset($_GET['unfinishedonly']))or(isset($s))or(isset($au))or(isset($ti))or(isset($jo))or(isset($pb))) $pagperpage = 10000;
else $pagperpage = 10;

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

/* Read user information */
if (!(isset($peek))) {
	$settingscontent = file_get_contents('./settings/'.$username.'.xml');
	$settingsxml = new SimpleXmlElement($settingscontent);
}

/* Get data on the texts a user has added */
$content = file_get_contents('./tasks/'.$username.'.xml');
$tasks = new SimpleXmlElement($content);

/* Read all single texts a user has added into an array */
$allbooks = array();
foreach ($tasks->channel->item as $entry) {
	$taskselecter[] = strval($entry->title);
}

/* Get all files containing bibliographical information */
checkdirrec('./allbooks', array('bib')); 
asort($tasklists);

/* $showonlyunread is required. */
$showonlyunread = True;
$selecter = 'basicinfo';
if ($searchstatus == True) include ('readbibtex.php');

?>
<!DOCTYPE html>
<html manifest="manifest.php">
<head>
	<title><?PHP echo $pageinfo['title']; ?> :: Books</title>
	<?PHP 
	if (!isset($settingsxml->css) or $settingsxml->css == '') echo '<link rel="stylesheet" type="text/css" href="main.css" />';
	else echo '<link rel="stylesheet" type="text/css" href="'.strval($settingsxml->css).'" />';
	?>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
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
						echo '<a href="./unread.php?settings=1" title="'.$dict->settings->$lan.'">&#9881;</a>';
						echo '<a href="./unread.php?tools=1" title="'.$dict->tools->$lan.'">&#128295;</a>';				
					}
				echo '</div>'.PHP_EOL;
				
				/* Headline */
				if (!(isset($s))) echo '<h2>'.$dict->findmoretexts->$lan.'</h2>'.PHP_EOL;
				else if (isset($s)) echo '<h2>'.$dict->searchresultsfor->$lan.': <span style="font-style:italic;">'.$s.'</span></h2>'.PHP_EOL;

				/* Search form. */
				?>
				<form class="advancedsearch" method="get" enctype="multipart/form-data" action="">
					<?PHP echo $dict->filterbykeyword->$lan; ?>: <input type="text" name="s" value="<?PHP if (isset($s)) echo $s; ?>" placeholder="<?PHP echo $dict->emptynofilter->$lan; ?>" />
					<?PHP echo $dict->authors->$lan; ?>: <input type="text" name="au" value="<?PHP if (isset($au)) echo $au; ?>" placeholder="<?PHP echo $dict->emptynofilter->$lan; ?>" />
					<?PHP echo $dict->title->$lan; ?>: <input type="text" name="ti" value="<?PHP if (isset($ti)) echo $ti; ?>" placeholder="<?PHP echo $dict->emptynofilter->$lan; ?>" />
					<?PHP echo $dict->publisher->$lan; ?>: <input type="text" name="pb" value="<?PHP if (isset($pb)) echo $pb; ?>" placeholder="<?PHP echo $dict->emptynofilter->$lan; ?>" />
					<?PHP echo $dict->journal->$lan; ?>: <input type="text" name="jo" value="<?PHP if (isset($jo)) echo $jo; ?>" placeholder="<?PHP echo $dict->emptynofilter->$lan; ?>" />
					<?PHP if (isset($peek)) echo '<input type="hidden" name="peek" value="'.$peek.'" />'; ?>
					<button type="submit"><?PHP echo $dict->change->$lan; ?></button>
				</p>
				</form>

				<?PHP
				
				/* Output */
				if ($searchstatus == True) {
				echo '<ul class="overviewlist">';			
				$i = 0;	
				
				/* Display all entries from the array $bibentries. The entries have been filtered according to the search variables in readbibtex.php. */
				foreach ($bibentries as $key => $value){
						
					echo '<li>';
					echo '<a href="./?q='.$key; if (isset($peek)) echo '&peek='.$peek; echo '">';
					
						echo '<div class="imgdiv">';
						if ($value['kind'] == 'Book') { 
							if (file_exists('./media/books/'.strval($key).'.jpg')) echo '<img title="'.$dict->bookcoverof->$lan.' '.$value['title'].'" alt="'.$dict->bookcoverof->$lan.' '.strval($key).'" src="./media/books/'.strval($key).'.jpg" />';
						}
						else if (isset($value['journal']) and file_exists('./media/sources/'.str_replace(':', '_', str_replace(' ', '_', $value['journal'])).'.jpg')) echo '<img title="'.$dict->bookcoverof->$lan.' '.$value['title'].'" alt="'.$dict->bookcoverof->$lan.' '.strval($key).'" src="./media/sources/'.str_replace(':', '_', str_replace(' ', '_', $value['journal'])).'.jpg" />';
						else if (($value['kind'] == 'InBook')and(isset($value['isbn']))and(isset($isbns[($value['isbn'])]))
								and(file_exists('./media/books/'.strval($isbns[($value['isbn'])]).'.jpg'))){
								echo '<img title="'.$dict->bookcoverof->$lan.' '.strval($isbns[($value['isbn'])]).'" alt="Book cover of '.strval($isbns[($value['isbn'])]).'" src="./media/books/'.strval($isbns[($value['isbn'])]).'.jpg" />';
						}
						echo '</div>';						
						echo '<div class="contdiv">';	
							echo '<h3>'.$value['title'].'</h3>';
							echo '<p>'; if (isset($value['author'])) echo $value['author']; echo ' ('; if (isset($value['year'])) echo $value['year']; echo ')</p>'; 
							if (isset($value['abstract']) and $value['abstract'] != '') {
								echo '<p id="abstract">'.substr($value['abstract'], 0, 150).'[...]</p>';
							}
							
						echo '</div>';
						
					echo '</a>';
					echo '</li>';

					$i++;					
				}
				unset ($i);
				echo '</ul>';
				}
				/* If no search terms were specified, notify the user. */
				else {
					echo '<p style="font-style:italic;">No search term specified. Please enter one below.</p>';
				}
			?>
			
	</main>
	<?PHP 
	if ((!(isset($peek)))and((isset($_GET['settings']))and($_GET['settings'] == 1))) include ('./settings.php'); 
	if ((!(isset($peek)))and((isset($_GET['tools']))and($_GET['tools'] == 1))) include ('./tools.php'); 
	?>
</body>

</html>

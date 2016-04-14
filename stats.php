<?PHP
function colorfromstring ($string) {
	$letternumbers = array(' ' => 0, '_' => 1, 'a' => 2, 'b' => 3, 'c' => 4, 'd' => 5, 'e' => 6, 'f' => 7, 'g' => 8, 'h' => 9, 'i' => 10, 'j' => 11, 'k' => 12, 'l' => 13, 'm' => 14, 'n' => 15, 'n' => 16, 'o' => 17, 'p' => 18, 'q' => 19, 'r' => 20, 's' => 21, 't' => 22, 'u' => 23, 'v' => 24, 'w' => 25, 'x' => 26, 'y' => 27, 'z' => 28);
	
	$string = strtolower($string);
	foreach ($letternumbers as $key => $value) $string = str_replace ($key, $value, $string);

	return substr(dechex(intval($string)), 0, 6);
}

if (isset($_GET['q'])) $q = $_GET['q'];
if ((isset($_GET['peek']))and($_GET['peek'] != '')) $peek = $_GET['peek'];
if ((isset($_GET['s']))and($_GET['s'] != '')) $s = $_GET['s'];
if (isset($_GET['p'])) $p = $_GET['p'];
if (isset($_GET['timestart'])) $timestart = $_GET['timestart'];
else $timestart = date('1970-01-01');
if (isset($_GET['timeend'])) $timeend = $_GET['timeend'];
else $timeend = (intval(date('Y'))+1).'-'.date('m-d');
if (isset($_GET['timespan'])) { $timespan = $_GET['timespan']; }
if (!(isset($_GET['timespan']))) $timespan = 'month';

if (!(isset($peek))) include ('password_protect.php');
include ('first.php');

$tasklists = array();

/* Usernames */
if (isset($_SESSION['username'])) $username = $_SESSION['username'];
if (isset($peek)) $username = $peek;
if (!(isset($username))) header('location: ./password_protect.php?logout=yes');
if (!(is_dir('./allbooks/'))) mkdir('./allbooks/');
if (!(is_dir('./media/'))) mkdir('./media/');
if (!(file_exists('./tasks/'.$username.'.xml'))) { $handle = fopen('./tasks/'.$username.'.xml', 'w'); fwrite($handle, '<rss><channel></channel></rss>'.PHP_EOL); fclose($handle); }

if (!(isset($peek))) {
	$settingscontent = file_get_contents('./settings/'.$username.'.xml');
	$settingsxml = new SimpleXmlElement($settingscontent);
}

$content = file_get_contents('./tasks/'.$username.'.xml');
$tasks = new SimpleXmlElement($content);

checkdirrec('./allbooks', array('bib'));
asort($tasklists);

$taskselecter = array();
foreach ($tasks->channel->item as $entry) {
	$taskselecter[] = strval($entry->title);
}

unset($entry);
include ('readbibtex.php');

$allbooks = array();
$booklengths = array();

/* Set general times */
$genstartTimeStamp = strtotime($timestart);
$genendTimeStamp = strtotime($timeend);

include ('readiallbooks.php');

?>
<!DOCTYPE html>
<html manifest="manifest.php">
<head>
	<title>JREnslin.de :: Books</title>

	<?PHP 
	if (!isset($settingsxml->css) or $settingsxml->css == '') echo '<link rel="stylesheet" type="text/css" href="main.css">';
	else echo '<link rel="stylesheet" type="text/css" href="'.strval($settingsxml->css).'">';
	?>

	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=480, initial-scale=0.7" />
	<link rel="shortcut icon" href="./books.png" /> 
	
	<?PHP
	?>
	
</head>

<body>

	<?PHP include ('navigation.php'); ?>
	
	<main>
		<h2><?PHP echo $dict->statistics->$lan; ?></h2>

		<form class="advancedsearch" method="get" enctype="multipart/form-data" action="stats.php">
		<p><?PHP echo $dict->takingintoaccount->$lan; ?> 
			<input type="date" name="timestart" value="<?PHP echo $timestart ?>" />		
			<?PHP echo $dict->and->$lan; ?>		
			<input type="date" name="timeend" value="<?PHP echo $timeend ?>" />. 
			<?PHP echo $dict->filterbykeyword->$lan; ?>: <input type="text" name="s" value="<?PHP if (isset($s)) echo $s; ?>" placeholder="<?PHP echo $dict->emptynofilter->$lan; ?>" />
			<?PHP if (isset($peek)) echo '<input type="hidden" name="peek" value="'.$peek.'" />'; ?>
			<button type="submit"><?PHP echo $dict->change->$lan; ?></button>
		</p>
		</form>
		<?PHP 
			
			$finished = array('yes' => 0, 'no' => 0);
			$editdates = array();
			$finisheddates = array();
			
			foreach ($allbooks as $key => $entry) {
				if ((isset($bibentries[$key]))and($bibentries[$key]['pages'] != '')){
					if ((count($entry['edits']) > 0)and($entry['edits'][0]['after'] == $bibentries[$key]['pages'])) {
						$finished['yes'] = $finished['yes'] + 1;
						$finisheddates[] = array('pubDate' => cleandate($entry['edits'][0]['pubDate']));
					}
					else $finished['no'] = $finished['no'] + 1;
			
					foreach ($entry['edits'] as $tedit) $editdates[] = array('pubDate' => cleandate($tedit['pubDate']), 'before' => $tedit['before'], 'after' => $tedit['after']);
				}
			}
			
				/* General options */
			echo '<div class="optionbuttons">'.PHP_EOL;
				if (!(isset($peek))){					
					echo '<a href="./stats.php?settings=1" title="'.$dict->settings->$lan.'">&#9881;</a>';
					echo '<a href="./stats.php?tools=1" title="'.$dict->tools->$lan.'">&#128295;</a>';
				}
			echo '</div>'.PHP_EOL;
				
			echo '<dl class="statsnos">'.PHP_EOL;
				
				echo '<div>';
					echo '<dt>'.$dict->noofbooksadded->$lan.'</dt>'.PHP_EOL;
					echo '<dd>'.count($allbooks).'</dd>'.PHP_EOL;
				echo '</div>';
				
				echo '<div>';
					echo '<dt>'.$dict->noofunfinished->$lan.'</dt>'.PHP_EOL;
					echo '<dd>'.$finished['no'].'</dd>'.PHP_EOL;
				echo '</div>';
				
				echo '<div>';
					echo '<dt>'.$dict->nooffinished->$lan.'</dt>'.PHP_EOL;
					echo '<dd>'.$finished['yes'].'</dd>'.PHP_EOL;
				echo '</div>';
				
				echo '<div>';
					echo '<dt>'.$dict->averagelength->$lan.'</dt>';
					echo '<dd>'.round(array_sum($booklengths) / count($booklengths)).' '.$dict->pages_lowercase->$lan.'</dd>';
				echo '</div>';

			echo '</dl>'.PHP_EOL;
				
			?>
			<h2><?PHP echo $dict->times->$lan; ?></h2>
			<form method="get" action="" >
				<select name="timespan" >
					<option value="day"><?PHP echo $dict->day->$lan; ?></option>
					<option value="month"><?PHP echo $dict->month->$lan; ?></option>
					<option value="year"><?PHP echo $dict->year->$lan; ?></option>
				</select>
				<button type="submit"><?PHP echo $dict->send->$lan; ?></button>
			</form>
			
			<?PHP
			echo '<dl>'.PHP_EOL;
				
				echo '<dt>'.$dict->editsbytime->$lan.'</dt>'.PHP_EOL;
				echo '<dd>';
					
					drawtimestats($editdates, $timespan);
					
				echo '</dd>'.PHP_EOL;
				
				echo '<dt>'.$dict->finishedtextsbytime->$lan.'</dt>'.PHP_EOL;
				echo '<dd>';
					drawtimestats ($finisheddates, $timespan);
				echo '</dd>'.PHP_EOL;
				
			echo '</dl>'.PHP_EOL;

			$alldates = array();
			foreach($allbooks as $entry) {
				foreach ($entry['edits'] as $entryedits) {
					if ($entryedits['after'] == '10') $finished = cleandate($entryedits['pubDate']);
					$alldates[$entry['title']] = array('start' => cleandate($entry['created']), 'end' => cleandate($entry['edits'][0]['pubDate']));
				}
			}

			$timespent = array();
			foreach($alldates as $entry) {
				$startTimeStamp = strtotime($entry['start']);
				$endTimeStamp = strtotime($entry['end']);
								
				$timeDiff = abs($endTimeStamp - $startTimeStamp);

				$numberDays = $timeDiff/86400;
				$numberDays = intval($numberDays);
				
				$timespent[] = $numberDays;
			}
			
			echo '<h3>'.$dict->numbers->$lan.'</h3>';
			
			echo '<dl>';
				echo '<dt>'.$dict->averagenoofdayspassed->$lan.'</dt>';
				echo '<dd>'.array_sum($timespent) / count($timespent).'</dd>';
			echo '</dl>';
		?>
		
	<h2><?PHP echo $dict->keywords->$lan; ?></h2>
	<?PHP
		$categories = array();
		$categoriescount = array();
		foreach ($allbooks as $entry) {
			foreach ($entry['categories'] as $categ){
				if (isset($entry['edits'][0])) {
					if (!in_array($categ, array_keys($categories))) $categories[$categ] = array('count' => 1,'times' => array(array('start' => cleandate($entry['created']), 'end' => cleandate($entry['edits'][0]['pubDate']))));
					else {
					$categories[$categ]['count']++; $categories[$categ]['times'][] = array('start' => cleandate($entry['created']), 'end' => cleandate($entry['edits'][0]['pubDate']));
					}
					
					if (!in_array($categ, array_keys($categoriescount))) $categoriescount[$categ] = 1;
					else {
					$categoriescount[$categ]++;
					}
				}
			}
		}
		$categmonths = array();
		foreach ($categories as $key => $categ){
			foreach ($categ['times'] as $ctime) {
				
				if (!in_array(substr($ctime['start'], 0, 7), array_keys($categmonths))) $categmonths[substr($ctime['start'], 0, 7)] = array($key => 1); 
				else if (!in_array($key, array_keys($categmonths[substr($ctime['start'], 0, 7)]))) $categmonths[substr($ctime['start'], 0, 7)][$key] = 1;
				else $categmonths[substr($ctime['start'], 0, 7)][$key]++;
				if (substr($ctime['start'], 0, 7) != substr($ctime['end'], 0, 7)) {								
					if (!in_array(substr($ctime['end'], 0, 7), array_keys($categmonths))) $categmonths[substr($ctime['end'], 0, 7)] = array($key => 1); 
					else if (!in_array($key, array_keys($categmonths[substr($ctime['end'], 0, 7)]))) $categmonths[substr($ctime['end'], 0, 7)][$key] = 1;
					else $categmonths[substr($ctime['end'], 0, 7)][$key]++;
				}
			}
		}

		ksort($categmonths);
		
		echo '<h3>'.$dict->favcategories->$lan.'</h3>';
		arsort($categoriescount);
		$i = 0;
		
		echo '<div class="favoritetags">';
		foreach ($categoriescount as $key => $value) {
			echo '<a rel="tag" href="./?s='.$key; if (isset($peek)) echo '&peek='.$peek; echo '" title="'.$key.': '.$value.' '.$dict->entries->$lan.'">'.$key.'</a>';
			$i++;
			if ($i == 10) break;
		}
		echo '</div>';
		

		unset ($i);
		
		echo '<h3>Timeline</h3>';
		echo '<figure class="drawstats2">';
		echo '<div style="display:block;margin-bottom:20px;">';
			
			foreach ($categmonths as $entry) {
				echo '<div style="width:'.(100 / count($categmonths)).'%;">';
					echo array_sum($entry);
					foreach ($entry as $key => $categ) echo '<div style="width:100%;height:'.($categ * 100 / (array_sum($entry))).'%;background-color:#'.colorfromstring($key).';" title="'.$key.': '.$categ.' '.$dict->entries->$lan.'"></div>';
				echo '</div>';		
			}

		echo '</div>';
			foreach ($categmonths as $key => $entry) {
				echo '<div class="legend" style="width:'.(100 / count($categmonths)).'%;"><time>'.$key.'</time></div>';
			}
		echo '</figure>';
	
	?>
	</main>
	
	<?PHP 
	if ((!(isset($peek)))and((isset($_GET['settings']))and($_GET['settings'] == 1))) include ('./settings.php'); 
	if ((!(isset($peek)))and((isset($_GET['tools']))and($_GET['tools'] == 1))) include ('./tools.php'); 
	?>
	
</body>

</html>


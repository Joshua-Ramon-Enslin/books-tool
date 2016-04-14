<?PHP
/* Basic variables */
if (isset($_GET['q'])) $q = $_GET['q'];
if ((isset($_GET['peek']))and($_GET['peek'] != '')) $peek = $_GET['peek'];

/* Search variables */
if ((isset($_GET['s']))and($_GET['s'] != '')) $s = $_GET['s'];
if (isset($_GET['n'])) $n = intval($_GET['n']);
if (isset($_GET['timestart'])) $timestart = $_GET['timestart'];
else $timestart = date('1970-01-01');
if (isset($_GET['timeend'])) $timeend = $_GET['timeend'];
else $timeend = (intval(date('Y'))+1).'-'.date('m-d');
if (isset($_GET['timespan'])) { $timespan = $_GET['timespan']; }
if (!(isset($_GET['timespan']))) $timespan = 'month';

/* Set general times */
$genstartTimeStamp = strtotime($timestart);
$genendTimeStamp = strtotime($timeend);

if (!(isset($peek))) include ('password_protect.php');
include ('first.php');

/* Usernames */
if (isset($_SESSION['username'])) $username = $_SESSION['username'];
if (isset($peek)) $username = $peek;
if (!(isset($username))) header('location: ./password_protect.php?logout=yes');

/* Check for necessary directories and files. Create them if they don't exist. */
if (!(is_dir('./allbooks/'))) mkdir('./allbooks/');
if (!(is_dir('./media/'))) mkdir('./media/');
if (!(file_exists('./tasks/'.$username.'.xml'))) { $handle = fopen('./tasks/'.$username.'.xml', 'w'); fwrite($handle, '<rss><channel></channel></rss>'.PHP_EOL); fclose($handle); }

$tasklists = array();

checkdir('./tasks');
asort($tasklists);
$users = $tasklists;
$tasklists = array();

checkdir('./settings');
asort($tasklists);

if (!(isset($peek))) {
	$currentsettingscontent = file_get_contents('./settings/'.$username.'.xml');
	$currentsettingsxml = new SimpleXmlElement($currentsettingscontent);
}

$usersettings = $tasklists;
$settingsxml = array();
foreach ($usersettings as $user) {
	$content = file_get_contents('./settings/'.$user.'.xml');
	$settingsxml[$user] = new SimpleXmlElement($content);
}
$tasklists = array();

checkdirrec('./allbooks', array('bib'));
asort($tasklists);

$tasks = array();
foreach ($users as $user) {
	$content = file_get_contents('./tasks/'.$user.'.xml');
	$tasks[$user] = new SimpleXmlElement($content);
}

$allbooks = array();
$booklengths = array();

$taskselecter = array();
$sources = array();

foreach ($tasks as $key => $ttask) {
	foreach ($ttask->channel->item as $entry){
		$taskselecter[] = strval($entry->title);
		if (!in_array(substr(strval($entry->title), strrpos(strval($entry->title), '_') + 1), $sources)) $sources[] = substr(strval($entry->title), strrpos(strval($entry->title), '_') + 1);
	}
}
$selecter = 'keywords';
include ('readbibtex.php');

foreach ($tasks as $key => $ttask) {
	foreach ($ttask->channel->item as $entry){

		if (isset($bibentries[strval($entry->title)])) { if (isset($s) and (!isset($bibentries[strval($entry->title)]['keywords']) or !in_array($s, $bibentries[strval($entry->title)]['keywords']))) continue; }
		
		if (isset($bibentries[strval($entry->title)]) and isset($bibentries[strval($entry->title)]['keywords'])) {
			$allbooks[$key][strval($entry->title)] = array(
				'categories' => $bibentries[strval($entry->title)]['keywords'],
				'created' => strval($entry->created)
			);
		}
	}
}

$countentries = array();
foreach ($allbooks as $key => $value) {
	$countentries[$key] = count($value);
}

?>


<!DOCTYPE html>
<html manifest="manifest.php">
<head>
	<title>JREnslin.de :: Books</title>
	<?PHP 
	if (!isset($currentsettingsxml->css) or $currentsettingsxml->css == '') echo '<link rel="stylesheet" type="text/css" href="main.css" />';
	else echo '<link rel="stylesheet" type="text/css" href="'.strval($currentsettingsxml->css).'" />';
	?>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=480, initial-scale=0.7" />
	<link rel="shortcut icon" href="./books.png" /> 
	
	<?PHP
	?>
	
</head>

<body>

	<?PHP include ('navigation.php'); ?>
	
	<main>

		<?PHP

				/* General options */
			echo '<div class="optionbuttons">'.PHP_EOL;
				if (!(isset($peek))){					
					echo '<a href="./ranking.php?settings=1" title="'.$dict->settings->$lan.'">&#9881;</a>';
					echo '<a href="./ranking.php?tools=1" title="'.$dict->tools->$lan.'">&#128295;</a>';					
				}
			echo '</div>'.PHP_EOL;
			
			/* Headline */
			if (!(isset($s))) echo '<h2>'.$dict->ranking->$lan.'</h2>'.PHP_EOL;
			else if (isset($s)) echo '<h2>'.$dict->ranking->$lan.' ('.$dict->filter->$lan.': <span style="font-style:italic;">'.$s.'</span>)</h2>'.PHP_EOL;
			
			?>			
				
			<form class="advancedsearch" method="get" enctype="multipart/form-data" action="ranking.php">
			<p>
				<?PHP echo $dict->filterbykeyword->$lan; ?>: <input type="text" name="s" value="<?PHP if (isset($s)) echo $s; ?>" placeholder="<?PHP echo $dict->emptynofilter->$lan; ?>" />
				<?PHP if (isset($peek)) echo '<input type="hidden" name="peek" value="'.$peek.'" />'; ?>
				<button type="submit"><?PHP echo $dict->change->$lan; ?></button>
			</p>
			</form>
		
			<?PHP
			
			$rank = 1;
			echo '<ol class="userranking">';
			foreach ($countentries as $key => $value){
				echo '<li class="h-card vcard">';
						
					/* ---- Favorite Categories ---- */
					$categoriescount = array();
					foreach ($allbooks[$key] as $entry) {
						if (isset($entry['categories'])) {
							foreach ($entry['categories'] as $categ){
								if (!in_array($categ, array_keys($categoriescount))) $categoriescount[$categ] = 1;
								else {
									$categoriescount[$categ]++;
								}
							
							}
						}
					}
					
					arsort($categoriescount);
					if (isset($s)) unset ($categoriescount[$s]);
					$i = 0;
					
					echo '<span class="ranknumber">#'.$rank.' ('.$value.' '.$dict->entries->$lan.')</span>';
								
					if (isset($settingsxml[$key])) {
						echo '<a href="profile.php?peek='.$key.'" class="url u-url">';
						if ($settingsxml[$key]->image != '') echo '<img src="'.$settingsxml[$key]->image.'" alt="'.$dict->profilepicof->$lan.' '.$settingsxml[$key]->name.'" title="'.$dict->profileof->$lan.' '.$settingsxml[$key]->name.'" />';
						if ($settingsxml[$key]->name != '') echo '<span class="fn p-name">'.$settingsxml[$key]->name.' (@'.$key.')</span></a>';
						else echo '<span class="fn p-name url u-url">@'.$key.'</span></a>';
					}
					else {
						echo '<a href="profile.php?peek='.$key.'" class="fn p-name url u-url"><span>@'.$key.'</span></a>';
					}

					echo '<div class="favoritetags">';
					foreach ($categoriescount as $tkey => $tvalue) {
						echo '<a rel="tag" href="./?s='.$tkey; if (isset($peek)) echo '&peek='.$peek; echo '" title="'.$tkey.': '.$tvalue.' '.$dict->entries->$lan.'">'.$tkey.'</a>';
						$i++;
						if ($i == 5) break;
					}
					unset($categoriescount);
					echo '</div>';
					
				echo '</li>';
				$rank++;
				if (isset($n) and $rank == intval($n)) break;
			}
			echo '</ol>';
			
		?>
		
	</main>
	
	<?PHP 
	if ((!(isset($peek)))and((isset($_GET['settings']))and($_GET['settings'] == 1))) include ('./settings.php'); 
	if ((!(isset($peek)))and((isset($_GET['tools']))and($_GET['tools'] == 1))) include ('./tools.php'); 
	?>
	
</body>

</html>

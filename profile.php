<?PHP

if (isset($_GET['q'])) $q = $_GET['q'];
if ((isset($_GET['peek']))and($_GET['peek'] != '')) $peek = $_GET['peek'];

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

$settingscontent = file_get_contents('./settings/'.$username.'.xml');
$settingsxml = new SimpleXmlElement($settingscontent);

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
$singletaskselecter = strval($tasks->channel->item[0]->title);
$selecter = 'keywordsplusone';

include ('readbibtex.php');

$allbooks = array();
$booklengths = array();

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
	
</head>

<body class="h-card vcard">

	<?PHP include ('navigation.php'); ?>

	<main class="userprofile">
		<?PHP

			/* General options */
			echo '<div class="optionbuttons">'.PHP_EOL;
				if (!(isset($peek))){					
					echo '<a href="./profile.php?settings=1" title="'.$dict->settings->$lan.'">&#9881;</a>';
					echo '<a href="./profile.php?tools=1" title="'.$dict->tools->$lan.'">&#128295;</a>';
				}
				echo '<a href="http://h2vx.com/vcf/http://www.museum-digital.de/joshua/tools/books/profile.php?peek='.$username.'" title="'.$dict->downloadvcard->$lan.'">&#x1F464;</a>';
			echo '</div>'.PHP_EOL;
				
			/* Headline */
			echo '<h2>Profile: '; if ($settingsxml->name != '') echo '<span class="fn p-name">'.$settingsxml->name.'</span> '; echo '(@<span class="nickname p-nickname">'.$username.'</span>)</h2>'.PHP_EOL;
				
			echo '<dl class="bibinfo">';
			
				echo '<div>';
				if ($settingsxml->description != '') {
					echo '<dt>'.$dict->description->$lan.'</dt>';
					echo '<dd class="p-note note">'.reconstruct_html($settingsxml->description).'</dd>';
				}
				
					if (($settingsxml->homepage != '')or($settingsxml->email != '')and(strval($settingsxml->emailpublic) == '1')) {
						echo '<dt>'.$dict->contact->$lan.'</dt>';
						echo '<dd class="contactinfo">';
						if ($settingsxml->homepage != '') {
							echo '<a id="homepage" class="url u-url" href="'.$settingsxml->homepage.'">'.$settingsxml->homepage.'</a>';
						}
						if (($settingsxml->email != '')and(strval($settingsxml->emailpublic) == '1')) {
							echo '<a id="email" class="email u-email" href="mailto:'.$settingsxml->email.'">'.$settingsxml->email.'</a>';
						}
						echo '</dd>';
					}
				echo '</div>';
				
				echo '<div id="cover">';
				if ($settingsxml->image != '') {
					echo '<figure>';
					echo '<img class="photo u-photo" alt="'.$dict->profilepic->$lan.'" src="'.$settingsxml->image.'" />';
					echo '<figcaption>'.$dict->profilepic->$lan.'</figcaption>';
					echo '</figure>';
				}
				echo '</div>';
			
				echo '<div>';
				if ($settingsxml->job != '') {
					echo '<dt>'.$dict->job->$lan.'</dt>';
					echo '<dd class="p-job-title">'.$settingsxml->job.'</dd>';
				}
				if ($settingsxml->affiliation != '') {
					echo '<dt>'.$dict->affiliation->$lan.'</dt>';
					echo '<dd class="org p-org">'.$settingsxml->affiliation.'</dd>';
				}
				echo '</div>';
			
				echo '<div>';
				if ($settingsxml->city != '') {
					echo '<dt>'.$dict->city->$lan.'</dt>';
					echo '<dd class="p-locality">';
					if (isset($_GET['getgeoinfo'])) getgeoinfo(strval($settingsxml->city));
					echo $settingsxml->city;
					if (!isset($_GET['getgeoinfo'])) { echo ' <a href="profile.php?getgeoinfo'; if (isset($peek)) echo '&peek='.peek; echo '">( &#127759; )</a>'; }
					echo '</dd>';
				}
				echo '</div>';
				
					
			echo '</dl>';
			
			/* ---- Favorite Categories ---- */
			echo '<h3>'.$dict->favcategories->$lan.'</h3>';
			$categoriescount = findfavcategories ($dict, $allbooks);

			echo '<div class="favoritetags">';
			$i = 0;
			foreach ($categoriescount as $key => $value) {
				echo '<a rel="tag" href="./?s='.$key; if (isset($peek)) echo '&peek='.$peek; echo '" title="'.$key.': '.$value.' '.$dict->entries->$lan.'">'.$key.'</a>';
				$i++;
				if ($i == 10) break;
			}
			echo '</div>';
			
			/* ---- Current Text ---- */
			echo '<h3>'.$dict->currenttext->$lan.'</h3>';
			$entry = $tasks->channel->item[0];
			
			/* Output */
			echo '<ul class="overviewlist">';	
				echo '<li>';
					echo '<a href="./?q='.$entry->title; if (isset($peek)) echo '&peek='.$peek; echo '">';
					if (isset($bibentries[strval($entry->title)])){
						echo '<div class="imgdiv">';
						if ($bibentries[strval($entry->title)]['kind'] == 'Book') { 
							if (file_exists('./media/books/'.strval($entry->title).'.jpg')) echo '<img title="'.$dict->bookcoverof->$lan.' '.$bibentries[strval($entry->title)]['title'].'" alt="'.$dict->bookcoverof->$lan.' '.strval($entry->title).'" src="./media/books/'.strval($entry->title).'.jpg" />';
						}
						else if (file_exists('./media/sources/'.str_replace(':', '_', str_replace(' ', '_', $bibentries[strval($entry->title)]['journal'])).'.jpg')) echo '<img title="'.$dict->bookcoverof->$lan.' '.$bibentries[strval($entry->title)]['title'].'" alt="'.$dict->bookcoverof->$lan.' '.strval($entry->title).'" src="./media/sources/'.str_replace(':', '_', str_replace(' ', '_', $bibentries[strval($entry->title)]['journal'])).'.jpg" />';
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
							
							if ($bibentries[strval($entry->title)]['pages'] != '') {
							echo '<p style="margin-bottom:0.5em;">'.$dict->progress->$lan.': '.$entry->progress.' / '.$bibentries[strval($entry->title)]['pages'].' ('.$dict->lastupdated->$lan.': <time>'.cleandate($entry->edit->pubDate).'</time>)</p>';
							
							/* QW Fix Calculation: Put into function */ 
?>						
							<div class="drawprogress" style="height:20px;" >
								<?PHP
								/* Calculate percentage of progress */
								if ($bibentries[strval($entry->title)]['pages'] != '') {
									if (strpos(' '.$bibentries[strval($entry->title)]['pages'], '-') > 0) {
										$allpages = explode('-', $bibentries[strval($entry->title)]['pages']);
										$complete = intval($allpages[1]) - intval($allpages[0]);
										$startpageminus = $allpages[0];
									}
									else $complete = intval($bibentries[strval($entry->title)]['pages']);
								
									$onepage = 100 / intval($complete);
									if (!(isset($startpageminus))) $startpageminus = 0;
									$currentprogress = (intval($entry->progress) - $startpageminus) * $onepage;
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
			echo '</ul>';
			
		?>
		
	</main>
	
	<?PHP 
	if ((!(isset($peek)))and((isset($_GET['settings']))and($_GET['settings'] == 1))) include ('./settings.php'); 
	if ((!(isset($peek)))and((isset($_GET['tools']))and($_GET['tools'] == 1))) include ('./tools.php'); 
	?>
	
</body>

</html>

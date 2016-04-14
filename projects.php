<?PHP

/* Get basic variables */
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

if (!(is_dir('./usrmedia/'))) mkdir('./usrmedia/');
if (!(is_dir('./usrmedia/'.$_SESSION['username'].'/'))) mkdir('./usrmedia/'.$_SESSION['username'].'/');
if (isset($q)) { if (!(is_dir('./usrmedia/'.$_SESSION['username'].'/'.str_replace('.', '', str_replace(' ', '', str_replace(':', '', $q))).'/'))) mkdir('./usrmedia/'.$_SESSION['username'].'/'.str_replace('.', '', str_replace(' ', '', str_replace(':', '', $q))).'/'); }

/* Create archive & settings files if not existent */
if (!(file_exists('./settings/'.$username.'.xml'))) { $handle = fopen('./settings/'.$username.'.xml', 'w'); fwrite($handle, '<rss></rss>'.PHP_EOL); fclose($handle); }
if (!(is_dir('./allbooks/'.$username.'/'))) mkdir('./allbooks/'.$username.'/');

/* Create projects directory */
if (!(is_dir('./projects/'))) mkdir('./projects/');
if (!(file_exists('./projects/'.$username.'.xml'))) { $handle = fopen('./projects/'.$username.'.xml', 'w'); fwrite($handle, '<rss><channel></channel></rss>'.PHP_EOL); fclose($handle); }
if (!(file_exists('./projects/'.$username.'_projrelations.xml'))) { $handle = fopen('./projects/'.$username.'_projrelations.xml', 'w'); fwrite($handle, '<rss><channel></channel></rss>'.PHP_EOL); fclose($handle); }

if (isset($q)) $fixedq = str_replace ('?', '', str_replace ('.', '', str_replace ('!', '', str_replace ('?', '', str_replace (':', '', str_replace (' ', '_', $q))))));

/* Create directory and file for project descriptions */
if (!(is_dir('./projectnotes/'))) mkdir('./projectnotes/');
if (!(is_dir('./projectnotes/'.$username.'/'))) mkdir('./projectnotes/'.$username.'/');
if ((isset($q)) and (!(file_exists('./projectnotes/'.$username.'/'.$fixedq.'.htm')))) { $handle = fopen('./projectnotes/'.$username.'/'.$fixedq.'.htm', 'w'); fwrite($handle, ''); fclose($handle); }

if (!(isset($peek))) {
	$settingscontent = file_get_contents('./settings/'.$username.'.xml');
	$settingsxml = new SimpleXmlElement($settingscontent);
}

/* Get bibliographical data and notes on project if one is currently to be displayed */
if (isset($q)) {
	$projectnotes = file_get_contents('./projectnotes/'.$username.'/'.$fixedq.'.htm');

	checkdirrec('./allbooks', array('bib')); 
	asort($tasklists);
}

$tasks = new SimpleXmlElement(file_get_contents('./tasks/'.$username.'.xml'));
$proj = new SimpleXmlElement(file_get_contents('./projects/'.$username.'.xml'));

/* Function to determine the relation a project has to other projects */
function findrelations ($q, $username) {
	$relations = new SimpleXmlElement(file_get_contents('./projects/'.$username.'_projrelations.xml'));
	$toreturn = array('higher' => array(), 'related' => array(), 'lower' => array());	
	foreach ($relations->item as $relation) {
		if (strval($relation->from) == $q) {
			if (strval($relation->relation) == 'related') $toreturn['related'][] = $relation->to;
			else if (strval($relation->relation) == 'higher') $toreturn['higher'][] = $relation->to;
			else if (strval($relation->relation) == 'lower') $toreturn['lower'][] = $relation->to;
		}
		else if (strval($relation->to) == $q) {
			if (strval($relation->relation) == 'related') $toreturn['related'][] = $relation->from;
			else if (strval($relation->relation) == 'higher') $toreturn['higher'][] = $relation->from;
			else if (strval($relation->relation) == 'lower') $toreturn['lower'][] = $relation->from;
		}
	}
	return ($toreturn);
}

if (isset($q)) $projectsrelations = findrelations($q, $username);

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

			# Display project list if no project has been selected for display
			if (!(isset($q))) {

				/* General options */
				echo '<div class="optionbuttons">'.PHP_EOL;
					if (!(isset($peek))){					
						echo '<a href="./?settings=1" title="'.$dict->settings->$lan.'">&#9881;</a>';
						echo '<a href="./?tools=1" title="'.$dict->tools->$lan.'">&#128295;</a>';				
					}
				echo '</div>'.PHP_EOL;
				
				/* Headline */
				if (!(isset($s))) echo '<h2>'.$dict->projects->$lan.'</h2>'.PHP_EOL;
				
				echo '<ul class="overviewlist projectsoverviewlist">';	
				foreach ($proj->channel->item as $entry) {
					echo '<li>';
						echo '<a href="./projects.php?q='.$entry->title; if (isset($peek)) echo '&peek='.$peek; echo '"><h3>'.$entry->title.'</h3></a>';
						echo '<dl>';
							echo '<div><dt>Associated texts</dt><dd>'.count($entry->assotext).'</dd></div>';
							echo '<div><dt>Associated quotes</dt><dd>'.count($entry->quote).'</dd></div>';
						echo '</dl>';
					echo '</li>';
				}
				echo '</ul>';
				
			}
			# Display single project if one has been selected
			else {

				/* General options */
				echo '<div class="optionbuttons">'.PHP_EOL;

					if (!(isset($peek))){					
						echo '<a href="./?q='.$q.'&amp;settings=1" title="'.$dict->settings->$lan.'">&#9881;</a>';
						echo '<a href="./?tools=1" title="'.$dict->tools->$lan.'">&#128295;</a>';
					}

					echo '<a href="./projects.php?q='.$q.'&exportcommentedbibliography'; if (isset($peek)) echo '&peek='.$peek; echo '#associatedtexts" title="'.$dict->exportcommentedbibliography->$lan.'">E</a>';
					echo '<a href="./projects.php?q='.$q.'&exportsimplebibliography'; if (isset($peek)) echo '&peek='.$peek; echo '#associatedtexts" title="'.$dict->exportsimplebibliography->$lan.'">S</a>';

					echo '<div class="tableofcontents">';
						echo '<span class="opentoc">T</span>';
						echo '<a href="#start" title="Jump to start">Start</a>';
						echo '<a href="#projnotes" title="Jump to Notes">Notes</a>';
						echo '<a href="#associatedquotes" title="Jump to associated quotes">Associated quotes</a>';
						echo '<a href="#associatedtexts" title="Jump to associated texts">Associated texts</a>';
						echo '<a href="#associateddatasource" title="Jump to associated data sources">Data Sources</a>';
						echo '<a href="#associatedmedia" title="Jump to associated media files">Media files</a>';
						echo '<a href="#statistics" title="Jump to statistics">Statistics</a>';
					echo '</div>';

					?>

					<!-- Search through project relationships -->
					<div id="semanticnavigation">	
						<span class="openprojsem">O</span>
						<h3>Related Projects</h3>
						<div class="projsem">
							<div class="projsemhigher">
							<?PHP if (count($projectsrelations['higher']) > 0) { ?>
								<?PHP foreach ($projectsrelations['higher'] as $projrelation) echo '<a href="./projects.php?q='.$projrelation.'" title="'.$projrelation.'">'.substr($projrelation, 0, 20).'</a>'; ?>
							<?PHP } ?>
							<span class="projsemdesc">Higher</span>
							</div>
							<div class="projsemrelated">
							<?PHP if (count($projectsrelations['related']) > 0) { ?>
								<?PHP foreach ($projectsrelations['related'] as $projrelation) echo '<a href="./projects.php?q='.$projrelation.'" title="'.$projrelation.'">'.substr($projrelation, 0, 20).'</a>'; ?>
							<?PHP } ?>
							<span class="projsemdesc">Related</span>
							</div>
							<div class="projsemcurrent">
								<a href="./projects.php?q=<?PHP echo $q; ?>"></a>
							</div>
							<div class="projsemlower">
							<?PHP if (count($projectsrelations['lower']) > 0) { ?>
								<?PHP foreach ($projectsrelations['lower'] as $projrelation) echo '<a href="./projects.php?q='.$projrelation.'" title="'.$projrelation.'">'.substr($projrelation, 0, 20).'</a>'; ?>
							<?PHP } ?>
							<span class="projsemdesc">Lower</span>
							</div>	
							
							<div>
								<form method="get" action="project-relations-edit.php">
									<input name="q" value="<?PHP echo $q; ?>" />
									<select name="relation">
										<option value="higher">Higher</option>
										<option value="related">Related</option>
										<option value="lower">Lower</option>
									</select>
									<select name="r">
										<?PHP foreach ($proj->channel->item as $anitem) echo '<option value="'.$anitem->title.'">'.$anitem->title.'</value>'; ?>
									</select>
									<button type="submit">Send</button>
								</form>
								<span class="projsemdesc">Add a relation</span>
							</div>
						</div>
					</div>

				<?PHP
				echo '</div>'.PHP_EOL;
				
				/* Single Entry */
				foreach ($proj->channel->item as $pentry) {
				if ($pentry->title == $q){
					
					$alltexts = array();
					$associatedtexts = array();
					$associatedtextsinfo = array();
					foreach ($pentry->assotext as $assotext) {
						$associatedtexts[] = $assotext->id;
					}
					foreach ($tasks->channel->item as $task) {
						if (in_array(strval($task->title), $associatedtexts)) $associatedtextsinfo[] = $task;
						$alltexts[] = strval($task->title);
					}
					asort($alltexts);


					$taskselecter = array();
					$sources = array();
					foreach ($pentry->assotext as $assotext) {
						$taskselecter[] = strval($assotext->id);

						if (!in_array(substr(strval($assotext->id), strrpos(strval($assotext->id), '_') + 1), $sources)) $sources[] = substr(strval($assotext->id), strrpos(strval($assotext->id), '_') + 1);
					}
					/* Load bibliographical information */
					include ('readbibtex.php');
					unset ($taskselecter);
					
					/* Load all quotes */
					$currentquotes = array();
					foreach ($pentry->quote as $assoquot) {
						$currentquotes[] = strval($assoquot->quote);
					}
					asort ($currentquotes);
					
					?>
					<h2 id="start"><?PHP echo $pentry->title; ?></h2>
					
					<div class="projnotes" id="projnotes">
						<?PHP if (!(isset($_GET['addnotes'])) and !(isset($peek))) echo '<a href="./projects.php?q='.$q.'&addnotes#projnotes" title="Edit notes" class="options">+</a>'; ?>
						<h3>Notes</h3>
						
						<?PHP 
							if ((!(isset($_GET['addnotes']))) and (strlen($projectnotes) > 5)) echo reconstruct_html($projectnotes); 
							else if (isset($_GET['addnotes']) and !(isset($peek))) {
								echo '<div>';
									echo '<h4>Edit Notes</h4>';
									echo '<form method="post" enctype="multipart/form-data" action="projects-edit-notes.php">'.PHP_EOL;
										echo '<input type="hidden" name="q" value="'.$q.'" />';
										echo '<textarea name="content">'.reconstruct_html($projectnotes).'</textarea>';
										echo '<button type="submit">Send</button>';
									echo '</form>';
								echo '</div>';
							}
						?>
					</div>

					<div class="projassociatedquotes" id="associatedquotes">
						<h3>Associated Quotes</h3>
					
						<?PHP
						echo '<ul>';
							foreach ($pentry->quote as $assoquot) {
								if ((strval($assoquot->private) != '1')or(!(isset($peek)))) {
								echo '<li>';
									if (strval($assoquot->private) == '1') echo '<span class="hiddenmarker" title="Quote is hidden from the public">&#128272;</span>';
									echo '<blockquote><p>'.reconstruct_html($assoquot->quote).'</p> <cite><a href="./?q='.$assoquot->source; if (isset($peek)) echo '&peek='.$peek; echo '" title="More information on this book">'.$bibentries[strval($assoquot->source)]['author'].': '.$bibentries[strval($assoquot->source)]['title'].'</a>, '.$assoquot->pageno.'</cite></blockquote>';

									/* Quote options */
									if (!(isset($peek))) {
										echo '<div class="quoteoptions">';
											echo '<a href="./projects.php?q='.$q.'&editquotecomment='.str_replace(' ', '_', substr(strval($assoquot->quote), 0, 150)).'#'.str_replace(' ', '_', substr(strval($assoquot->quote), 0, 150)).'" title="Comment this quote">C</a>';
											if (strval($assoquot->private) == '1') echo '<a href="projects-edit.php?guid='.$pentry->guid.'&assoquot='.$assoquot->quote.'&assoquottext='.$assoquot->source.'&assoquotpageno='.$assoquot->pageno.'&quotecomment='.$assoquot->comment.'&assoquotprivate=0" title="Make this quote public">P</a>';
											else echo '<a href="projects-edit.php?guid='.$pentry->guid.'&assoquot='.$assoquot->quote.'&assoquottext='.$assoquot->source.'&assoquotpageno='.$assoquot->pageno.'&quotecomment='.$assoquot->comment.'&assoquotprivate=1" title="Hide this post from the public">H</a>';
											echo '<a href="projects-edit.php?guid='.$pentry->guid.'&assoquot='.$assoquot->quote.'&assoquottext='.$assoquot->source.'&deletequote">-</a>';
										echo '</div>';
									}

									/* Comment on quote */
									if ((isset($assoquot->comment))and($assoquot->comment != '')and(!(isset($_GET['editquotecomment'])))) {
										echo '<div class="quotecomment"><h4>Comment</h4><p>'.$assoquot->comment.'</p></div>';
									}
									if ((isset($_GET['editquotecomment']))and(str_replace(' ', '_', substr(strval($assoquot->quote), 0, 150)) == $_GET['editquotecomment'])) {
										echo '<div class="quotecomment" id="'.str_replace(' ', '_', substr(strval($assoquot->quote), 0, 150)).'">';
											echo '<h4>Comment</h4>';
											echo '<form method="get" enctype="multipart/form-data" action="projects-edit.php">'.PHP_EOL;

												/* Print (hidden) information on the quote the comment is to be related to */
												echo '<input type="hidden" name="guid" value="'.$pentry->guid.'" />';
												echo '<input type="hidden" name="assoquot" value="'.$assoquot->quote.'" />';
												echo '<input type="hidden" name="assoquottext" value="'.$assoquot->source.'" />';
												echo '<input type="hidden" name="assoquotpageno" value="'.$assoquot->pageno.'" />';
												echo '<input type="hidden" name="assoquotprivate" value="'.$assoquot->private.'" />';
												echo '<textarea name="assoquotcomment">';
													if ((isset($assoquot->comment))and($assoquot->comment != '')) echo $assoquot->comment;
												echo '</textarea>';
												echo '<button type="submit">Send</button>';
											echo '</form>';
										echo '</div>';
									}
								echo '</li>';
								}
							}
						echo '</ul>';
						?>
					</div>

					<?PHP if (!(isset($peek))) { ?>
					<div class="projassociatedtextquotes">
						<h3>More Quotes From Associated Texts</h3>
						
						<?PHP						
						echo '<ul>';
							foreach ($associatedtextsinfo as $atentry){
								
								foreach ($atentry->quote as $atquote){
									if (!(in_array(strval($atquote->quote), $currentquotes))) {
										echo '<li>';
											echo '<blockquote><p>'.reconstruct_html($atquote->quote).'</p>';
											echo '<div class="quotefooter"><cite>'.$bibentries[strval($atentry->title)]['author'].': '.$bibentries[strval($atentry->title)]['title'].', '.$atquote->pageno.'</cite>';
											echo '<a href="projects-edit.php?guid='.$pentry->guid.'&assoquottext='.$atentry->title.'&assoquotpageno='.$atquote->pageno.'&assoquot='.$atquote->quote.'" class="associateprojquote" title="Add this quote to the text">+</a>';
											echo '</div>';
											
										echo '</li>';
									}
								}
							}
						echo '</ul>';
						?>
					</div>
					<?PHP } 

					if (!isset($_GET['exportsimplebibliography'])) echo '<div class="projassociatedtexts" id="associatedtexts">';
					else echo '<div class="projassociatedtextssimple" id="associatedtexts">';
						if (!(isset($_GET['addassociatedtext'])) and !(isset($peek))) echo '<a href="./projects.php?q='.$q.'&addassociatedtext#addassociatedtext" title="Add an associated text" class="options">+</a>'; ?>
						<h3>Associated Texts</h3>
						<?PHP
						$assotexts = array();
						foreach ($pentry->assotext as $assotext) {
							$assotexts[strval($assotext->id)] = array('comment' => strval($assotext->comment), 'categories' => $bibentries[strval($assotext->id)]['keywords']);
						}
						ksort($assotexts);

						echo '<ul>';
						
							foreach ($assotexts as $assotextkey => $assotextvalue) {
								echo '<li>';
									if (isset($_GET['exportcommentedbibliography'])) { echo '<h4>'; citstyle_authordate ($bibentries, strval($assotextkey)); echo '</h4>'; }
									else if (isset($_GET['exportsimplebibliography'])) { citstyle_authordate ($bibentries, strval($assotextkey)); }
									
									else { echo '<h4><a href="./?q='.$assotextkey; if (isset($peek)) echo '&peek='.$peek; echo '">'.$bibentries[strval($assotextkey)]['title'].'</a></h4>'; }
									if (!isset($_GET['exportsimplebibliography'])) echo '<p>'.str_replace(PHP_EOL, '<br />', reconstruct_html($assotextvalue['comment'])).'</p>';
								echo '</li>';
							}
						echo '</ul>';
						?>
						
						<?PHP if (isset($_GET['addassociatedtext']) and !(isset($peek))) { ?>
						<div id="addassociatedtext" class="addassociatedtext">
							<h4>Add an associated text</h4>
							<form id="addtask" method="get" enctype="multipart/form-data" action="projects-edit.php">
								<?PHP
								echo '<input type="hidden" name="guid" value="'.$pentry->guid.'" />';
								echo '<select name="assotext">'.PHP_EOL;
									foreach ($alltexts as $pentrytext) {
										echo '<option value="'.$pentrytext.'">'.$pentrytext.' ('.$bibentries[$pentrytext]['title'].')</option>'.PHP_EOL;
									}
								echo '</select>';
								echo '<textarea name="assotextcomment"></textarea>';
								?>
								<button type="submit">Send</button>
							</form>
						</div>
						<?PHP } ?>
					</div>

					<div class="projassociateddatasource" id="associateddatasource">
						<?PHP if (!(isset($_GET['adddatasource'])) and !(isset($peek))) echo '<a href="./projects.php?q='.$q.'&adddatasource#associateddatasource" title="Add a data source" class="options">+</a>'; ?>
						<h3>Data Sources and Links</h3>
						<?PHP
						$datasources = array();
						foreach ($pentry->datasource as $datasource) {
							$datasources[strval($datasource->title)] = array('link' => strval($datasource->link), 'comment' => strval($datasource->comment), 'pubDate' => strval($datasource->pubDate));
						}
						ksort($datasources);

						echo '<ul>';
						
							foreach ($datasources as $datasourcekey => $datasourcevalue) {
								
								echo '<li>';
									if (strpos(' '.$datasourcevalue['link'], 'youtube') > 0) echo '<img src="media/filetypes/videos.svg" />';
									else echo '<img src="./media/filetypes/statistics.svg" />';
									echo '<h4><a href="'.reconstruct_html($datasourcevalue['link']).'">'.reconstruct_html($datasourcekey).'</a></h4>'; 
									echo '<div><p>'.str_replace(PHP_EOL, '<br />', reconstruct_html($datasourcevalue['comment'])).'</p><p>Accessed/Added: <time>'.$datasourcevalue['pubDate'].'</time></p></div>';
								echo '</li>';
							}
						echo '</ul>';
						?>
						
						<?PHP if (isset($_GET['adddatasource']) and !(isset($peek))) { ?>
						<div id="adddatasource" class="adddatasource">
							<h4>Add a Data Source</h4>
							<form id="addtask" method="get" enctype="multipart/form-data" action="projects-edit.php">
								<?PHP
								echo '<input type="hidden" name="guid" value="'.$pentry->guid.'" />';
								echo '<input type="text" name="assodatasourcetitle" placeholder="Title" />';
								echo '<input type="text" name="assodatasourcelink" placeholder="Link" />';								
								echo '<textarea name="assodatasourcecomment" placeholder="Comment"></textarea>';
								?>
								<button type="submit">Send</button>
							</form>
						</div>
						<?PHP } ?>
					</div>

					<div class="projassociatedmedia" id="associatedmedia">
						<?PHP if ((!(isset($_GET['uploadassocmedia'])))and(!(isset($peek)))) echo '<a href="./projects.php?q='.$q.'&uploadassocmedia#associatedmedia" title="Add associated media files" class="options">+</a>'; ?>
						<h3>Associated Media Files</h3>
						<?PHP if ((isset($_GET['uploadassocmedia']))and(!(isset($peek)))) { ?>
						<div class="uploadassociatedmedia">
							<h4>Upload Associated Media Files</h4>
							<form enctype="multipart/form-data" action="_upload.php" method="POST">
		
								<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
								<input type="hidden" name="uploadfolder" value="usrmedia/<?PHP echo $_SESSION['username'].'/'.str_replace('.', '', str_replace(' ', '', str_replace(':', '', $q))); ?>/" />
								<input name="userfile" type="file" required />
								<button type="submit"><?PHP echo $dict->send->$lan; ?></button>
							</form>
		
						</div>
						<?PHP
						}
						$associatedmedia = array();
						
						$folder = 'usrmedia/'.$_SESSION['username'].'/'.str_replace('.', '', str_replace(' ', '', str_replace(':', '', $q))).'/';
						if ($handle = opendir($folder)) {
							while (false !== ($entry = readdir($handle))) {
								if (!((is_file($folder.'/'.rtrim($entry, '.')) == false)and(strpos(' '.$entry, '.') == 0))){
									if (in_array(strtolower(pathinfo($entry, PATHINFO_EXTENSION)), array('mp4', 'webm', 'mp3', 'jpg', 'jpeg', 'png', 'svg', 'xlsx', 'xml', 'txt'))) $associatedmedia[] = pathinfo($entry, PATHINFO_BASENAME);
								}
							}
						}
						?>
						<ul>
							<?PHP
							foreach ($associatedmedia as $assomedia) {
								echo '<li>';
									echo '<a href="usrmedia/'.$_SESSION['username'].'/'.str_replace('.', '', str_replace(' ', '', str_replace(':', '', $q))).'/'.$assomedia.'">';
									/* Find appropriate image for file type */
									if (in_array(pathinfo($assomedia, PATHINFO_EXTENSION), $filetypes['text'])) echo '<img src="media/filetypes/text.svg" />';
									else if (in_array(pathinfo($assomedia, PATHINFO_EXTENSION), $filetypes['video'])) echo '<img src="media/filetypes/videos.svg" />';
									else if (in_array(pathinfo($assomedia, PATHINFO_EXTENSION), $filetypes['audio'])) echo '<img src="media/filetypes/audios.svg" />';
									else if (in_array(pathinfo($assomedia, PATHINFO_EXTENSION, $filetypes['presentations']))) echo '<img src="media/filetypes/presentations.svg" />';
									else if (in_array(pathinfo($assomedia, PATHINFO_EXTENSION), $filetypes['pdf'])) echo '<img src="media/filetypes/pdfs.svg" />';
									
									echo '<h4>'.$assomedia.'</h4>';
									echo '</a>';
								echo '</li>';
							}
							?>
						</ul>
					</div>


					<div id="statistics">
					<h3>Project Statistics</h3>
					<?PHP 
						$categoriescount = findfavcategories ($dict, $assotexts, True);
						
						if (count($categoriescount) > 0) {
							echo '<figure>';
							echo '<div class="favoritetagsdiagram diagram">';
							$i = 0;
							foreach ($categoriescount as $key => $value) {
								echo '<a rel="tag" style="height:'.( 100 / array_values($categoriescount)[0] * $value ).'%;" href="./?s='.$key; if (isset($peek)) echo '&peek='.$peek; echo '" title="'.$key.': '.$value.' '.$dict->entries->$lan.'"><span>'.$key.'</span><span class="counter">'.$value.'</span></a>';
								$i++;
								if ($i == 10) break;
							}
							echo '</div>';
							echo '<figcaption style="margin-top:3em;">Keywords most of the associated texts have been tagged with</figcaption>';
							echo '</figure>';
						}
					?>
					</div>
				<?PHP
				}
				}
			}
				
		?>
		
	</main>
	
	<?PHP 
	if ((!(isset($peek)))and((isset($_GET['settings']))and($_GET['settings'] == 1))) include ('./settings.php'); 
	if ((!(isset($peek)))and((isset($_GET['tools']))and($_GET['tools'] == 1))) include ('./tools.php'); 
	?>
	
		<?PHP
			/* Section for adding projects if the overview is currently shown */
			if ((!isset($q))and(!(isset($peek)))){
				echo '<section id="addsec" class="addsec">';
				echo '<h2>'.$dict->addproject->$lan.'</h2>'.PHP_EOL;
				echo '<form id="addtask" method="get" enctype="multipart/form-data" action="projects-edit.php">';
					echo '<input type="text" name="title" placeholder="'.$dict->title->$lan.'" />';
					echo '<button type="submit">'.$dict->send->$lan.'</button>';
				echo '</form>';
				echo '</section>';
			}
		?>
	
	
</body>

</html>

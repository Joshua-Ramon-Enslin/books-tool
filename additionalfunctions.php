<section class="window">
	<h2 class="tools">Additional Functions</h2>
	<div class="close"><a href="./<?PHP if (isset($q)) echo '?q='.$q; ?>">&#65336;</a></div>
		
	<div class="litdatsearch">
	<?PHP if (!isset($_GET['addlitdat'])) echo '<a class="options" href="./?additionalfunctions=1&addlitdat">+</a>'; ?>
	<h3>Searching Literature Databases</h3>

	<?PHP
	if (!(file_exists('./contextdata/literaturedatabases.xml'))) { $handle = fopen('./contextdata/literaturedatabases.xml', 'w'); fwrite($handle, '<rss></rss>'.PHP_EOL); fclose($handle); }

	$litdatcont = new SimpleXmlElement(file_get_contents('./contextdata/literaturedatabases.xml'));
	?>

	<?PHP if (isset($_GET['addlitdat'])) { ?>
	
	<div class="contextdatainput" style="width:100%;">
		<h4>Add a Literature Database</h4>
		<form method="get" action="contextdata_literaturedatabases.php">
			<input type="text" name="title" placeholder="Name of the service" />
			<input type="text" name="link" placeholder="URL of the search page" />
			<input type="text" name="var" placeholder="Search variable (e.g. q)" />
			<button type="submit">Send</button>
		</form>
	</div>

	<?PHP } ?>
	
	<?PHP
	$literaturedatabases = array();

	foreach ($litdatcont->channel->item as $entry) {
		$literaturedatabases[strval($entry->title)] = array('searchurl' => strval($entry->link), 'var' => strval($entry->var));
	}
	/*
	$literaturedatabases = array(
		'GoogleScholar' => array('searchurl' => 'https://scholar.google.de/scholar', 'var' => 'q'), 
		'Project_Muse' => array('searchurl' => 'https://muse.jhu.edu/results', 'var' => 'search_term'), 
		'OApen' => array('searchurl' => 'https://www.oapen.org/search', 'var' => 'keyword'), 
		'JStor' => array('searchurl' => 'https://www.jstor.org/action/doBasicSearch', 'var' => 'Query')
	);
	*/
	ksort ($literaturedatabases);
	?>

	<script type="text/javascript">
	function transferinputcont () {
    <?PHP 
		foreach ($literaturedatabases as $key => $value) { 
			echo 'document.getElementById("litdat'.$key.'name").innerHTML = document.getElementById("litdatgensearch").value;'.PHP_EOL; 
			echo 'document.getElementById("litdat'.$key.'link").href = "'.$value['searchurl'].'?'.$value['var'].'=" + document.getElementById("litdatgensearch").value;'.PHP_EOL;
			echo 'document.getElementById("litdat'.$key.'link").style = "font-weight:bold;color:#58a;"'.PHP_EOL;
		}
	?>
	}
	</script>

	<div>
		<p>Using this function, you can search through different literature databases (more will be added over time). After writing a search term in the search bar and pressing the button, the links will be updated to lead to the respective search page on each of the services listed below.</p>

		<input id="litdatgensearch" placeholder="Search term" />
		<button onclick="transferinputcont()">Provide search links</button>
	</div>

	<ul>
	<?PHP 
		foreach ($literaturedatabases as $key => $value) {
			echo '<li>Search for "<span id="litdat'.$key.'name" class="searchterm"></span>" on  '.str_replace('_', ' ', $key).' - <a id="litdat'.$key.'link" href="'.$value['searchurl'].'?'.$value['var'].'=" style="">Search</a></li>';
		}
	?>
	</ul>

	<div>

</section>

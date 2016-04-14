<?PHP 

	# Function to remove potentially harmful substrings from a string (usually used when saving documents)	
	function cleanup_str($cleanup){
		$cleanup=str_replace('?','--qm--',$cleanup);
		$cleanup=str_replace('&','--and--',$cleanup);
		$cleanup=str_replace('<?PHP','',$cleanup);
		$cleanup=str_replace('<?','',$cleanup);
		$cleanup=str_replace('?>','',$cleanup);
		$cleanup=str_replace('<','[[',$cleanup);
		$cleanup=str_replace('>',']]',$cleanup);
		return $cleanup;
	}

	# This function restores HTML tags from saved documents		
	function reconstruct_html($cleanup){
	$cleanup=str_replace('--qm--','?',$cleanup);
	$cleanup=str_replace('--and--','&amp;',$cleanup);
	$cleanup=str_replace('[[','<',$cleanup);
    $cleanup=str_replace(']]','>',$cleanup);
    return $cleanup;
	}
	

	function cleandate ($toclean) {
		$toclean = substr($toclean, 5, 11);
		$toclean = str_replace(' ', '.', $toclean);
		$toclean = str_replace('Jan', '01', $toclean);
		$toclean = str_replace('Feb', '02', $toclean);
		$toclean = str_replace('Mar', '03', $toclean);
		$toclean = str_replace('Apr', '04', $toclean);
		$toclean = str_replace('May', '05', $toclean);
		$toclean = str_replace('Jun', '06', $toclean);
		$toclean = str_replace('Jul', '07', $toclean);
		$toclean = str_replace('Aug', '08', $toclean);
		$toclean = str_replace('Sep', '09', $toclean);
		$toclean = str_replace('Oct', '10', $toclean);
		$toclean = str_replace('Nov', '11', $toclean);
		$toclean = str_replace('Dec', '12', $toclean);
		$toclean = substr($toclean, 6, 4).'-'.substr($toclean, 3, 2).'-'.substr($toclean, 0, 2);
		return $toclean;
	} 

	function checknote ($str, $kind = ''){
		
		$returnstr = $str;
		$strarray = explode (' ', $str);
		$imgfiletypes = array('.jpg','.jpeg','.png','.bmp','.gif');
		$vidfiletypes = array('.mp4','.ogg','.webm');
		$audfiletypes = array('.mp3');
		$adds = '';	
		
		foreach ($strarray as $strs){
			foreach ($imgfiletypes as $filetype){
				/* Check for images */
				if ((strpos(' '.strtolower($strs), $filetype)  > 1)and((strpos(' '.strtolower($strs), 'http://') > 0)or(strpos(' '.strtolower($strs), 'https://')))){
					$adds = $adds.'<a href="'.$strs.'" title="View this image"><img src="'.$strs.'" /></a>';
				}	
			}
			foreach ($vidfiletypes as $filetype){
				/* Check for videos */
				if ((strpos(' '.strtolower($strs), $filetype)  > 1)and((strpos(' '.strtolower($strs), 'http://') > 0)or(strpos(' '.strtolower($strs), 'https://')))){
					$adds = $adds.'<video controls><source src="'.$strs.'">Your browser does not support the video tag or the given format.</video>';
				}	
			}		
			foreach ($audfiletypes as $filetype){
				/* Check for audio */
				if ((strpos(' '.strtolower($strs), $filetype)  > 1)and((strpos(' '.strtolower($strs), 'http://') > 0)or(strpos(' '.strtolower($strs), 'https://')))){
					$adds = $adds.'<audio controls><source src="'.$strs.'">Your browser does not support the audio tag or the given format.</audio>';
				}	
			}		
			/* Check for videos from youtube */
			if ((strpos(' '.strtolower($strs), 'http://www.youtube.com') > 0)or(strpos(' '.strtolower($strs), 'https://www.youtube.com'))){
				$adds = $adds.'<iframe src="'.str_replace('watch?v=', 'embed/', $strs).'" allowfullscreen></iframe>';
			}		
			/* Check for links */
			if ((strpos(' '.strtolower($strs), 'http://') > 0)or(strpos(' '.strtolower($strs), 'https://'))){
				if ($kind = 'like') $tkind = 'class="u-like-of" ';
				else $tkind = '';
				$returnstr = str_replace($strs, '<a href="'.$strs.'" '.$tkind.'title="Click to follow the link">'.$strs.'</a>', $returnstr);
			}
		}
	
		return ($returnstr.'<br />'.$adds);
	
	}

	function checknotelinksonly ($str){
		
		$returnstr = $str;
		$strarray = explode (' ', $str);
		$adds = '';	
		
		foreach ($strarray as $strs){		
			/* Check for links */
			if ((strpos(' '.strtolower($strs), 'http://') > 0)or(strpos(' '.strtolower($strs), 'https://'))){
				$returnstr = str_replace($strs, '<a href="'.$strs.'" title="Click to follow the link">'.$strs.'</a>', $returnstr);
			}
		}
	
		return ($returnstr.'<br />'.$adds);
	
	}
	
	# This function checks a folder ($folder) and returns all files with the extension $extension in it
	function checkdir ($folder, $extensions = array('xml')){
			
		global $tasklists; 
		
		if ($handle = opendir($folder)) {
			while (false !== ($entry = readdir($handle))) {
				if (!((is_file($folder.'/'.rtrim($entry, '.')) == false)and(strpos(' '.$entry, '.') == 0))){
					if (in_array(strtolower(pathinfo($entry, PATHINFO_EXTENSION)), $extensions)) $tasklists[] = str_replace('.'.pathinfo($entry, PATHINFO_EXTENSION), '', pathinfo($entry, PATHINFO_BASENAME));
				}
			}
		}
	}
	
	# This function checks a folder ($folder) and its subfolders recursively and returns all files with the extension $extension in them
	function checkdirrec ($folder, $extensions = array('xml')){
		
		global $tasklists; 
		
		if ($handle = opendir($folder)) {
			while (false !== ($entry = readdir($handle))) {
				if (!((is_file($folder.'/'.rtrim($entry, '.')) == false)and(strpos(' '.$entry, '.') == 0))){
					if (in_array(strtolower(pathinfo($entry, PATHINFO_EXTENSION)), $extensions)) { $tasklists[] = $folder.'/'.str_replace('.'.pathinfo($entry, PATHINFO_EXTENSION), '', pathinfo($entry, PATHINFO_BASENAME)); }
				}
				else if ((is_dir($folder.'/'.$entry))and(strlen($entry) > 5)and(strpos($entry, '..') == 0)) { checkdirrec ($folder.'/'.$entry, $extensions); }
			}
		}
	}


	# This function draws a graph for statistics
	function drawtimestats ($dates, $timespan){
		$timespanconv = array('year' => 4, 'month' => 7, 'day' => 10);
				
		echo '<figure class="drawstats">';
			/* Calculating dates */
			$alldates = array();
			foreach($dates as $entry) {
				$currdate = substr($entry['pubDate'], 0, $timespanconv[$timespan]);
				if (!(in_array($currdate, array_keys($alldates)))) { $alldates[$currdate] = 0; }
				$alldates[$currdate] = $alldates[$currdate] + 1; 
			}
			ksort($alldates);
										
			foreach($alldates as $key=>$entry) {		
				echo '<div style="width:'.(100 / count($alldates)).'%;" title="'.$key.' // '.$entry.'"><a style="height:'.($entry * 2).'px;"></a></div>';
			}
			foreach($alldates as $key=>$entry) {		
				echo '<div class="legend" style="width:calc('.(100 / count($alldates)).'% - 1px);"><time>'.$key.'</time></div>';
			}
			echo '<figcaption>Edited tasks per '.$timespan.'</figcaption>';
		echo '</figure>';
	}
		


	// Function removed for publication (not my function)
	function lang_getfrombrowser ($allowed_languages, $default_language, $lang_variable = null, $strict_mode = true) {
		return $default_language;
	}
	
	# This function returns bibliographical entries as citations using the author-date system
	function citstyle_authordate ($bibentries, $id) {
		
		$text = $bibentries[$id];
		
		if ($text['kind'] == 'Book') { echo trim($text['author'], '.').'. '.$text['year'].'. <i>'.$text['title'].'</i>. '.$text['publisher']; }
		if ($text['kind'] == 'Article') {
			echo trim($text['author'], '.').'. '.$text['year'].'. "'.$text['title'].'". '.$text['journal']; 
			if (isset($text['volume'])) echo ' <i>'.$text['volume'].'</i>'; 
			if (isset($text['number'])) echo ' ('.$text['number'].')';
			if (isset($text['pages'])) echo ', '.$text['pages'];
		}
		if ($text['kind'] == 'InBook') {
			echo trim($text['author'], '.').'. '.$text['year'].'. "'.$text['title'].'" <i>in</i> '.$text['editor']; 
			if (isset($text['pages'])) echo ', '.$text['pages'];
		}
	}

	function http_test_existance($url) {

		return (($fp = @fopen($url, 'r')) === false) ? false : @fclose($fp);

	}

	# This function retrieves data from the OSM API and displays it
	function getgeoinfo ($q) {
		$url = 'https://nominatim.openstreetmap.org/search?q='.$q.'&format=json';
		if (http_test_existance($url) != False) {
			$geodata = json_decode(file_get_contents($url));

			$result = array();

			$result['place_id'] = strval($geodata[0]->place_id);
			$result['osm_id'] = strval($geodata[0]->osm_id);
			$result['name'] = strval($geodata[0]->display_name);
			$result['lon'] = strval($geodata[0]->lon);
			$result['lat'] = strval($geodata[0]->lat);
			
			echo '<div class="additionalcontextinfo">';
			echo '<h4>Geographical Information</h4>';
			echo '<p>Retrieved from <abbr title="Open Street Maps">OSM</abbr> Nominatim</p>';
				
				echo '<dl>';
					echo '<dt>Name</dt><dd>'.$result['name'].'</dd>';
					echo '<dt>Longitude</dt><dd>'.$result['lon'].'</dd>';
					echo '<dt>Latitude</dt><dd>'.$result['lat'].'</dd>';
				echo '</dl>';
				echo '<a href="https://www.openstreetmap.org/#map=12/'.$result['lat'].'/'.$result['lon'].'">Find this place on OSM</a>';

			echo '</div>';
		}
	}

	#This function reads a user's library and returns the keywords sorted by their popularity
	function findfavcategories ($dict, $allbooks, $ignoreedits = False) {

		$categoriescount = array();
		foreach ($allbooks as $entry) {
			if (isset($entry['categories'])) {
				foreach ($entry['categories'] as $categ){
					if (isset($entry['edits']) and isset($entry['edits'][0]) or $ignoreedits == True) {
						if (!in_array($categ, array_keys($categoriescount))) $categoriescount[$categ] = 1;
						else {
						$categoriescount[$categ]++;
						}
					}
				}
			}
		}
		arsort($categoriescount);
		$i = 0;
		
		return ($categoriescount);


	}

?>

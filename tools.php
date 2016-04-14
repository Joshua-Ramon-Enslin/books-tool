<!-- The tools and section windows are embedded into other files. They are embedded using the respective GET variables. -->
<section class="window">

	<h2 class="tools"><?PHP echo $dict->tools->$lan; ?></h2>
	<div class="close"><a href="./<?PHP if (isset($q)) echo '?q='.$q; ?>">&#65336;</a></div>
		
		<h3><?PHP echo $dict->switchlang->$lan; ?></h3>
		<form enctype="multipart/form-data" action="./" method="GET">
			<select name="lan">
				<option value="en"><?PHP echo $dict->english->$lan; ?></option>
				<option value="de"><?PHP echo $dict->german->$lan; ?></option>
				<option value="id"><?PHP echo $dict->indonesian->$lan; ?></option>
			</select>
			<button type="submit"><?PHP echo $dict->send->$lan; ?></button>
		</form>
		<hr />
		
		<h3><?PHP echo $dict->uploadbiblio->$lan; ?></h3>
		<form enctype="multipart/form-data" action="_upload.php" method="POST">
		
			<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
			<input type="hidden" name="uploadfolder" value="allbooks/<?PHP echo $_SESSION['username']; ?>/" />
			<input name="userfile" type="file" required />
			<span class="helpline">
				Here you can upload a bibliographical list in the form of a .bib file.
				It is possible that you need to export your bibliography first.
			</span>
			<button type="submit"><?PHP echo $dict->send->$lan; ?></button>
		</form>
		
		<hr />
		<h3><?PHP echo $dict->dlbibliography->$lan; ?></h3>
		<h4><?PHP echo $dict->yourbibliography->$lan; ?></h4>
		<ul>
			<?PHP
			foreach ($tasklists as $task) {
				echo '<li><a title="Download '.$task.'.xml" href="'.$task.'.xml">'.basename($task).'.xml</a> (<a href="delete-bib.php?q='.basename($task).'.xml" title="Delete this bibliographical list" class="deletebib">Delete</a>)</li>';
			}
			?>
		</ul>
		<h4><?PHP echo $dict->collectivebibliography->$lan; ?></h4>
		<ul>
			<li><a href="export-bibtex.php?all"><?PHP echo $dict->dlbibliography->$lan; ?></a></li>
		</ul>

</section>

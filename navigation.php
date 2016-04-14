
	<header>
		<h1><a href="<?PHP echo $pageinfo['titleurl']; ?>"><?PHP echo $pageinfo['title']; ?></a> :: <a href="./">Books</a></h1>
		<img class="logo" alt="logo" src="books.svg" />
	</header>
	
	<?PHP 
		if (file_exists('../!shared/projectslink.php')) include ('../!shared/projectslink.php');
	?>
	
	<nav>

		<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) echo '<div>'; ?>		
			<a href="./<?PHP if (isset($peek)) echo '?peek='.$peek; ?>"><?PHP echo $dict->overview->$lan; ?></a>

			<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) { ?>
			<div class="helphover">
				<h4>Overview / Start Page</h4>
				<p>Here you can see a stream of all the texts you have added.</p>
			</div>
		</div>
		<?PHP } ?>
		<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) echo '<div>'; ?>
			<a href="./stats.php<?PHP if (isset($peek)) echo '?peek='.$peek; ?>"><?PHP echo $dict->statistics->$lan; ?></a>
			<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) { ?>
			<div class="helphover">
				<h4>Statistics</h4>
				<p>On the statistics page you can access some data collated from the books you added.</p>
			</div>
		</div>
		<?PHP } ?>
			<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) echo '<div>'; ?>		
			<a href="http://jrenslin.de/tools">?</a>	
			<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) { ?>
			<div class="helphover">
				<h4>Documentation</h4>
				<p>A more thorough documentation of the functions and ways of this tool can be found here.</p>
			</div>
		</div>
		<?PHP } ?>

		<div class="search">
			<form method="get" enctype="multipart/form-data" action="./">
				<?PHP if (isset($peek)) echo '<input type="hidden" name="peek" value="'.$peek.'" />'.PHP_EOL; ?>
				<input type="text" name="s" placeholder="<?PHP echo $dict->search->$lan; ?>" <?PHP if (isset($s)) echo 'value="'.$s.'"'; ?> />
				<select name="searchkey">
					<option value="s">Tag</option>
					<option value="au">Au</option>
					<option value="ti">Ti</option>
					<option value="pb">Pb</option>
					<option value="jo">Jo</option>
				</select>
				<button type="submit">&#128270;</button>
				<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) { ?>
				<div class="helphover">
					<h4>Search Function</h4>
					<p>Using this function, you can search for books you have previously added.</p>
					<p>In the middle, you can select what you want to search for:
						<dl>
							<dt>Tag</dt><dd>Search by tag.</dd>
							<dt>Au</dt><dd>Search by author.</dd>
							<dt>Ti</dt><dd>Search by title.</dd>
							<dt>Pb</dt><dd>Search by publisher.</dd>
							<dt>Jo</dt><dd>Search by journal.</dd>
						</dl>
					</p> 
				</div>
				<?PHP } ?>
			</form>
		</div>

		<?PHP if ((!(isset($peek)))and($username == $adminusr)and(file_exists('./admin.php'))) echo '<a href="./admin.php">'.$dict->adminpanel->$lan.'</a>'; ?>
		
		<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) echo '<div>'; ?>
			<a href="./profile.php<?PHP if (isset($peek)) echo '?peek='.$peek; ?>"><?PHP echo $dict->profile->$lan; ?></a>	
			<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) { ?>
			<div class="helphover">
				<h4>Profile</h4>
				<p>Your profile page. On the profile page data about you is shown. Other users can also see this page, commonly after finding it through the <i>ranking</i> page.</p>
			</div>
		</div>
		<?PHP } ?>
		<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) echo '<div>'; ?>
			<a href="./projects.php<?PHP if (isset($peek)) echo '?peek='.$peek; ?>"><?PHP echo $dict->projects->$lan; ?></a>	
			<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) { ?>
			<div class="helphover">
				<h4>Projects</h4>
				<p>Using the projects function, you can set up, yes, projects. After having created a project entry, e.g. for an article you want to write or for a topic you want to descibe, you can then link selected texts to the project and note down the most topically relevant quotations. You may also upload files associated with the project.</p>
			</div>
		</div>
		<?PHP } ?>
		<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) echo '<div>'; ?>
			<a href="./ranking.php<?PHP if (isset($peek)) echo '?peek='.$peek; ?>"><?PHP echo $dict->ranking->$lan; ?></a>	
			<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) { ?>
			<div class="helphover">
				<h4>Ranking</h4>
				<p>Using the ranking page you can access other users' reading data.</p>
				<p>You may also filter them by the keywords associated with the texts they've read. This function has been named the <q>find an expert</q> function.</p>
			</div>
		</div>
		<?PHP } ?>
		<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) echo '<div>'; ?>
			<a href="./lists.php<?PHP if (isset($peek)) echo '?peek='.$peek; ?>"><?PHP echo $dict->lists->$lan; ?></a>	
			<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) { ?>
			<div class="helphover">
				<h4>Lists</h4>
				<p>Using the lists function, users can find all journals, publishers or authors from whom they have read texts.</p>
			</div>
		</div>
		<?PHP } ?>
		<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) echo '<div>'; ?>
			<a href="./unread.php<?PHP if (isset($peek)) echo '?peek='.$peek; ?>"><?PHP echo $dict->findmoretexts->$lan; ?></a>	
			<?PHP if (isset($settingsxml) and isset($settingsxml->helpyesno) and intval($settingsxml->helpyesno) == 1) { ?>
			<div class="helphover">
				<h4>Unread Texts</h4>
				<p>Using this function, you can find texts that have been added to the bibliographical database, but not yet been linked with you. The search function for unread texts works in similar ways to the usual search function.</p>
			</div>
		</div>
		<?PHP } ?>
		
		<div>
			<a href="./?additionalfunctions=1">Additional Functions</a>
		</div>

		<div class="logininfo">
			<?PHP if (!(isset($peek))) { echo $dict->currentlyloggedinas->$lan; ?>:<br /><span id="username"><?PHP echo $username; ?></span> <a href="./?logout=yes">(<?PHP echo $dict->logout->$lan; ?>)</a> <?PHP }
			else echo $dict->currentlyviewingdata->$lan.' <span id="username">'.$username.'</span>'; ?>
		</div>

	</nav>


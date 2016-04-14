<!-- The tools and section windows are embedded into other files. They are embedded using the respective GET variables. -->
<section class="window">
	<h2><?PHP echo $dict->settingsanduserinfo->$lan; ?></h2>
	<div class="close"><a href="./<?PHP if (isset($q)) echo '?q='.$q; ?>">&#65336;</a></div>
	
	<form id="addtask" method="get" enctype="multipart/form-data" action="settings-write.php">
	<dl>
	
		<dt><?PHP echo $dict->username->$lan; ?></dt>
		<dd><input value="<?PHP echo $username; ?>" disabled /></dd>
		
		<?PHP if (isset($q)) echo '<input type="hidden" name="q" value="'.$q.'" />'; ?>
		<dt><?PHP echo $dict->name->$lan; ?></dt>
		<dd><input type="text" name="name" value="<?PHP echo $settingsxml->name ?>" /></dd>

		<dt><?PHP echo $dict->emailaddress->$lan; ?></dt>
		<dd><input type="text" name="email" value="<?PHP echo $settingsxml->email ?>" /></dd>
		
		<dt><?PHP echo $dict->displayemailbelow->$lan; ?></dt>
		<dd>
		<?PHP
			if (strval($settingsxml->emailpublic) == '1') echo '<input type="radio" name="emailpublic" value="1" id="emailpublicyes" checked /><label for="emailpublicyes">'.$dict->yes->$lan.'</label> <input type="radio" name="emailpublic" value="0" id="emailpublicno" /><label for="emailpublicno">'.$dict->no->$lan.'</label>';
			else echo '<input type="radio" name="emailpublic" value="1" id="emailpublicyes" /><label for="emailpublicyes">'.$dict->yes->$lan.'</label> <input type="radio" name="emailpublic" value="0" id="emailpublicno" checked /><label for="emailpublicno">'.$dict->no->$lan.'</label>';
		?>
		</dd>
		
		<div style="border:1px solid #ccc;border-radius:5px;padding:0px 5px 0px 10px;">
		<h3><?PHP echo $dict->additionalinfo->$lan; ?></h3>
		
		<dt>Homepage</dt>
		<dd><input type="text" name="homepage" value="<?PHP echo $settingsxml->homepage; ?>" /></dd>
		
		<dt><?PHP echo $dict->profilepic->$lan; ?></dt>
		
		<dd><div class="imgchecker"><img id="imgcheck" /><p onclick="document.getElementById('imgcheck').src = document.getElementById('imagefield').value"><?PHP echo $dict->checkimage->$lan; ?></p></div>
		<input type="text" name="image" id="imagefield" value="<?PHP echo $settingsxml->image; ?>" placeholder="http://example.com/example.jpg" /></dd>
		
		<dt><?PHP echo $dict->job->$lan; ?></dt>
		<dd><input type="text" name="job" value="<?PHP echo $settingsxml->job; ?>" /></dd>
		
		<dt><?PHP echo $dict->affiliation->$lan; ?></dt>
		<dd><input type="text" name="affiliation" value="<?PHP echo $settingsxml->affiliation; ?>" /></dd>
		
		<dt><?PHP echo $dict->city->$lan; ?></dt>
		<dd><input type="text" name="city" value="<?PHP echo $settingsxml->city; ?>" /></dd>
		
		<dt><?PHP echo $dict->description->$lan; ?></dt>
		<dd><textarea name="description" class="mceEditor"><?PHP echo reconstruct_html($settingsxml->description); ?></textarea></dd>
		
		</div>

		<dt>CSS</dt>
		<dd><input type="text" name="css" value="<?PHP echo $settingsxml->css; ?>" /></dd>

		<dt>Show Help</dt>
		<dd>
		<?PHP
			if (strval($settingsxml->helpyesno) == '1') echo '<input type="radio" name="helpyesno" value="1" id="helpyes" checked /><label for="helpyes">'.$dict->yes->$lan.'</label> <input type="radio" name="helpyesno" value="0" id="helpno" /><label for="helpno">'.$dict->no->$lan.'</label>';
			else echo '<input type="radio" name="helpyesno" value="1" id="helpyes" /><label for="helpyes">'.$dict->yes->$lan.'</label> <input type="radio" name="helpyesno" value="0" id="helpno" checked /><label for="helpno">'.$dict->no->$lan.'</label>';
		?>
		</dd>
		
	</dl>
	
	<button type="submit"><?PHP echo $dict->send->$lan; ?></button>
	</form>
	
</section>

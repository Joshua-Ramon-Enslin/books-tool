<?php

### General upload function
# As uploading data is only enabled for bibtex files right now, restrictions were set up or that.
if (!isset($myhomepage)) include ('./first.php');

if (!isset($_SESSION['username'])) die('No username specified!');
if (!(is_dir('./allbooks/'.$_SESSION['username'].'/'))) mkdir('./allbooks/'.$_SESSION['username'].'/');

if ($_POST['uploadfolder'] == './allbooks/'.$_SESSION['username'].'/') $allowedfileext = array('bib');
else $allowedfileext = array('bib', 'mp4', 'webm', 'mp3', 'jpg', 'jpeg', 'png', 'svg', 'xlsx', 'xml', 'txt');

$uploaddir = './filedump/';
if (isset($_POST['uploadfolder'])) $uploaddir = './'.$_POST['uploadfolder'];

$uploadfile = $uploaddir . str_replace(' ', '', basename($_FILES['userfile']['name']));
echo $uploadfile;
$fileext = pathinfo($uploadfile, PATHINFO_EXTENSION);

if ($_FILES['userfile']['size'] > 2000000) die($dict->filetoolarge->$lan);
if (in_array($fileext, strtolower($allowedfileext))) die($dict->extnotallowed->$lan);

if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
  if ($_POST['uploadfolder'] == './allbooks/'.$_SESSION['username'].'/') header ('Location: ./?tools=1&uploaded=1');
  else header ('Location: ./projects.php');
  
} else {
   die ('<h1>Upload failed.</h1>');
}



?> 

<?PHP

	/* This file is loaded before every other file. It includes the necessary functions and sets up some essential variables which can be used for customization */
	
	if (session_status() != PHP_SESSION_ACTIVE) session_start();
	if (file_exists('functions.php')) include ('functions.php');
	else die();
  
	/* ## Page information ## */
	$adminusr = 'admin';
	$adminemail = 'example@example.com';
	
	/* Page title and link to main page (if that exists) */
	$pageinfo = array();
	$pageinfo['title'] = 'Some Page';
	$pageinfo['titleurl'] = 'http://example.com';
	
	$myhomepage = 'http://example.com/books/';

	define ('secondsinday', 86400);
	
	/* Load dictionary and determine language */
	if (file_exists('./dictionary.xml')) $dict = new SimpleXmlElement(file_get_contents('./dictionary.xml'));
	else die();
  
	$availablelangs = ['en', 'de', 'id'];
	if (isset($_GET['lan'])) { $lan = $_GET['lan']; $_SESSION['lan'] = $_GET['lan']; }
	if (isset($_SESSION['lan'])) $lan = $_SESSION['lan'];
	else if (!(isset($_SESSION['lan']))) $_SESSION['lan'] = lang_getfrombrowser($availablelangs, 'en', null, false); 
	if (!(isset($lan))) $lan = 'en';

	/* Array for file types */
	$filetypes = array();
	$filetypes['video'] = array('mp4', 'webm'); 
	$filetypes['audio'] = array('mp3', 'ogg'); 
	$filetypes['text'] = array('txt', 'xml');  
	$filetypes['pdf'] = array('pdf'); 
	$filetypes['presentations'] = array('ppt', 'odp', 'pptx');

?>

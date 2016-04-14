<?PHP

include ('first.php');
include ('password_protect.php');

if (isset($_GET['id'])) $id = $_GET['id'];
if (isset($_GET['covertodl'])) $covertodl = $_GET['covertodl'];

if (strpos(' '.$covertodl, 'http://covers.openlibrary.org/b/isbn') != 1) die('<h1>This is no cover from open library...</h1><p>Currently you can only import book covers from open library into this page. For security reasons all other websites are blocked.</p>');

if (!(is_file('./media/books/'.$id.'.jpg'))) file_put_contents('./media/books/'.$id.'.jpg', file_get_contents($covertodl));

$to = $adminemail;

mail($to, 'New book cover added', 'A new book cover was added for entry '.$id.'. Added by '.$_SESSION['username'].'. Source URL: '.$covertodl);
header ('Location: ./?q='.$id);

?>
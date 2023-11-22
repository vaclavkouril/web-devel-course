<!doctype html>
<h1>Articles</h1>

<form>

<?php	
$host = 'localhost';
$user = '54437910@localhost';
$password = 'bdLL1DqH';
$database = 'stud_54437910';

$connection = mysqli_connect($host, $user, $password, $database);
	
if (!$connection) { echo 'there has been an erorr connecting'; }

$query = 'Select * From articles Where';
	
mysqli_close($connection);
?>
<ul>
<?php foreach ($data as $item) {?>
    <li>
    <?php htmlspecialchars($item["name"]); ?>
    <?php htmlspecialchars($item["published"] ?? 'Not Published'); ?>
        <p>
            <?php htmlspecialchars($item["preview"]); ?>
        </p>
    </li>
<?php }?>
</ul>

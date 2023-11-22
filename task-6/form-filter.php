<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Articles</title>
</head>
<body>

<h1>Articles</h1>

<form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <label for="published">Published:</label>
    <select name="published">
        <option value="">All</option>
        <option value="1">Published</option>
    </select>

    <label for="preview">Preview contains:</label>
    <input type="text" name="preview" value="<?php echo htmlspecialchars($_GET['preview'] ?? ''); ?>">

    <button type="submit">Filter</button>
</form>

<?php
$host = 'localhost';
$user = '54437910';
$password = 'REDACTED';
$database = 'stud_54437910';

$connection = mysqli_connect($host, $user, $password, $database);

if (!$connection) {
    die('There has been an error connecting');
}

$query = 'SELECT * FROM articles';

$whereConditions = array();
if (isset($_GET['published']) && $_GET['published'] === '1') {
    $whereConditions[] = 'published IS NOT NULL';
}

if (isset($_GET['preview']) && !empty($_GET['preview'])) {
    $whereConditions[] = 'preview LIKE "%' . mysqli_real_escape_string($connection, $_GET['preview']) . '%"';
}

if (!empty($whereConditions)) {
    $query .= ' WHERE ' . implode(' AND ', $whereConditions);
}

$result = mysqli_query($connection, $query);

if ($result) {
    echo '<ul>';
    while ($item = mysqli_fetch_assoc($result)) {
        echo '<li>';
        echo htmlspecialchars($item["name"]);
        echo htmlspecialchars($item["published"] ?? 'Not Published');
        echo '<p>';
        echo htmlspecialchars($item["preview"]);
        echo '</p>';
        echo '</li>';
    }
    echo '</ul>';
    mysqli_free_result($result);
} else {
    echo 'Error executing query: ' . mysqli_error($connection);
}

mysqli_close($connection);
?>

</body>
</html>

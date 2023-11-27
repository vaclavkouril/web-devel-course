<!DOCTYPE html>
<html>
<head>
    <title>My Web Page</title>
</head>
<body>

<?php
$name = 'Ailish';
$age = 32;
$title = '<AI>';

// Display the name property
echo "<h1>Name: $name</h1>";

// Display the age property
echo "<p>Age: " . htmlspecialchars($age) . "</p>";

// Display the title property (note: using htmlspecialchars to escape special characters)
echo "<p>Title: " . htmlspecialchars($title) . "</p>";

$counter = filter_input(INPUT_GET, "counter", FILTER_VALIDATE_INT);

// Add a link to navigate to counter - 1
$prevCounter = $counter - 1;
echo "<p><a href='?counter=$prevCounter'>Decrement</a></p>";

// Add a link to navigate to counter + 1
$nextCounter = $counter + 1;
echo "<p><a href='?counter=$nextCounter'>Increment</a></p>";
    echo "<h1>" . $counter . "</h1>"
        
        
        ?>
</body>
</html>

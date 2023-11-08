<!DOCTYPE html>
<html>
<head>
    <title>Submission Result</title>
</head>
<body>
    <?php
    // Check if all form fields are provided
    if (isset($_GET['name']) && isset($_GET['age']) && isset($_GET['luck']) && isset($_GET['intelligence'])) {
        $name = $_GET['name'];
        $age = (int)$_GET['age'];
        
        if ($age >= 0 && $age < 100) {
            // Display a message based on age
            if ($age <= 6) {
                echo "You are so cute, $name.";
            } elseif ($age <= 18) {
                echo "Hello young one.";
            } else {
                echo "Greetings, $name.";
            }
        } else {
            header("Location: form.php?name=$name&age=$age&luck={$_GET['luck']}&intelligence={$_GET['intelligence']}&invalid=submit");
            exit();
        }
    } else {
        header("Location: form.php?invalid=submit");
        exit();
    }
    ?>
</body>
</html>

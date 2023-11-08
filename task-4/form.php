<!DOCTYPE html>
<html>
<head>
    <title>Form Page</title>
</head>
    <body>
    <?php
    if (isset($_GET['invalid']) && $_GET['invalid'] === 'submit') {
    echo "<p style='color: red;'>Submission failed.</p>";
    }
    ?>
    <h1>Submit Your Information</h1>
    <form action="submit.php" method="GET">
        <label for="name">Name: </label>
        <input type="text" id="name" name="name" required value="<?php echo isset($_GET['name']) ? $_GET['name'] : ''; ?>"><br><br>

        <label for="age">Age: </label>
        <input type="number" id="age" name="age" value="<?php echo isset($_GET['age']) ? $_GET['age'] : ''; ?>"><br><br>

        <label for="luck">Luck: </label>
        <input type="number" id="luck" name="luck" value="<?php echo isset($_GET['luck']) ? $_GET['luck'] : ''; ?>"><br><br>

        <label for="intelligence">Intelligence: </label>
        <input type="number" id="intelligence" name="intelligence" value="<?php echo isset($_GET['intelligence']) ? $_GET['intelligence'] : ''; ?>">
            
        <input type="submit" value="Submit">
    </form>
</body>
</html>

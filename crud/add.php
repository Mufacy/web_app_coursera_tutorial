<?php
require_once "pdo.php";
session_start();

if (!isset($_SESSION['name']))
{
    die("ACCESS DENIED");
}

if ( isset($_POST['make']) && isset($_POST['model'])
   && isset($_POST['year']) && isset($_POST['mileage'])) 
{
    // Data validation
    if ( strlen($_POST['make']) < 1 || strlen($_POST['model']) < 1 || !is_numeric($_POST['year']) || !is_numeric($_POST['mileage'])) 
    {
        $_SESSION['error'] = "All fields are required";
        header("Location: add.php");
        return;
    }

    $sql = "INSERT INTO autos (make, year, mileage, model)
              VALUES (:make, :year, :mileage, :model)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute
    (
        array(
        ':make' => $_POST['make'],
        ':year' => $_POST['year'],
        ':mileage' => $_POST['mileage'],
        ':model' => $_POST['model']
        )
    );
    $_SESSION['success'] = 'Record Added';
    header( 'Location: index.php' ) ;
    return;
}

// Flash pattern
if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
?>
<head> <title> Mohamad Mouaz Al Midani's CRUD</title> </head>
<p>Tracking Automobiles for <?= $_SESSION['name'] ?> </p>
<form method="post">
<p>Make:
<input type="text" name="make"></p>
<p>Model:
<input type="text" name="model"></p>
<p>Year:
<input type="text" name="year"></p>
<p>Mileage:
<input type="text" name="mileage"></p>
<p><input type="submit" value="Add"/>
<a href="index.php">Cancel</a></p>
</form>
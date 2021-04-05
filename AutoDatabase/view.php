<?php
    session_start();
    include_once "./PDO.php";

    if (!isset($_SESSION['name']))
    {
        die("not logged in");
    }
    else
    {
        $name = $_SESSION['name'];
    }

    if ( isset($_SESSION["success"]) ) 
    {
        echo('<p style="color:green">'.$_SESSION["success"]."</p>\n");
        unset($_SESSION["success"]);
    }  

    $databaseService =  new databaseService();
    $conn = $databaseService->getConnection();

    $query = "SELECT * FROM autos";

    $stmt = $conn->prepare($query);
?>
<html>
<head> <title> Mohamad Mouaz Al Midani Automobile Tracker </title> </head>
<body>
    <h1> Tracking autos for <?= $name ?></h1>
    <h1> Automobiles </h1>
    <?php
        if ($stmt->execute())
        {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                echo ("<p> Id: ". htmlentities($row['Task_Descr']));
                echo (" make: ". htmlentities($row['Task_Detail']));
                echo (" year: ". htmlentities($row['Task_Start_Date']));
                echo (" mileage: ". htmlentities($row['Task_End_Date'] . "</p>\n"));
            }
        }
    ?>
    <a href="add.php">Add New</a> |
    <a href="logout.php">Logout</a>
</body>
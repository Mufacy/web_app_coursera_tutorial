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

    if(isset($_POST['make']) && isset($_POST['year']) && isset($_POST['mileage']))
    {
        $_SESSION['make']    = $_POST['make'];
        $_SESSION['year']    = $_POST['year'];
        $_SESSION['mileage'] = $_POST['mileage'];
        header("Location: add.php");
    }

    if(isset($_SESSION['make']) && isset($_SESSION['year']) && isset($_SESSION['mileage']))
    {
        $make = $_SESSION['make'];
        $year = $_SESSION['year'];
        $mileage = $_SESSION['mileage'];
        
        if(strlen($make) < 1)
        {
            $_SESSION['failed'] = "Make is required";
            header("Location: add.php");
        // echo('<p "style=color:red">Make is required </p> \n');
        }
        else
        if (!is_numeric($year) || !is_numeric($mileage))
        {
            $_SESSION['failed'] = "Mileage and year must be numeric";
            header("Location: add.php");
        // echo('<p "style=color:red">Mileage and year must be numeric </p> \n');  
        }
        else
        {
            $databaseService = new databaseService();
            $conn = $databaseService->getconnection();

            $query = "INSERT INTO autos SET make = :make, year = :year, mileage = :mileage";

            $stmt = $conn->prepare($query);

            $stmt->bindParam(':make', $make);
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':mileage', $mileage);

            if($stmt->execute())
            {
                $_SESSION["success"] = "Record inserted";
                header("Location: view.php");
            }
        }
    }
?>

<html>
<head> <title>Mohamad Mouaz Al Midani's Automobile Tracker</title> </head>
<body style="font-family: sans-serif;">
    <div class="container">
    <h1>Tracking Autos for <?= $name ?> </h1>
    <?php 
        if ( isset($_SESSION["failed"]) ) 
        {
            echo('<p style="color:red">'.$_SESSION["failed"]."</p>\n");
            unset($_SESSION["failed"]);
        }  
    
        // Check if we are logged in!
        if ( ! isset($_SESSION["account"]) ) 
        { ?>
        <p>Please <a href="login.php">Log In</a> to start.</p>
        <?php } 
        else 
        { ?>
        <p>This is where a cool application would be.</p>
        <p>Please <a href="logout.php">Log Out</a> when you are done.</p>
        <?php } ?>
        <div class="container">
            <form method="post">
                <p>Make: <input type="text" name="make" size="60"/></p>
                <p>Year: <input type="text" name="year"/></p>
                <p>Mileage: <input type="text" name="mileage"/></p>
                <input type="submit" value="Add">
                <input type="submit" name="cancel" value="Cancel">
            </form>
        </div>
    </body>
</html>
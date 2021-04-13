<?php
    include_once "./PDO.php";
    include_once "./util.php";
    include_once "./head.php";
    session_start();

    if ( ! isset($_GET['profile_id']) ) 
    {
        $_SESSION['error'] = "Missing profile_id";
        header('Location: index.php');
        return;
    }

    $databaseService = new databaseService();
    $conn = $databaseService->getconnection();

    $pid = $_GET['profile_id'];

    $query = "SELECT user_id, profile_id, first_name, last_name, email, headline, summary
              FROM profile where profile_id = :profile_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':profile_id', $pid);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $uid      = htmlentities($row['user_id']);
    $fname    = htmlentities($row['first_name']);
    $lname    = htmlentities($row['last_name']);
    $email    = htmlentities($row['email']);
    $headline = htmlentities($row['headline']);
    $summ     = htmlentities($row['summary']);
    $pid      = htmlentities($row['profile_id']);
?>
<head> <title>Mohamad Mouaz Al Midani's Profile View</title> <head>
<body>
<h1> Profile Information </h1>
<p>First Name: <?= $fname ?></p>
<p>Last Name: <?= $lname  ?></p>
<p>Email: <?= $email ?></p>
<p>Headline: <?= $headline ?></p>
<p>Summary: <?= $summ ?></p>
<?php

    //getting all the positions
    $positions = loadPos($conn, $pid);
    if (count($positions) > 0 )
    {
        echo('<p> Position: </p>');
        echo('<ul>');
        for($i = 0; $i < count($positions); $i++)
        {
            $row = $positions[$i];
            echo('<li>');
            echo(htmlentities($row['year']).': ' . htmlentities($row['description']));
            echo('</li>');
        }
        echo('</ul>');
    }    

    //getting all the Education
    $Educations = loadEdu($conn, $pid);
    if (count($Educations) > 0 )
    {
        echo('<p> Education: </p>');
        echo('<ul>');
        for($i = 0; $i < count($Educations) ; $i++)
        {
            $row = $Educations[$i];
            echo('<li>');
            echo(htmlentities($row['year']).': ' . htmlentities($row['name']));
            echo('</li>');
        }
        echo('</ul>');
    }    
?> 
<a href="index.php">Done</a>
</body>
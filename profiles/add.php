
<?php
    include_once "./PDO.php";
    session_start();

    if (!isset($_SESSION['name']) || !isset( $_SESSION['user_id'] ))
    {
        die("ACCESS DENIED");
    }

    if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email'])
        && isset($_POST['headline']) && isset($_POST['summary']))
    {
        $uid = $_SESSION['user_id'];
        $fname = $_POST['first_name'];
        $lname = $_POST['last_name'];
        $email = $_POST['email'];
        $headline = $_POST['headline'];
        $summ = $_POST['summary'];
        
        if(strlen($fname) < 1 || strlen($lname) < 1 || strlen($email) < 1 || strlen($headline) < 1 ||strlen($summ) < 1)
        {
            $_SESSION['error'] = 'All fields are required';
            header('Location:add.php');
            return;
        }
        
        if (strpos($email,'@') < 1)
        {
            $_SESSION['error'] = 'Email address must contain @';
            header('Location:add.php');
            return;
        }

        $databaseService = new databaseService();
        $conn = $databaseService->getconnection();

        $query = "INSERT INTO profile SET user_id = :uid,
                            first_name = :fname, last_name = :lname,
                            email = :email, headline = :headline,
                            summary = :summary";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':uid',$uid);
        $stmt->bindParam(':fname',$fname);
        $stmt->bindParam(':lname',$lname);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':headline',$headline);
        $stmt->bindParam(':summary',$summ);

        if($stmt->execute())
        {
            $_SESSION['success'] = "Profile added";
            header('Location:index.php');
            return;
        }
        else
        {
            $_SESSION['error'] = "Failed to add profile";
            header('Location:add.php');
            return;
        }
    }
    
    $name = $_SESSION['name'];
?>
<head>
    <title>Mohamad Mouaz Al Midani's Profile Add</title>
</head>
    <body>
    <div class="container">
        <h1>Adding Profile for <?= $name ?></h1>
        <?php
            if(isset($_SESSION['error']))
            {
                echo ('<p style="color:red;">'.$_SESSION['error']."</p>\n");
                unset($_SESSION['error']);
            }
        ?>
        <form method="post">
            <p>First Name:
            <input type="text" name="first_name" size="60"/></p>
            <p>Last Name:
            <input type="text" name="last_name" size="60"/></p>
            <p>Email:
            <input type="text" name="email" size="30"/></p>
            <p>Headline:<br/>
            <input type="text" name="headline" size="80"/></p>
            <p>Summary:<br/>
            <textarea name="summary" rows="8" cols="80"></textarea>
            <p>
            <input type="submit" value="Add">
            <input type="submit" name="cancel" value="Cancel">
            </p>
        </form>
    </div>
</body>
</html>
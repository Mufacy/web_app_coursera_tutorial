
<?php
    include_once "./PDO.php";
    session_start();

    if (!isset($_SESSION['name']) || !isset( $_SESSION['user_id'] ))
    {
        die("ACCESS DENIED");
    }

    if ( ! isset($_GET['profile_id']) ) 
    {
        $_SESSION['error'] = "Missing profile_id";
        header('Location: index.php');
        return;
    }

    $databaseService = new databaseService();
    $conn = $databaseService->getconnection();

    $pid = $_GET['profile_id'];

    if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email'])
        && isset($_POST['headline']) && isset($_POST['summary']) && isset($_POST['profile_id']))
    {
        $uid      = $_SESSION['user_id'];
        $fname    = $_POST['first_name'];
        $lname    = $_POST['last_name'];
        $email    = $_POST['email'];
        $headline = $_POST['headline'];
        $summ     = $_POST['summary'];
        
        if(strlen($fname) < 1 || strlen($lname) < 1 || strlen($email) < 1 || strlen($headline) < 1 ||strlen($summ) < 1)
        {
            $_SESSION['error'] = 'All fields are required';
            header('Location:edit.php?profile_id='.$pid);
            return;
        }

        if (strpos($email,'@') < 1)
        {
            $_SESSION['error'] = 'Email address must contain @';
            header('Location:edit.php?profile_id='.$pid);
            return;
        }

        $query = "UPDATE profile SET user_id = :uid,
                        first_name = :fname, last_name = :lname,
                        email = :email, headline = :headline,
                        summary = :summary
                WHERE profile_id = :pid";

        $stmt = $conn->prepare($query);

        $stmt->bindParam(':uid',$uid);
        $stmt->bindParam(':fname',$fname);
        $stmt->bindParam(':lname',$lname);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':headline',$headline);
        $stmt->bindParam(':summary',$summ);
        $stmt->bindParam(':pid',$pid);

        if($stmt->execute())
        {
            $_SESSION['success'] = "Profile updated";
            header('Location:index.php');
            return;
        }
        else
        {
            $_SESSION['error'] = "Failed to update profile";
            header('Location:edit.php?profile_id='.$pid);
            return;
        }
    }

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
    $pid      = $row['profile_id'];

    $name     = $_SESSION['name'];
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
            <input type="text" name="first_name" size="60" value="<?= $fname ?>"/></p>
            <p>Last Name:
            <input type="text" name="last_name" size="60" value="<?= $lname ?>"/></p>
            <p>Email:
            <input type="text" name="email" size="30" value="<?= $email ?>"/></p>
            <p>Headline:<br/>
            <input type="text" name="headline" size="80" value="<?= $headline ?>"/></p>
            <p>Summary:<br/>
            <textarea name="summary" rows="8" cols="80"><?= $summ ?></textarea>
            <p>
            <input type="hidden" name="profile_id" value="<?= $pid ?>"/>
            <input type="submit" value="Save">
            <input type="submit" name="cancel" value="Cancel">
            </p>
        </form>
    </div>
</body>
</html>
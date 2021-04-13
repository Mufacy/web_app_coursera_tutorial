
<?php
    include_once "./PDO.php";
    include_once "./util.php";
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
        //validating the profile input
        $msg = validateProfile();
        if (is_string($msg))
        {
            $_SESSION['error'] = $msg;
            header('Location: add.php');
            return;
        }

        //validating the position data
        $msg = validatePos();
        if (is_string($msg))
        {
            $_SESSION['error'] = $msg;
            header('Location: add.php');
            return;
        }

        $uid      = $_SESSION['user_id'];
        $fname    = $_POST['first_name'];
        $lname    = $_POST['last_name'];
        $email    = $_POST['email'];
        $headline = $_POST['headline'];
        $summ     = $_POST['summary'];
        
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
            $profile_id = $_REQUEST['profile_id'];
            //delete all old entries
            $stmt = $conn->prepare('DELETE FROM Position WHERE profile_id = :profile_id');

            $stmt->bindParam(':profile_id', $profile_id);

            $stmt->execute();

            //insert 
            $stmt = $conn->prepare('INSERT INTO Position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)');

            $rank = 1;
            for($i=1; $i<=9; $i++) 
            {
                if ( ! isset($_POST['year'.$i]) ) continue;
                if ( ! isset($_POST['desc'.$i]) ) continue;

                $year = $_POST['year'.$i];
                $desc = $_POST['desc'.$i];
                $stmt = $conn->prepare('INSERT INTO Position
                    (profile_id, rank, year, description)
                    VALUES ( :pid, :rank, :year, :desc)');

                $stmt->execute(array(
                ':pid' => $profile_id,
                ':rank' => $rank,
                ':year' => $year,
                ':desc' => $desc)
                );

                $rank++;
            }
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

    //fetch profile data
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
    
    //fetch positions data
    $query = "SELECT rank, year, description FROM position where profile_id = :profile_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':profile_id', $pid);
    $stmt->execute();
?>
<head>
    <title>Mohamad Mouaz Al Midani's Profile Edit</title>
    <script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
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
            <p>
            Position: <input type="submit" value="+" id="addPos">
            </p>
            <div id="position_fields">
            <?php
                //getting all the positions
                $posCount = 0;
                while($row = $stmt->fetch(PDO::FETCH_ASSOC))
                {
                    $posCount++;
                    echo('<div id="position'.$posCount.'">');
                    echo('<p>Year: <input type="text" name="year'.$posCount.'" value="'.htmlentities($row['year']).'">');
                    echo('<input type="button" value="-" onclick="$('."'#position".$posCount."').remove();  return false;".'"></p>');
                    echo('<textarea name="desc' . $posCount . '" rows="8" cols="80">'. htmlentities($row['description']) .'</textarea> </div>');
                }
            ?>
            </div>
            <input type="hidden" name="profile_id" value="<?= $pid ?>"/>
            <input type="submit" value="Save">
            <input type="submit" name="cancel" value="Cancel">
            </p>
        </form>
    </div>
    <script>
        function validateUser()
        {
            console.log('Validating...');
            try 
            {
                fname       = $('#first_name').val();
                lname       = $('#last_name').val();
                email       = $('#email').val();
                headline    = $('#headline').val();
                summary     = $('#summary').val();
                console.log("Validating Profile");
                if (fname == null || fname == "" || lname == null || lname == ""
                    || headline == null || headline == "" || summary == null || summary == "") 
                {
                    alert("All fields must be filled out");
                    return false;
                }   

                if ( email.indexOf('@') == -1 ) 
                {
                    alert("Invalid email address");
                    return false;
                }
                return true;
            } 
            catch(e) 
            {
                return false;
            }
            return false;
        }

        $(document).ready
        (   
            function ()
            {
            window.console && console.log('Document ready called');
            countPos = <?= $posCount ?>;
            $('#addPos').click
                (
                    function (event)
                    {
                        event.preventDefault();
                        if (countPos >= 9)
                        {
                            alert("Maximum of nine position entries exceeded");
                            return;
                        }
                        countPos++;
                        window.console && console.log("Adding position"+countPos);
                        $('#position_fields').append
                        (
                            '<div id="position'+countPos+'"> \
                             <p>Year: <input type="text" name="year'+countPos+'" value=""> \
                             <input type="button" value="-" onclick="$('+"'#position"+countPos+"').remove(); return false;"+'"></p> \
                             <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea> </div>'
                        );
                    }
                )
            }
        )
    </script>
</body>
</html>
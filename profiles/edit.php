<?php
    include_once "./PDO.php";
    include_once "./util.php";
    include_once "./head.php";
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

    //load up the profile in question
    $stmt = $conn->prepare('SELECT * FROM profile 
                            where profile_id = :prof AND user_id = :uid');
    $stmt->execute(array(':prof' => $_REQUEST['profile_id'],
                         ':uid' => $_SESSION['user_id']));

    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($profile === false)
    {
        $_SESSION['error'] = "Could not load profile";
        header('Location: index.php');
        return;
    }

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

        //validating the Education data
        $msg = validateEdu();
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
            
            //delete all old entries for Position
            $stmt = $conn->prepare('DELETE FROM Position WHERE profile_id = :profile_id');

            $stmt->bindParam(':profile_id', $profile_id);

            $stmt->execute();

            //delete all old entries for Education
            $stmt = $conn->prepare('DELETE FROM education WHERE profile_id = :profile_id');

            $stmt->bindParam(':profile_id', $profile_id);

            $stmt->execute();


            //insert positions and education
            insertPos($conn, $profile_id);
            insertEdu($conn, $profile_id);

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
            School: <input type="text" size="80" name="edu_school1" class="school" value="" />
            <p>
            Education: <input type="submit" value="+" id="addEdu">
            </p>
            <div id="edu_fields">
                <?php
                    $query = "SELECT institution.name, education.year FROM institution join education
                            ON institution.institution_id = education.institution_id
                            where education.profile_id = :profile_id
                            ORDER BY rank";

                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':profile_id', $pid);
                    $stmt->execute();
                    $eduCount = 0;
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC))
                    {
                        $eduCount++;
                        echo('<div id="edu'.$eduCount.'">');
                        echo('<p>Year: <input type="text" name="edu_year'.$eduCount.'" value="'.htmlentities($row['year']).'">');
                        echo('<input type="button" value="-" onclick="$('."'#edu".$eduCount."').remove();  return false;".'"></p>');
                        echo('<p>School: <input type="text" size="80" class="school" name="edu_school'.$eduCount.'" value="'.htmlentities($row['name']).'"> </div>');
                    }
                ?>
            </div>
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
            <p>
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
            $('.school').autocomplete({ source: "school.php" });
            window.console && console.log('Document ready called');
            countEdu = <?= $eduCount ?>;
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

             //add Education on click
             $('#addEdu').click
                (
                    function (event)
                    {
                        event.preventDefault();
                        if (countEdu >= 9)
                        {
                            alert("Maximum of nine education entries exceeded");
                            return;
                        }
                        countEdu++;
                        window.console && console.log("Adding position"+countEdu);
                        var source = $('#edu-template').html();
                        $('#edu_fields').append(source.replace(/@COUNT@/g, countEdu));

                        //Add the event handler to the new ones
                        $('.school').autocomplete({
                        source: "school.php"
                        })
                    }
                    
                )
            }
        )
    </script>

    <!-- HTML with substitution hot spots -->
    <script id="edu-template" type="text">
        <div id="edu@COUNT@">
            <p>Year: <input type="text" name="edu_year@COUNT@" value="" />
            <input type="button" value="-" onclick="$('#edu@COUNT@').remove(); return false;"><br>
            <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
            </p>
        </div>
    </script>
</body>
</html>

<?php
    include_once "./PDO.php";
    include_once "./util.php";
    session_start();

    if (!isset($_SESSION['name']) || !isset( $_SESSION['user_id'] ))
    {
        die("ACCESS DENIED");
    }

    if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email'])
        && isset($_POST['headline']) && isset($_POST['summary']))
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

        $uid = $_SESSION['user_id'];
        $fname = $_POST['first_name'];
        $lname = $_POST['last_name'];
        $email = $_POST['email'];
        $headline = $_POST['headline'];
        $summ = $_POST['summary'];

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
            $profile_id = $conn->lastInsertId();
            
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

                $stmt->bindParam(':pid',$profile_id);
                $stmt->bindParam(':rank',$rank);
                $stmt->bindParam(':year',$year);
                $stmt->bindParam(':desc',$desc);
                
                $stmt->execute();

                $rank++;
            }
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
            <input type="text" name="first_name" id="first_name" size="60"/></p>
            <p>Last Name:
            <input type="text" name="last_name" id="last_name" size="60"/></p>
            <p>Email:
            <input type="text" name="email" id="email" size="30"/></p>
            <p>Headline:<br/>
            <input type="text" name="headline" id="headline" size="80"/></p>
            <p>Summary:<br/>
            <textarea name="summary" id="summary" rows="8" cols="80"></textarea>
            <p>
            Position: <input type="submit" value="+" id="addPos">
            </p>
            <div id="position_fields"></div>
            <input type="submit" value="Add" onclick="return validateUser();">
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

        countPos = 0;
        $(document).ready
        (   function ()
            {
            window.console && console.log('Document ready called');
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
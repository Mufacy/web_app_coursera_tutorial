<?php
    include_once "./PDO.php";
    session_start();

    $databaseService = new databaseService();
    $conn = $databaseService->getConnection();

    $query = "select profile.first_Name, Headline, profile.user_id,
                    profile.profile_id from users join profile ON users.user_id = profile.user_id";

    $stmt = $conn->prepare($query);

    $stmt->execute();
?>
<html>
<head> 
    <title>80d68c37 Resume Registry</title>
</head>

<body>
    <h1>80d68c37 Resume Registry</h1>
    <?php
        if (isset($_SESSION['success']))
        {
            echo ('<p style="color:green;">'.$_SESSION['success']."</p>\n");
            unset($_SESSION['success']);
        }

        if (!isset($_SESSION['user_id']))
        {
            echo('<a href="login.php">Please log in</a>'. "\n");
        }
        else
        {
            echo('<a href="logout.php">Logout</a>'. "\n");
        }
    ?>
    <table border="1">
    
    <tr> 
        <td>Name    </td>
        <td>Headline</td>
        <td>Action  </td>
    </tr>
    
    <?php
        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            echo ("<tr>");
            echo ('<td> <a href="view.php?profile_id='.$row['profile_id'].'">'. htmlentities($row['first_Name']) ."</td>");
            echo ("<td>". htmlentities($row['Headline']) ."</td>");
            if (isset($_SESSION['user_id']) && ($row['user_id'] == $_SESSION['user_id']))
            {
                echo("<td>");
                echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
                echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
                echo("</td>");
            }
            echo ("</tr>\n");
        }

        if (isset($_SESSION['userId']))
        {
            echo ('<a href="add.php" </a>');
        }
    ?>
    </table>

    <?php
        echo('<a href="add.php">Add New Entry </a>');
    ?>
</body>
</html>
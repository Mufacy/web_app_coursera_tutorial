<?php
//error messages
function showMessages()
{
    if (isset($_SESSION['success']))
    {
        echo ('<p style="color:green;">' . $_SESSION['success'] . '</p>');
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error']))
    {
        echo ('<p style="color:red;">' . $_SESSION['error'] . '</p>');
        unset($_SESSION['error']);
    }
    return;
}

function validateProfile()
{
    if (strlen($_POST['first_name']) == 0 && strlen($_POST['last_name']) == 0 && strlen($_POST['email']) == 0
     && strlen($_POST['headline']) == 0 && strlen($_POST['summary']) == 0)
     {
        return "All fields are required";   
     }

     if (strpos($_POST['email'],'@') < 1)
     {
        return "Email address must contain @";
     }
     return true;
}

function validatePos() 
{
    for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['year'.$i]) ) continue;
      if ( ! isset($_POST['desc'.$i]) ) continue;
  
      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];
  
      if ( strlen($year) == 0 || strlen($desc) == 0 ) {
        return "All fields are required";
      }
  
      if ( ! is_numeric($year) ) {
        return "Position year must be numeric";
      }
    }
    return true;
}

function validateEdu() 
{
    for($i=1; $i<=9; $i++) 
    {
      if ( ! isset($_POST['edu_year'.$i]) ) continue;
      if ( ! isset($_POST['edu_school'.$i]) ) continue;
  
      $edu_year = $_POST['edu_year'.$i];
      $edu_school = $_POST['edu_school'.$i];
  
      if ( strlen($edu_year) == 0 || strlen($edu_school) == 0 ) 
      {
        return "All fields are required";
      }
  
      if ( ! is_numeric($edu_year) ) 
      {
        return "Education year must be numeric";
      }
    }
    return true;
}

function insertPos($conn, $profile_id)
{           
  $rank = 1;
  for($i=1; $i<=9; $i++) 
  {
    if ( ! isset($_POST['year'.$i]) ) continue;
    if ( ! isset($_POST['desc'.$i]) ) continue;

    $year = $_POST['year'.$i];
    $desc = $_POST['desc'.$i];
    $stmt = $conn->prepare(
                            'INSERT INTO Position
                            (profile_id, rank, year, description)
                            VALUES ( :pid, :rank, :year, :desc)'
                          );

    $stmt->bindParam(':pid',$profile_id);
    $stmt->bindParam(':rank',$rank);
    $stmt->bindParam(':year',$year);
    $stmt->bindParam(':desc',$desc);
               
    $stmt->execute();

    $rank++;
  }
}

function insertEdu($conn, $profile_id)
{       
  $rank = 1;
  for($i=1; $i<=9; $i++) 
  {
    if ( ! isset($_POST['edu_year'.$i]) ) continue;
    if ( ! isset($_POST['edu_school'.$i]) ) continue;

    $edu_school = $_POST['edu_school'.$i];
    $edu_year = $_POST['edu_year'.$i];

    //get institution id
    $institution_id = false;
    
    $stmt = $conn->prepare('SELECT institution_id FROM institution WHERE name = :prefix');
    $stmt->execute(array( ':prefix' => $edu_school));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row !== false) $institution_id = $row['institution_id'];

    if ($institution_id === false)
    {
      $stmt = $conn->prepare('INSERT INTO institution (name) VALUES (:name)');
      $stmt->execute(array( ':name' => $edu_school));
      $institution_id = $conn->lastInsertId();
    }

    //filling the education
    
    $stmt = $conn->prepare('INSERT INTO education 
                                   (profile_id, institution_id, rank, year) 
                            VALUES ( :pid, :iid, :rank, :year)');

    $stmt->bindParam(':pid',$profile_id);
    $stmt->bindParam(':rank',$rank);
    $stmt->bindParam(':year',$edu_year);
    $stmt->bindParam(':iid',$institution_id);
               
    $stmt->execute();

    $rank++;
  }
}


function loadPos($conn, $profile_id)
{
  $stmt = $conn->prepare('SELECT * FROM Position where profile_id = :prof order by rank');
  $stmt->execute(array(':prof' => $profile_id));
  $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $positions;
}

function loadEdu($conn, $profile_id)
{
  $stmt = $conn->prepare('SELECT year, name FROM education join institution on
                         education.institution_id = institution.institution_id
                         where profile_id = :prof order by rank');
  $stmt->execute(array(':prof' => $profile_id));
  $educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
  return $educations;
}
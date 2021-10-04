<?php
  include_once('db.php');
  if(isset($_POST['fname']))
  {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $city = $_POST['city'];
    $id = $_POST['id'];

    $sql = "update user set fname='$fname', lname='$lname', city='$city' where id=$id";

    if(mysqli_query($conn,$sql))
    {
      echo header('Location:index.php');

    }
    else
    {
      echo mysqli_error($conn);
    }
  }

?>

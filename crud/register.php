<?php
  include_once('db.php')
  if(isset($_POST['fname']))
  {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $city = $_POST['city'];

    $conn = mysqli_connect("localhost","root","","cruddb");

    if(!$conn)
    {
      echo "Connection failed!";
      exit;
    }

    $sql = "insert into user(fname,lname,city,status) value('$fname','$lname','$city',1)";

    if(mysqli_query($conn,$sql))
    {
      header('Location:index.php');
    }
    else
    {
      echo mysqli_error($conn);
    }
  }

?>

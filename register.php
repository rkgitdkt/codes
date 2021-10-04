
<?php
  /*connect database*/
  $conn = mysqli_connect('servername','username','password','databasename');
  if(!$conn)
  {
    die('Connection failed!'.mysqli_error($conn));
  }
  /*now get submitted data*/
  $sno = $_POST['sno'];
  $name = $_POST['name'];
  $uname = $_POST['uname'];
  $pwd = $_POST['pwd'];

  $sql = "INSERT INTO tablename(sno, name, uname, pwd) VALUES('$sno', '$name','$uname',
   '$pwd')";

  if(mysqli_query($conn,$sql))
    echo "Registerd Successfully";
  else
    echo mysqli_error($conn);
?>

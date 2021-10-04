<?php
include_once('db.php');
if(isset($_GET['id']))
{
	$id = $_GET['id'];
	$sql = "select * from user where id=$id and status=1";
	$result = mysqli_query($conn,$sql);
	$row = mysqli_fetch_assoc($result);
}
?>
<html>
  <head>
     <title>Edit User</title>
  </head>
  <body>
    <div id="header">
      <h1>CRUD</h1>
    </div>
    <div id="nav">
      <a href="index.php">Home</a> | <a href="register.php">Register</a>
    </div>
    <h3>Update user</h3>
    <div id="data">
      <form action="update.php" method="post">
        <label>First name</label>
        <input type="text" name="fname" value="<?php echo $row['fname']; ?>"><br>
        <label>Last name</label>
        <input type="text" name="lname" value="<?php echo $row['lname']; ?>"><br>
        <label>City</label>
        <input type="text" name="city" value="<?php echo $row['city']; ?>"><br>
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <input type="submit" value="Update">
      </form>
    </div>
  </body>
</html>

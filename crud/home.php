<html>
  <head>
    <title>Home</title>
  </head>
  <body>
    <div id="header">
      <h1>CRUD</h1>
    </div>
    <div id="nav">
      <a href="crud.php">Home</a> | <a href="register.php">Register</a>
    </div>
    <h3>User details</h3>
    
    <?php
    include_omce('db.php')
    $sql = "select * from user where status=1";

    $result = mysqli_query($conn,$sql);

    echo "<table border='1'>";
    while($row = mysqli_fetch_assoc($result))
    {
      echo "<tr>";
      echo "<td>".$row['fname']."</td>";
      echo "<td>".$row['lname']."</td>";
      echo "<td>".$row['city']."</td>";
      echo "<td><a href='edit.php?id=".$row['id']."'>Edit</a></td>";
      echo "<td><a href='delete.php?id=".$row['id']."'>Delete</a></td>";
      echo "</tr>";
    }
    echo "</table>";
    ?>
  </body>
</html>

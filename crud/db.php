$conn = mysqli_connect("localhost","root","","cruddb");
if(!$conn)
{
  echo "Connection failed!";
  exit;
}

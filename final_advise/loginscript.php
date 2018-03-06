<?php
  include "connection.php";

  session_start();

  if(isset($_POST['uname']))
  {
      $get_username = $_POST['uname'];
      $get_password = $_POST['psw'];

      $query = "SELECT facultyid, firstname, lastname FROM sis.faculty WHERE firstname = '$get_username' AND lastname = '$get_password'"; 
      $result = pg_query($query) or die('Query failed:'.pg_last_error());

      $array = pg_fetch_array($result, 0, PGSQL_NUM);
      $rows = pg_fetch_row($result);

      if($rows == 0)
      {
        die(header("location:myapp.php?loginFailed=true&reason=incorrect_details"));
      }else
      {
          $_SESSION['uname'] = $get_username;
          $_SESSION['fac_id'] = $array[0]; //$array[0] kay para ma select ang facultyid kay mao man ang first na mention sa $query.

          header("Location: enter_sy_sem.php", true, 301); // Redirect user to index.php
          exit();
      }
  }
?>
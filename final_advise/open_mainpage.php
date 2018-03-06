<?php
include "connection.php";

session_start();

  if(isset($_POST['selectterm']))
  {
     $query = "SELECT syearid, schooyear, semester FROM sis.schoolyear WHERE syearid = '$_POST[selectterm]'";
     $result = pg_query($query) or die('Query failed:'.pg_last_error());
     $array = pg_fetch_array($result, 0, PGSQL_NUM);

  	 //taking the integer part of the schoolyear
  	 $integerSY = array_map('intval', explode('-', $array[1]));

  	 //user input used for creating COR
  	 $_SESSION['sy_sem_input'] = $_POST['selectterm'];

     /*NOTE: isa ra ka variable akong gigamit para sa prev_sy ug prev_sem. Para na dili na maglibog2 unsa ang ipangalan sa mga previous if ang sem is summer, 1 or 2. Pwede raman na kay either sa if-else ra man ang modagan sa code so dili siya magka collision ang variables.*/

/*######################################################################################################################*/
  	 if($array[2] == "Summer")
  	 {
  	 	$_SESSION['summer_status'] = "Regular";
  	 	$_SESSION['prev_sy'] = $array[1];
  	 	$_SESSION['prev_sem'] = "2";
  	 }
  	 else if($array[2] == "1")
  	 {
  	 	//converting string to int
   	 	$integerSY = array_map('intval', explode('-', $array[1]));

  		//perform minus operation of SY
  		$from_year = $integerSY[0] - 1;
  		$to_year = $integerSY[1] - 1;

  		//converting back int to string
  		$string_prev_from_sy = (string)$from_year;
  		$string_prev_to_sy = (string)$to_year;

  		//sessioning final previous sy and sem
  		$_SESSION['prev_sy'] = $string_prev_from_sy . '-' . $string_prev_to_sy;
  		$_SESSION['prev_sem'] = "2";	
  	 }
  	 else if($array[2] == "2")
  	 {
  	 	//converting sem to int
  	 	$integerSEM = (int)$array[2];

  	 	//performing minus operation of sem
  	 	$from_sem = $integerSEM - 1;

  	 	//converting sem back to string from int
  	 	$string_prev_sem = (string)$from_sem;

  	 	//sessioning final previous sy and sem. Using the same sy but different sem.
  	 	$_SESSION['prev_sem'] = $string_prev_sem; 
  	 	$_SESSION['prev_sy'] = $array[1];
  	 }
/*######################################################################################################################*/

     // query for checking if previous syear exists.
     $querychk = "SELECT syearid FROM sis.schoolyear WHERE schooyear = '".$_SESSION['prev_sy']."' AND 
                semester = '".$_SESSION['prev_sem']."'";
     $resultchk = pg_query($querychk) or die('Query failed:'.pg_last_error());
     $rowschk = pg_num_rows($resultchk);
     // query for checking if previous syear exists.

     if($rowschk == 0)
     {
        echo '<script type="text/javascript">';
        echo 'alert("Unable to proceed. Please make sure previous school year exists.");';
        echo 'window.stop();';
        echo 'window.location.href = "enter_sy_sem.php";';
        echo '</script>';
     }
     else
     {
       //session the sy and sem for display in mainpage
       $_SESSION['cur_sy'] = $array[1];
       $_SESSION['cur_sem'] = $array[2];

       //diri nako gi assignan ug values ang arraychk to avoid syntax error in saving COR in case prev sem does not exist
       $arraychk = pg_fetch_array($resultchk, 0, PGSQL_NUM);
       //sessioning the syearid to be used in displaying the prev COR.
       $_SESSION['syearidbefore'] = $arraychk[0];

    	 header("Location: mainpage.php", true, 301); // Redirect user
       exit();
     }
  }
?>
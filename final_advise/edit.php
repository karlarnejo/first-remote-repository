<?php
include "connection.php";

session_start();

if(isset($_POST['studid']))
{
	//query to check if idno exist
	$querychk = "SELECT s.idno
               from sis.student s
               where s.idno = '$_POST[studid]'";

    $resultchk = pg_query($querychk) or die('Query failed:'.pg_last_error());

    $rowschk = pg_num_rows($resultchk);
    //query to check if idno exist

    if($rowschk == 0)
    {
	    echo '<script type="text/javascript">';
	    echo 'alert("ID number does not exist!");';
	    echo 'window.stop();';
	    echo 'window.location.href = "mainpage.php";';
	    echo '</script>';
    }
    else
    {
    	//query for selecting prev regid
		$queryreg = "SELECT regno, clearancestatus FROM sis.registration WHERE idno = '$_POST[studid]' AND syearid = '".$_SESSION['syearidbefore']."'";

		$resultreg = pg_query($queryreg) or die('Query failed:'.pg_last_error());

		$rowsreg = pg_num_rows($resultreg);
		//query for selecting prev regid

		if($rowsreg == 0)
		{
			echo '<script type="text/javascript">';
		    echo 'alert("Error. No previous record to edit!");';
		    echo 'window.stop();';
		    echo 'window.location.href = "mainpage.php";';
		    echo '</script>';
		}
		else
		{
			//diri nako gi assign para di mag syntax error if wala siya ga exist.
			$arrayreg = pg_fetch_array($resultreg, 0, PGSQL_NUM);

			//Update query
			$queryupd = "UPDATE sis.registration
						 SET yearlevel = '$_POST[yrlvl]'
						 WHERE idno = '$_POST[studid]' AND regno = '$arrayreg[0]'";

		    $resultupd = pg_query($queryupd) or die('Query failed:'.pg_last_error());
			//Update query

			echo '<script type="text/javascript">';
		    echo 'alert("Successful editing!");';
		    echo 'window.stop();';
		    echo 'window.location.href = "mainpage.php";';
		    echo '</script>';
		}
	}
}
?>
<?php
include "connection.php";

session_start();

if(isset($_POST['studid']))
{
/*---------------------------------------------------------------------------------------------------------------------*/
	//query to check if idno exist
	$querychk = "SELECT s.idno
               from sis.student s
               where s.idno = '$_POST[studid]'";

    $resultchk = pg_query($querychk) or die('Query failed:'.pg_last_error());

    $rowschk = pg_num_rows($resultchk);
    //query to check if idno exist
/*---------------------------------------------------------------------------------------------------------------------*/
    //check if id exist. Kaylangan ni siya ky di ko kabalo mo enable/disable sa New na button. Mobalik siyag disabled after refresh. So to prevent entering blank IDNO, icheck nalng daan if ga exist o wala.
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
/*---------------------------------------------------------------------------------------------------------------------*/
		//query for selecting prev regid
		$queryreg = "SELECT regno, clearancestatus, scholasticstatus, courseid FROM sis.registration 
					WHERE idno = '$_POST[studid]' AND syearid = '".$_SESSION['syearidbefore']."'";

		$resultreg = pg_query($queryreg) or die('Query failed:'.pg_last_error());

		$rowsreg = pg_num_rows($resultreg);
		//query for selecting prev regid
/*---------------------------------------------------------------------------------------------------------------------*/
		//if-else for computing scholastic status
		//LOGIC -> If wala siyay previous record, meaning transferee siya or new student.
		if($rowsreg == 0)
		{
/*---------------------------------------------------------------------------------------------------------------------*/
			//query to check if naay record sa admission -> (error checking if wala siya sa admmission)
			$querycidchk = "SELECT courseid FROM sis.admission WHERE idno = '$_POST[studid]' AND syearid = '".$_SESSION['sy_sem_input']."'";

			$resultcidchk = pg_query($querycidchk) or die('Query failed:'.pg_last_error());

			$rowcidchk = pg_fetch_row($resultcidchk);
			//query to check if naay record sa admission -> (error checking if wala siya sa admmission)
/*---------------------------------------------------------------------------------------------------------------------*/

			//if walay record sa prev sem AND no record in admission, pop error. Else, proceed.
			if($rowcidchk == 0)
			{
				echo '<script type="text/javascript">';
		        echo 'alert("Student not yet admitted!");';
		        echo 'window.stop();';
		        echo 'window.location.href = "mainpage.php";';
		        echo '</script>';
			}
			else
			{
				/*<------------Diri magsugod para sa TRANSFEREE and NEW student------------->*/

				//query for selecting courseid in admission
				$querycid = "SELECT courseid FROM sis.admission WHERE idno = '$_POST[studid]' AND syearid = '".$_SESSION['sy_sem_input']."'";

				$resultcid = pg_query($querycid) or die('Query failed:'.pg_last_error());

				$arraycid = pg_fetch_array($resultcid, 0, PGSQL_NUM);
				//end of query for selecting courseid

				//query to insert
				$query = "INSERT INTO sis.registration (regdate, totalunits, feeamountdue, feeamountpaid, scholasticstatus, liabilityamountdue, liabilityamountpaid, clearancestatus, registered, idno, syearid, courseid, adviser, yearlevel) VALUES ('now', '0', '0', '0', 'Regular', '0', '0', 'false', 'false', '$_POST[studid]', '".$_SESSION['sy_sem_input']."', '$arraycid[0]', '".$_SESSION['fac_id']."', '$_POST[yrlvl]')";  
						  
				$result = pg_query($query) or die('Query failed:'.pg_last_error());
				//query to insert

				echo '<script type="text/javascript">';
				echo 'alert("COR successfully created!");';
				echo 'window.location.href = "mainpage.php";';
				echo '</script>';
			}
		}
		else
		{
			/*<------------Diri magsugod para sa REGULAR and SHIFTEE student------------->*/

/*##########START OF QUERIES THAT ARE JUST USED FOR DETERMINING STUDENT TYPE AND RECORDS for OLD students############*/

/*---------------------------------------------------------------------------------------------------------------------*/
			//declaring scholasticstatus. Diri nato gi declare for formality (pwede ra sad dili diri i declare) ky diri man mahulog ang students nga regular or shiftee(kana silang duha naay previous COR)
			$final_status;
/*---------------------------------------------------------------------------------------------------------------------*/
			//assigning values to prevreg. take note diri nako gi assign ky diri man ma fall ang mga naay prev record(regular/shiftee).
			$arrayreg = pg_fetch_array($resultreg, 0, PGSQL_NUM);
/*---------------------------------------------------------------------------------------------------------------------*/
			//query to check if naay record sa admission -> for shiftee
			$querycidshift = "SELECT courseid FROM sis.admission WHERE idno = '$_POST[studid]' AND syearid = '".$_SESSION['sy_sem_input']."'";

			$resultcidshift = pg_query($querycidshift) or die('Query failed:'.pg_last_error());

			$rowcidshift = pg_fetch_row($resultcidshift);
			//query to check if naay record sa admission -> for shiftee
/*---------------------------------------------------------------------------------------------------------------------*/
			//declaring the totalfailedingrades and totalpassedincomp
			$totalfailedingrades = 0;
			$totalpassedincomp = 0;
/*---------------------------------------------------------------------------------------------------------------------*/
			//query for adding all 5.00/ $totalfailedingrades
			$queryfail = "SELECT sum(s.units) AS totalfailedingrades
						  FROM sis.subject s, sis.offering offe, sis.enrolledsubject es, sis.registration reg
						  WHERE s.subjectid = offe.subjectid AND offe.offeringno = es.offeringno AND reg.regno = es.regno AND es.regno = '$arrayreg[0]' AND (grade = 'INC' OR grade = 'DRP' OR grade is null OR grade = '5.00')";

			$resultfail = pg_query($queryfail) or die('Query failed:'.pg_last_error());

			$arrayfail = pg_fetch_array($resultfail, 0, PGSQL_NUM);

			//assigning the totalfailedingrades to be used in computing later
			$totalfailedingrades = $arrayfail[0];

			//query for adding all 5.00/ $totalfailedingrades
/*---------------------------------------------------------------------------------------------------------------------*/
			//query for adding all passed in completiongrade(for INC's)
			$querytotalpassed = "SELECT sum(s.units) AS totalpassedincomp
								FROM sis.subject s, sis.offering offe, sis.enrolledsubject es, sis.registration reg
								WHERE s.subjectid = offe.subjectid AND offe.offeringno = es.offeringno AND reg.regno = es.regno AND es.regno = '$arrayreg[0]' AND (completiongrade != 'INC' AND completiongrade != 'DRP' AND completiongrade is not null AND completiongrade != '5.00')";

			$resulttotalpassed = pg_query($querytotalpassed) or die('Query failed:'.pg_last_error());

			$arraytotalpassed = pg_fetch_array($resulttotalpassed, 0, PGSQL_NUM);

			//assigning the totalpassedincomp to be used in computing later
			$totalpassedincomp = $arraytotalpassed[0];

			//query for adding all passed in completiongrade(for INC's)
/*---------------------------------------------------------------------------------------------------------------------*/
			//query for adding all units of subject taken
			$querytotalunits = "SELECT sum(s.units) 
							FROM sis.subject s, sis.offering offe, sis.enrolledsubject es, sis.registration reg
							WHERE s.subjectid = offe.subjectid AND offe.offeringno = es.offeringno AND reg.regno = es.regno AND es.regno = '$arrayreg[0]'";

			$resulttotalunits = pg_query($querytotalunits) or die('Query failed:'.pg_last_error());

			$arraytotalunits = pg_fetch_array($resulttotalunits, 0, PGSQL_NUM);
			//query for adding all units of subject taken
/*---------------------------------------------------------------------------------------------------------------------*/
			//query para mo check sa collegeid GIKAN sa course na entity gamit ang prev courseid from registration. Mao ning checking if ang adviser kay MARO/GIBAYARAN. Ma fall ni siya sa registration na entity. Student nga gibayaran ang adviser nga mopadayon gyapon sa iyang course without shifting to other college maskin Dismissed Fom College na iyang status -> para ni sa checking if Dismissed From College ang status ky dili pwede maka enroll ang student sa same college if ang status niya kay Dismissed From College.
			$querycollegedismissed = "SELECT col.collegeid
													FROM sis.college col, sis.course c
													WHERE c.collegeid = col.collegeid AND c.courseid = '$arrayreg[3]'";

			$resultcollegedismissed = pg_query($querycollegedismissed) 
													or die('Query failed:'.pg_last_error());

			$arraycollegedismissed = pg_fetch_array($resultcollegedismissed, 0, PGSQL_NUM);
			//query para mo check sa collegeid GIKAN sa course na entity gamit ang prev courseid from registration. Mao ning checking if ang adviser kay MARO/GIBAYARAN. Ma fall ni siya sa registration na entity. Student nga gibayaran ang adviser nga mopadayon gyapon sa iyang course without shifting to other college maskin Dismissed Fom College na iyang status -> para ni sa checking if Dismissed From College ang status ky dili pwede maka enroll ang student sa same college if ang status niya kay Dismissed From College.
/*---------------------------------------------------------------------------------------------------------------------*/

/*#############END OF QUERIES THAT ARE JUST USED FOR DETERMINING STUDENT TYPE AND RECORDS for OLD students#############*/




/*################################IF CONDITIONS FOR DETERMINING SCHOLASTIC STATUS #####################################*/

			//declaring $final_failed to calculate all failed subjects (inlcuding complied INC's with passing grade)
			$final_failed = $totalfailedingrades - $totalpassedincomp;
			//declaring $ans to be used in scholasticstatus. Rounding off to 2 decimal points.
			$ans = round(($final_failed/$arraytotalunits[0])*100, 0);

			//assigning the scholasticstatus of each student
			if($ans >= 0.00 && $ans <= 24.00)
			{
				$final_status = "Regular";
			}
			else if($ans >= 25.00 && $ans <= 49.00)
			{
				$final_status = "Warning";
			}
			else if($ans >= 50.00 && $ans <= 75.00)
			{
				$final_status = "Probation";
			}
			else if($ans >= 76.00 && $ans <= 99.00)
			{
				$final_status = "Dismissal From College";
			}
			else
			{
				$final_status = "Dismissal From MSUS";
			}

/*################################IF CONDITIONS FOR DETERMINING SCHOLASTIC STATUS #####################################*/




/*---------------------------------------------------------------------------------------------------------------------*/
			
			/*<------------------ACTUAL CONDITIONS START HERE FOR OLD STUDENTS----------------->*/

			//check if student is cleared or not.
			if($arrayreg[1] == "f")
			{
				echo '<script type="text/javascript">';
				echo 'alert("Student not yet cleared!");';
				echo 'window.location.href = "mainpage.php";';
				echo '</script>';
			}
			else
			{
				//NEWEST REVISION 11/20/2016 START HERE. Pangitaa lang ang query na na involve ani i highlight para makita. Naa pa pod ang continuation ani didto sa shiftee na side pangitaa lang ang NEWEST REVISION 11/20/2016 didto.
				//check if dismissed siya sa msu system.
				if($final_status == "Dismissal From MSUS")
				{
					echo '<script type="text/javascript">';
					echo 'alert("Cant create COR. Student is dismissed in MSUS!");';
					echo 'window.location.href = "mainpage.php";';
					echo '</script>';
				}
				else
				{
					//if wala siyay record sa admission and naa siyay prev cor, meaning regular. Else, shiftee.
					if($rowcidshift == 0)
					{
						//diri ko gacheck if dismissed na siya and iyang course kay mo equal sa same course sa previous niya if mo enroll kay mo error. KANI NA CASE KAY IF NAAY MARO NA ADVISER GI BAYARAN. Mocheck siya sa registration na entity if ang i create nya na COR kay same ang course id sa prev ug sa karon.
						if($final_status == "Dismissal From College" AND 
					    $arraycollegedismissed[0] == $arraycollegedismissed[0])
						{
							echo '<script type="text/javascript">';
							echo 'alert("Cant create COR. Student is dismissed in College!");';
							echo 'window.location.href = "mainpage.php";';
							echo '</script>';
						}
						//NEWEST REVISION 11/20/2016 ENDS HERE. Pangitaa lang ang query na na involve ani i highlight para makita. Naa pa pod ang continuation ani didto sa shiftee na side pangitaa lang ang NEWEST REVISION 11/20/2016 didto.
						else
						{
							/*<------------Diri magsugod para sa REGULAR student------------->*/

							//query for selecting courseid
							$querycidreg = "SELECT courseid FROM sis.course WHERE coursename = '$_POST[coursename]'";

							$resultcidreg = pg_query($querycidreg) or die('Query failed:'.pg_last_error());

							$arraycidreg = pg_fetch_array($resultcidreg, 0, PGSQL_NUM);
							//end of query for selecting courseid

							//query to insert
							$query = "INSERT INTO sis.registration (regdate, totalunits, feeamountdue, feeamountpaid, scholasticstatus, liabilityamountdue, liabilityamountpaid, clearancestatus, registered, idno, syearid, courseid, adviser, yearlevel) VALUES ('now', '0', '0', '0', '$final_status', '0', '0', 'false', 'false', '$_POST[studid]', '".$_SESSION['sy_sem_input']."', '$arraycidreg[0]', '".$_SESSION['fac_id']."', '$_POST[yrlvl]')";  
								  
							$result = pg_query($query) or die('Query failed:'.pg_last_error());
							//query to insert

							echo '<script type="text/javascript">';
							echo 'alert("COR successfully created!");';
							echo 'window.location.href = "mainpage.php";';
							echo '</script>';
						}
					}
					else
					{
						/*<------------Diri magsugod para sa SHIFTEE student------------->*/
						
						//query for selecting courseid in admission
						$querycid = "SELECT courseid FROM sis.admission WHERE idno = '$_POST[studid]' AND syearid = '".$_SESSION['sy_sem_input']."'";

						$resultcid = pg_query($querycid) or die('Query failed:'.pg_last_error());

						$arraycid = pg_fetch_array($resultcid, 0, PGSQL_NUM);
						//end of query for selecting courseid

						//NEWEST REVISION 11/20/2016 STARTS HERE. Mao ni ang continuation ako giingon

						//query mo check sa courseid nga gi shiftan if ang courseid kay na belong ba sa same na college sa before.
						$querycollegedismissedadmission = "SELECT col.collegeid
													FROM sis.college col, sis.course c
													WHERE c.collegeid = col.collegeid AND c.courseid = '$arraycid[0]'";

						$resultcollegedismissedadmission = pg_query($querycollegedismissedadmission) 
																or die('Query failed:'.pg_last_error());

						$arraycollegedismissedadmission = pg_fetch_array($resultcollegedismissedadmission, 0, PGSQL_NUM);
						//query mo check sa courseid nga gi shiftan if ang courseid kay na belong ba sa same na college sa before.

						//if ang status niya kay DFC AMD ang collegeid gamit ang prev courseid niya kay mo equal sa college id sa iyang gi shiftan(different course pero same college like BSIT and BSIS) gamit ang courseid sa admission na table kay mo error siya.
						if($final_status == "Dismissal From College" AND 
					    $arraycollegedismissed[0] == $arraycollegedismissedadmission[0])
						{
							echo '<script type="text/javascript">';
							echo 'alert("Cant create COR. Student is dismissed in College!");';
							echo 'window.location.href = "mainpage.php";';
							echo '</script>';
						}
						//if gikan kag dismissal from college and ni shift ka lain course from lain na college, automatic probation imo status.
						else if($final_status == "Dismissal From College" AND 
					    $arraycollegedismissed[0] != $arraycollegedismissedadmission[0])
						{
							//query to insert
							$query = "INSERT INTO sis.registration (regdate, totalunits, feeamountdue, feeamountpaid, scholasticstatus, liabilityamountdue, liabilityamountpaid, clearancestatus, registered, idno, syearid, courseid, adviser, yearlevel) VALUES ('now', '0', '0', '0', 'Probation', '0', '0', 'false', 'false', '$_POST[studid]', '".$_SESSION['sy_sem_input']."', '$arraycid[0]', '".$_SESSION['fac_id']."', '$_POST[yrlvl]')";  
								  
							$result = pg_query($query) or die('Query failed:'.pg_last_error());
							//query to insert

							echo '<script type="text/javascript">';
							echo 'alert("COR successfully created!");';
							echo 'window.location.href = "mainpage.php";';
							echo '</script>';
						}
						//NEWEST REVISION 11/20/2016 ENDS HERE. Mao ni ang continuation ako giingon
						else
						{
							//query to insert
							$query = "INSERT INTO sis.registration (regdate, totalunits, feeamountdue, feeamountpaid, scholasticstatus, liabilityamountdue, liabilityamountpaid, clearancestatus, registered, idno, syearid, courseid, adviser, yearlevel) VALUES ('now', '0', '0', '0', '$final_status', '0', '0', 'false', 'false', '$_POST[studid]', '".$_SESSION['sy_sem_input']."', '$arraycid[0]', '".$_SESSION['fac_id']."', '$_POST[yrlvl]')";  
								  
							$result = pg_query($query) or die('Query failed:'.pg_last_error());
							//query to insert

							echo '<script type="text/javascript">';
							echo 'alert("COR successfully created!");';
							echo 'window.location.href = "mainpage.php";';
							echo '</script>';
							//i love you byyy sorry sa tanan hapit na ta mag 1 year :D
						}
					}
				}
			}
		}
	}
}
?>
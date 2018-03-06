<!DOCTYPE html>
<html lang="en">
<head>
	   <meta charset = "utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1">
	   <title>Mindanao State University IIT</title>
  	 <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  	 <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  	 <script src = "bootstrap/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
<?php
include "connection.php";

session_start();
?>

<table class="table table-hover">
                <thead> 
                    <tr> 
                        <th>Subject Name</th>
                        <th>Descriptive Title</th>
                        <th>Year Level</th>
                        <th>Semester</th>
                        <th>Grades</th>
                    </tr> 
                </thead>
                <body>
                    <?php 
                            $query = "SELECT sub.subjectname, sub.descriptivetitle, cs.yearlevel, cs.semester
                                      FROM sis.course c, sis.subject sub, sis.course_subject cs, sis.registration reg, sis.admission ad, sis.student s
                                      WHERE reg.courseid = c.courseid
                                      AND c.courseid = cs.courseid
                                      AND sub.subjectid = cs.subjectid
                                      AND s.idno = reg.idno
                                      AND ad.courseid = c.courseid
                                      AND (reg.regno = '".$_SESSION['tblregno']."' OR ad.admissionno = '".$_SESSION['tbladmissionno']."')";

                            /*start grade table result*/
                            $result = pg_query($query) or die('Query failed:'.pg_last_error());
                            /*end grade table result*/                     

                            $query2 = "SELECT es.grade
									   FROM sis.enrolledsubject es
									   WHERE regno = 56";

                            $result2 = pg_query($query2) or die('Query failed:'.pg_last_error());

                            if (!$result)
                            { 
                                echo "Problem with query " . $query . "<br/>"; 
                                echo pg_last_error(); 
                                exit(); 
                            } 

                            while($myrow = pg_fetch_array($result) && $myrow2 = pg_fetch_array($result2))
                            { 
                                printf ("<tr align=\"left\">
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                        </tr>", $myrow[0], htmlspecialchars($myrow[1]), htmlspecialchars($myrow[2]), htmlspecialchars($myrow[3]), $myrow2[0]);
                            } 
                    ?>
                </body> 
            </table>

</div>


</body>
</html>
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
<?php
include "connection.php";

session_start();
?>

<?php
if(isset($_POST['studid']))
{
    //start of query for retrieving student details
    $query1 = "SELECT s.idno, s.firstname, s.lastname, s.mi
               from sis.student s
               where s.idno = '$_POST[studid]'";

    $result1 = pg_query($query1) or die('Query failed:'.pg_last_error());

    
    $rows1 = pg_num_rows($result1);
    //end of query for retrieving student details

    //check if id exist
    if($rows1 == 0)
    {
        echo '<script type="text/javascript">';
        echo 'alert("ID number does not exist!");';
        echo 'window.stop();';
        echo 'window.location.href = "mainpage.php";';
        echo '</script>';
    }
    else
    {
        //assigning values to array
        $array1 = pg_fetch_array($result1, 0, PGSQL_NUM);

        $test = $_SESSION['syearidbefore'];

        //start of query for retrieving prev COR
        $query = "SELECT reg.yearlevel, reg.scholasticstatus, c.courseid, c.coursename
                  FROM sis.registration reg, sis.course c, sis.schoolyear sy
                  WHERE sy.syearid = reg.syearid AND c.courseid = reg.courseid AND 
                  reg.idno = '$_POST[studid]' AND
                  sy.syearid = $test";

        $result = pg_query($query) or die('Query failed:'.pg_last_error());
        //start of query for retrieving prev COR

        //check if naay gi return ang query for retrieving prev COR
        $rows = pg_num_rows($result);

        //gagamit tag ingani kay if mag assign ta diretso sa values na gi return, assuming nga wala ta kabalo if naa ba jud i return o wala. Kay if walay i return, mo error siya. Mao na mag if-else ta.
        //if naa, assign array sa katong gi select nako for retrieving prev COR. If wala, assign to blank.
        if($rows == 1)
        {
            $array = pg_fetch_array($result, 0, PGSQL_NUM);
        }
        else
        {
            $array[0] = "";
            $array[1] = "";
            $array[2] = "";
            $array[3] = "";
            $array[4] = "";
        }



/*NEW UPDATE FOR PHP CODES AS OF 11/19/2016 STARTS HERE. Ayaw sa ni pansina if ga create pa ka sa student details. Para ra ni sa displaying of Prospectus and Grades. Wala ni kinalaman sa pag save or edit sa COR. I disregard lang sa ni kay ang use ani kay i display ang prospectus and grades table kay para ma determine sa adviser if i promote ba siya to next year level. DILI ni necessary sa creation or edit of COR. i IGNORE sa ni and focus sa main points.*/



        //declare sa status sa student whether shiftee, new, transferee, regular. Mausab ni when certain conditions are met.
        $studentstatus;
        
        //himo $querytable kay para kani ang i run if (transferee/new student), if shiftee, if regular students
        $querytableprospectus;
        
        //himo $querynumyearlevel para ma count nako pila ka tables akong himoon based sa number of years sa course.
        $querytablegrades;
        

/*---------------------------------------------------------------------------------------------------------------------*/
        //query for selecting prev regid
        $queryreg = "SELECT regno, clearancestatus FROM sis.registration WHERE idno = '$_POST[studid]' AND syearid = '".$_SESSION['syearidbefore']."'";

        $resultreg = pg_query($queryreg) or die('Query failed:'.pg_last_error());

        $rowsreg = pg_num_rows($resultreg);
        //query for selecting prev regid
/*---------------------------------------------------------------------------------------------------------------------*/
        //LOGIC -> If wala siyay previous record, meaning transferee siya or new student. else, old or shiftee
        if($rowsreg == 0)
        {
            //diri ma fall ang mga transferee and new students

/*---------------------------------------------------------------------------------------------------------------------*/
            //query to check if naay record sa admission -> if ga exist ra ang student sa student table maski wala sa admission
            $querynoexist = "SELECT courseid FROM sis.admission 
                            WHERE idno = '$_POST[studid]' AND syearid = '".$_SESSION['sy_sem_input']."'";

            $resultnoexist = pg_query($querynoexist) or die('Query failed:'.pg_last_error());

            $rownoexist = pg_fetch_row($resultnoexist);
            //query to check if naay record sa admission -> if ga exist ra ang student sa student table maski wala sa admission
/*---------------------------------------------------------------------------------------------------------------------*/

            //icheck niya ang records sa admission kay what if ga exist ang student sa student table pero walay records sa registration or sa admission?
            if($rownoexist == 0)
            {
                echo '<script type="text/javascript">';
                echo 'alert("Student exists but not yet enrolled in any course!");';
                echo 'window.stop();';
                echo 'window.location.href = "mainpage.php";';
                echo '</script>';
            }
            else
            {
                //para nis new and transferee students.

                //query for selecting courseid in admission
                $querycid = "SELECT courseid, admissionno, admissiontype FROM sis.admission 
                            WHERE idno = '$_POST[studid]' AND syearid = '".$_SESSION['sy_sem_input']."'";

                $resultcid = pg_query($querycid) or die('Query failed:'.pg_last_error());

                $arraycid = pg_fetch_array($resultcid, 0, PGSQL_NUM);
                //end of query for selecting courseid

                $querytableprospectus = "SELECT sub.subjectname, sub.descriptivetitle, cs.yearlevel, cs.semester,
                                c.coursename, c.major, c.courseid
                                FROM sis.course c, sis.subject sub, sis.course_subject cs, sis.admission ad, sis.student s
                                WHERE s.idno = ad.idno
                                AND ad.courseid = c.courseid
                                AND c.courseid = cs.courseid
                                AND sub.subjectid = cs.subjectid
                                AND ad.admissionno = '$arraycid[1]'
                                ORDER BY cs.yearlevel, cs.semester";

                $querytablegrades = "SELECT sub.subjectname, sub.descriptivetitle, es.grade, es.completiongrade
                                    FROM sis.subject sub, sis.enrolledsubject es, sis.registration reg, sis.offering offe
                                    WHERE reg.regno = es.regno AND offe.offeringno = es.offeringno 
                                    AND sub.subjectid = offe.subjectid AND reg.idno = '$_POST[studid]' 
                                    ORDER BY reg.regno";

                //diri nato gibutang ang admissiontype sa student. kabalo man ta nga transferee or freshman siya kay diri man ma fall ang new and transferee student.
                $studentstatus = $arraycid[2];
            }

        }
        else
        {
            //diri nako gi assign ang pg_fetch_array sa $resultreg kay diri man ma fall ang naay mga previous records
            $arrayreg = pg_fetch_array($resultreg, 0, PGSQL_NUM);
/*---------------------------------------------------------------------------------------------------------------------*/
            //query to check if naay record sa admission -> for shiftee
            $querycidshift = "SELECT courseid FROM sis.admission 
                            WHERE idno = '$_POST[studid]' AND syearid = '".$_SESSION['sy_sem_input']."'";

            $resultcidshift = pg_query($querycidshift) or die('Query failed:'.pg_last_error());

            $rowcidshift = pg_fetch_row($resultcidshift);
            //query to check if naay record sa admission -> for shiftee
/*---------------------------------------------------------------------------------------------------------------------*/
            //if walay record sa admission, regular. else, shiftee
            if($rowcidshift == 0)
            {
                //regular students

                $querytableprospectus = "SELECT sub.subjectname, sub.descriptivetitle, cs.yearlevel, cs.semester, 
                        c.coursename, c.major, c.courseid
                        FROM sis.course c, sis.subject sub, sis.course_subject cs, sis.registration reg, sis.student s
                        WHERE s.idno = reg.idno
                        AND reg.courseid = c.courseid
                        AND c.courseid = cs.courseid
                        AND sub.subjectid = cs.subjectid
                        AND reg.regno = '$arrayreg[0]'
                        ORDER BY cs.yearlevel, cs.semester";

                $querytablegrades = "SELECT sub.subjectname, sub.descriptivetitle, es.grade, es.completiongrade
                                    FROM sis.subject sub, sis.enrolledsubject es, sis.registration reg, sis.offering offe
                                    WHERE reg.regno = es.regno AND offe.offeringno = es.offeringno 
                                    AND sub.subjectid = offe.subjectid AND reg.idno = '$_POST[studid]'
                                    ORDER BY reg.regno";

                //diri nato gibutang ang admissiontype sa student. kabalo man ta nga transferee or freshman siya kay diri man ma fall ang new and transferee student.
                $studentstatus = "Regular";
            }
            else
            {
                //shiftee students

                //query for selecting courseid in admission
                $querycid = "SELECT courseid, admissionno, admissiontype FROM sis.admission 
                             WHERE idno = '$_POST[studid]' AND syearid = '".$_SESSION['sy_sem_input']."'";

                $resultcid = pg_query($querycid) or die('Query failed:'.pg_last_error());

                $arraycid = pg_fetch_array($resultcid, 0, PGSQL_NUM);
                //end of query for selecting courseid

                //display table
                $querytableprospectus = "SELECT sub.subjectname, sub.descriptivetitle, cs.yearlevel, cs.semester,
                            c.coursename, c.major, c.courseid
                            FROM sis.course c, sis.subject sub, sis.course_subject cs, sis.admission ad, sis.student s
                            WHERE s.idno = ad.idno
                            AND ad.courseid = c.courseid
                            AND c.courseid = cs.courseid
                            AND sub.subjectid = cs.subjectid
                            AND ad.admissionno = '$arraycid[1]'
                            ORDER BY cs.yearlevel, cs.semester";

                $querytablegrades = "SELECT sub.subjectname, sub.descriptivetitle, es.grade, es.completiongrade
                                    FROM sis.subject sub, sis.enrolledsubject es, sis.registration reg, sis.offering offe
                                    WHERE reg.regno = es.regno AND offe.offeringno = es.offeringno 
                                    AND sub.subjectid = offe.subjectid AND reg.idno = '$_POST[studid]'
                                    ORDER BY reg.regno";

                //diri nato gibutang ang admissiontype sa student. kabalo man ta nga transferee or freshman siya kay diri man ma fall ang new and transferee student.
                $studentstatus = $arraycid[2];
            }

        }

/*NEW UPDATE FOR PHP CODES AS OF 11/19/2016 ENDS HERE. Ayaw sa ni pansina if ga create pa ka sa student details. Para ra ni sa displaying of Prospectus and Grades. Wala ni kinalaman sa pag save or edit sa COR. I disregard lang sa ni kay ang use ani kay i display ang prospectus and grades table kay para ma determine sa adviser if i promote ba siya to next year level. DILI ni necessary sa creation or edit of COR. i IGNORE sa ni and focus sa main points.*/

    }
}
?>

<!--NEW UPDATE AS OF 11/23/16. Gi wala na to nako ang "prosepctus for chuchu. Ako na gibutang sa student enrollment status"-->
<?php
    if(isset($_POST['studid']))
    {
        $query = $querytableprospectus;

        $result = pg_query($query) or die('Query failed:'.pg_last_error());                

        $myarr = pg_fetch_array($result);
    }
?>
<!--******************************************************************************************************************-->

<nav class="navbar navbar-inverse">
  <div class="container-fluid">
  
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav navbar-right">
        <li><a href="#" >Welcome <?php echo $_SESSION['uname'];?></a></li>
        <li><a href="logoutscript.php"><span class="glyphicon glyphicon-log-out"></span>Logout</a></li>
      </ul>
    </div>

  </div>
</nav>

<!--******************************************************************************************************************-->

<div class="jumbotron text-center">
	<h1>Mindanao State University</h1>
	<p>Iligan Institute of Technology</p>
</div>	

<!--******************************************************************************************************************-->

<div class="container text-center">
    <h4 align="left"><b>Create COR For School Year And Semester</b></h4>
</div>

<div class= "container well form-inline">
	<div class="form-group">
		<label for="semester">School Year:</label>
		<input type="text" class="form-control" id="cursyr" name="cursyr" size="9" value="<?php echo $_SESSION['cur_sy'];?>" readonly="true">
        <label for="semester">Semester:</label>
		<input type="text" class="form-control" id="cursem" name="cursem" size="5" value="<?php echo $_SESSION['cur_sem'];?>" readonly="true">
	</div>
</div>

<!--******************************************************************************************************************-->

<div class="container text-center">
    <h4 align="left"><b>Student Previous Semester Information</b></h4>
</div>

<!--******************************************************************************************************************-->

<div class="container well">
<form class="form-inline" method="POST" action="" id="myForm">
<div class="form-group">
    <label class="control-label">Student: </label>
    <input type="text" class="form-control" id="studid" required name="studid" size="21px" placeholder="Enter ID number" value="<?php if(isset($_POST['studid'])){ echo $_POST['studid'];}?>">
    <button type="submit" class="btn btn-primary btn-s" id="okbtn" name="okbtn">OK</button>
    <input type="text" class="form-control" id="studname" name="studname" size="60" value="<?php if(isset($_POST['studid'])){ echo $array1[1] . ' ' . $array1[3] . ' ' . $array1[2];}?>" readonly="true">
 </div>
 <br>
 <br>
 <div class="form-group">
    <label class="control-label">Year Level: </label>&nbsp;&nbsp;
    <input type="number" class="form-control" id="yrlvl" name="yrlvl" required value="<?php if(isset($_POST['studid'])){ echo $array[0];}?>" style="width: 7em" readonly="true">
    <label class="control-label">Scholastic Status: </label>
    <input type="text" class="form-control" id="status" name="status" size="60" value="<?php if(isset($_POST['studid'])){ echo $array[1];}?>" readonly="true">
 </div>
 <br>
 <br>
 <div class="form-group">
    <label class="control-label">Course: </label>
    <input type="text" class="form-control" id="courseid" name="courseid" size="27" value="<?php if(isset($_POST['studid'])){ echo $array[2];}?>" readonly="true"> &nbsp;
    <input type="text" class="form-control" id="coursename" name="coursename" size="60" value="<?php if(isset($_POST['studid'])){ echo $array[3];}?>" readonly="true">
 </div>
 <br>
 <br>
 <div class="form-group form-inline">
    <button type="button" class="btn btn-primary btn-s" id="new" name="new" style="height:34px;width:70px">New</button>
    <button type="button" class="btn btn-primary btn-s" id="edit" name="edit" style="height:34px;width:70px">Edit</button>
    <button 
        type="submit" class="btn btn-primary btn-s" id="save" name="save" disabled="true" style="height:34px;width:70px">Save
    </button>
    <button type="button" class="btn btn-primary btn-s" id="cancel" name="cancel" disabled="true">Cancel</button>
 </div>
 </form>
</div>

<div class="container text-center">
    <h4 align="left"><b>Student Current Enrollment Information</b></h4>
</div>

<div class="container well">
    <form class="form-inline" method="POST" action="" id="form2">
        <div class="form-group">
            <label class="control-label">Admission Status: </label><br>
            <input type="text" class="form-control" id="studstatus" name="studstatus" size="27" value="<?php if(isset($_POST['studid'])){ echo $studentstatus;}?>" readonly="true">
        </div>
        <br>
        <br>
        <div class="form-group">
            <label class="control-label">Current Admitted Course: </label><br>
            <input type="text" class="form-control" id="curcourseid" name="courseid" size="27" value="<?php if(isset($_POST['studid'])){ echo $myarr[6];}?>" readonly="true"> &nbsp;
            <input type="text" class="form-control" id="curcoursename" name="coursename" size="60" value="<?php if(isset($_POST['studid'])){ echo $myarr[4];}?>" readonly="true">
        </div>
    </form>
</div>
<!--                         NEW UPDATE FOR HTML(TABLES) CODES AS OF 11/19/2016 STARTS HERE                           -->

<!--******************************************************************************************************************-->

<!--i observe gani ang closing bracket sa "if(isset($_POST['studid']))" sa ubos diba wala? Nakadumdom ka atong giingon nako nimo nga dili pwde magbutang ug html sulod sa php? Then if magbutang man ganing html sulod sa php kay dapat i paagi nimo sa echo? Naay alternative solution ana. Putlon nimo ang bracket sa kung unsa man ganing condition, den didto nimo isumpay sa pinakalast. Observe as you scroll down pangitaa ang bracket diba naa na sa closing </div> sa container sa table nato.-->
<?php
if(isset($_POST['studid']))
{

?>

<div class="container">
<!--hr means mo draw siyag horizonal line. Mura siyag divider kunohay-->
<hr>

    <div class="row">
        <div class="col-sm-6">
            <table class="table table-hover table table-striped">

                <h4 align="left"><b>Prospectus</b></h4>
                <thead> 
                    <tr> 
                        <th>Subject Name</th>
                        <th>Descriptive Title</th>
                        <th>Year Level</th>
                        <th>Semester</th>
                    </tr> 
                </thead>
                <body>
                    <?php 
                        if(isset($_POST['studid']))
                        {
                            //diri nako gigamit si querytable
                            $query = $querytableprospectus;

                            $result = pg_query($query) or die('Query failed:'.pg_last_error());                

                            if (!$result)
                            { 
                                echo "Problem with query " . $query . "<br/>"; 
                                echo pg_last_error(); 
                                exit(); 
                            } 

                            while($myrow = pg_fetch_array($result))
                            { 
                                printf ("<tr align=\"left\">
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                        </tr>", $myrow[0], htmlspecialchars($myrow[1]), htmlspecialchars($myrow[2]), htmlspecialchars($myrow[3]));
                            }
                        } 
                    ?>
                </body> 
            </table>
        </div>

        <div class="col-sm-6"> 
            <table class="table table-hover table table-striped">
                <h4 align="left"><b>Entire Student Grades and Subjects</b>

                <thead> 
                    <tr> 
                        <th>Subject Name</th>
                        <th>Descriptive Title</th>
                        <th>Grade</th>
                        <th>Completion Grade</th>
                    </tr> 
                </thead>
                <body>
                    <?php 
                        if(isset($_POST['studid']))
                        {
                            //diri nako gigamit si querytablegrades
                            $query = $querytablegrades;

                            $result = pg_query($query) or die('Query failed:'.pg_last_error());                

                            if (!$result)
                            { 
                                echo "Problem with query " . $query . "<br/>"; 
                                echo pg_last_error(); 
                                exit(); 
                            } 

                            while($myrow = pg_fetch_array($result))
                            { 
                                printf ("<tr align=\"left\">
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                            <td>%s</td>
                                        </tr>", $myrow[0], htmlspecialchars($myrow[1]), htmlspecialchars($myrow[2]), htmlspecialchars($myrow[3]));
                            }
                        } 
                    ?>
                </body> 
            </table>
        </div>
    </div>
</div>
<!--tanawa mao ni ang closing bracket sa atong if(isset($_POST['studid'])). Observe diba ang closing bracket "}" kay gi enclose nako siyang <?php?> nga tag. Hunahunaon nalang nimo nga if ever man gani nga gusto ka mag ingani na style, dapat each php nga element wether if-else or brackets or anything as long as php element siya kay dapat i enclose jud nimo siyang opening tag sa php and closing tag sa php. Dapat complete parehas ani <?php?>-->
<?php } ?>
<!--                          NEW UPDATE FOR HTML(TABLES) CODES AS OF 11/19/2016 ENDS HERE                            -->

<!--******************************************************************************************************************-->

<!--                                 SCRIPTS PARA SA BUTTONS DISABLE/ENABLE!!!!                                       -->

<!-- if ang new button gi press, ang new button, edit button, ok button kay ma disabble while ang yearl level, save ug cancel kay ma enable. Take note ang studentid na input kay readOnly siya instead sa disable kay if i disable nimo, dili siya ma apil ug submit. readOnly means ga exist japon ang data, pero dili lang ma edit. While ang disable is from the word itself i disable siya, dili siya ma access/submit. REMEMBER! readOnly means pwede ma submit at the same time kay uneditable. disabled means dili pwede ma submit ang at the same time kay uneditable-->
<script type="text/javascript">
var okbbtn  = document.getElementById('okbtn');
var newbtn = document.getElementById('new');
var editbtn = document.getElementById('edit');
var savebtn = document.getElementById('save');
var cancelbtn = document.getElementById('cancel');
var studidtx = document.getElementById('studid');
var yrlvltx = document.getElementById('yrlvl');
newbtn.addEventListener('click', function(){
    newbtn.disabled = true;
    editbtn.disabled = true;
    studidtx.readOnly = true;
    okbbtn.disabled = true;
    yrlvltx.readOnly = false;
    savebtn.disabled = false;
    cancelbtn.disabled = false;
});
</script>


<!--same thing sa new button pero ang edit button lang ang trigger ani while katong isa ky ang new button ang trigger-->
<script type="text/javascript">
var okbbtn  = document.getElementById('okbtn');
var newbtn = document.getElementById('new');
var editbtn = document.getElementById('edit');
var savebtn = document.getElementById('save');
var cancelbtn = document.getElementById('cancel');
var studidtx = document.getElementById('studid');
var yrlvltx = document.getElementById('yrlvl');
editbtn.addEventListener('click', function(){
    newbtn.disabled = true;
    editbtn.disabled = true;
    studidtx.readOnly = true;
    okbbtn.disabled = true;
    yrlvltx.readOnly = false;
    savebtn.disabled = false;
    cancelbtn.disabled = false;
});
</script>

<!--if ang ok button gi press, ilisan ang form action didto sa form nato sa taas to blank. Meaning ang dawaton niya na action is katong php codes nga local diri na page/file(katong mga php nga naa ra diri sa file ang basahon, dili tong mga separate files like save.php). By default, ang action gyud sa form nato is blank para ang local ra iya basahon-->
<script type="text/javascript">
document.getElementById("okbtn").onclick = function() { 
  document.getElementById("myForm").action = "";
};
</script>

<!--if ang new button ang gi press, mailisdan ang action sa form nato sa taas into save.php para ang basahon na php codes is katong naa sa save.php-->
<script type="text/javascript">
document.getElementById("new").onclick = function() { 
  document.getElementById("myForm").action = "save.php";
};
</script>

<!--if ang edit button ang gi press, mailisdan ang action sa form nato sa taas into edit.php para ang basahon na php codes is katong naa sa edit.php-->
<script type="text/javascript">
document.getElementById("edit").onclick = function() { 
  document.getElementById("myForm").action = "edit.php";
};
</script>

<!--if ang cancel button ang gi press, walay mabago ani ha. Ang gibuhat ra jud ani is i balik ra niya sa pinaka una na state sa webpage the first time gi open siya.-->
<script type="text/javascript">
var okbbtn = document.getElementById('okbtn');
var newbtn = document.getElementById('new');
var editbtn = document.getElementById('edit');
var savebtn = document.getElementById('save');
var cancelbtn = document.getElementById('cancel');
var studidtx = document.getElementById('studid');
cancelbtn.addEventListener('click', function(){
    newbtn.disabled = false;
    editbtn.disabled = false;
    studidtx.readOnly = false;
    okbbtn.disabled = false;
    yrlvltx.readOnly = true;
    savebtn.disabled = true;
    cancelbtn.disabled = true;
});
</script>


</body>
</html>
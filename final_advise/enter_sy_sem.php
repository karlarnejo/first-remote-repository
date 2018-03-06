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
	session_start();
	include 'connection.php';
?>

<div class="jumbotron text-center">
        <h1>Mindanao State University</h1>
        <p>Iligan Institute Of Technology</p>
</div>

<div class="container">
	<form class="form-horizontal" action="open_mainpage.php" method="POST">

		<div class="form-group">
      		<h4 align="center">Enter School Year | Semester</h4>
      			<div class="col-sm-12">
        			<select name="selectterm" class="form-control" required>
                        <option value="">Select School Year | Semester</option>
                        <?php

                        $query = "SELECT syearid, schooyear, semester FROM sis.schoolyear ORDER BY schooyear, semester";
                        $list = pg_query($query);

                        //akong gi fetch array gbutang sa $row_list para mahimo siyang array.
                        //akong dayon gi loop kay dili biya pwede duha ka value mabutang sa isa ka index. So ako gi loop para sa isa ka loop, isa ra ka value masulod sa $row_list, iya dayon i print. Den another loop, mailisdan nasad ang values sa $row_list, iya nasad dayon i print. Den another loop and so on.
                        while($row_list=pg_fetch_array($list))
                        {
                            printf ("<option value=%s>School Year: %s | Semester: %s
                                     </option>", $row_list[0], htmlspecialchars($row_list[1]), htmlspecialchars($row_list[2])); 
                        }
                        ?> 
                    </select>
      			</div>
    	</div>		

    	<div class="form-group">
      		<div class="col-sm-12">
        		<button type="submit" class="btn btn-success" name="sy_sem">Enter</button>
      		</div>
    	</div>

	</form>
</div>

</body>
</html>




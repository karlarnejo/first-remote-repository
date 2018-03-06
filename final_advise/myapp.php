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

<div class="jumbotron text-center">
        <h1>Mindanao State University</h1>
        <p>Iligan Institute Of Technology</p>
</div>

<div class="container">
  <form class="form-horizontal" action="loginscript.php" method="POST">
    
    <div class="form-group">
      <label class="control-label col-sm-2">Username:</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" name="uname" placeholder="Enter username">
      </div>
    </div>
    
    <div class="form-group">
      <label class="control-label col-sm-2">Password:</label>
      <div class="col-sm-10">
        <input type="password" class="form-control" name="psw" placeholder="Enter password">
      </div>
    </div>
  
    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-default" name="login">Login</button>
      </div>
    </div>

  </form>
</div>

</div>
</body>
</html>
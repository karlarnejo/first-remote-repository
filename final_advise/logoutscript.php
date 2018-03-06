<?php
session_start();
	if(session_destroy()) // Destroying All Sessions
	{
		header("Location: myapp.php", true, 301); // Redirecting To Home Page
		exit();
	}
?>
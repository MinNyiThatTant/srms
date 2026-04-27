<?php
session_start(); // Call/start session

// delete session variable
session_unset();

// delete all sessions
session_destroy();

header("Location: admin.php");
exit();
?>
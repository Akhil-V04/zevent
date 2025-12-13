<?php
session_start();

/* Clear all session data */
session_unset();
session_destroy();

/* Redirect to combined login page */
header("Location: auth/login.php");
exit;

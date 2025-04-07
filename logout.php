<?php
require_once('config.php');

if (isset($_SESSION['session_token'])) {
    $session_token = $_SESSION['session_token'];


    $query = "DELETE FROM sessions WHERE session_token = '$session_token'";
    mysqli_query($conn, $query);

   
    session_unset();
    session_destroy();

    header("Location: login.php");
}
?>
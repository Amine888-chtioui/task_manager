<?php
require_once('config.php');

if (isset($_SESSION['session_token'])) {
    $session_token = $_SESSION['session_token'];

    // حذف الجلسة من قاعدة البيانات
    $query = "DELETE FROM sessions WHERE session_token = '$session_token'";
    mysqli_query($conn, $query);

    // حذف بيانات الجلسة
    session_unset();
    session_destroy();

    header("Location: login.php");
}
?>
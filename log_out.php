<?php
session_start();
session_unset();
session_destroy();

echo "<script> window.setTimeout(function() { window.location='index.php'; }, 500); </script>";

die();
?>
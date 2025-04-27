<?php
session_start();
session_destroy();
header("Location: /RestoPay/");
exit;
?>

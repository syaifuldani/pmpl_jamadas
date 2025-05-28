<?php
session_start();
// Hapus session chat
unset($_SESSION['current_chat']);
// Hapus semua session
session_unset();
session_destroy();
header("Location: index.php");
exit();
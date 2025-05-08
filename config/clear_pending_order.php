<?php
session_start();
header('Content-Type: application/json');

unset($_SESSION['pending_order']);
echo json_encode(['status' => 'success']);
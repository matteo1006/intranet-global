<?php
session_start();
require_once "db_config.php";

if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type'];
    $id = $_GET['id'];
    $table = "";

    switch ($type) {
        case "site":
            $table = "sites";
            break;
        case "service":
            $table = "services";
            break;
        case "line":
            $table = "lines";
            break;
        case "operator":
            $table = "operators";
            break;
        case "user":
             $table = "users";
             break;
    }

    if ($table) {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header("Location: admin.php");

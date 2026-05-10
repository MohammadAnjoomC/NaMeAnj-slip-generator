<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) { exit("Unauthorized"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    $baseName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $_POST['name']);
    $pdfData = base64_decode($_POST['pdf']);
    $user_dir = "../user/" . $username;

    if (!is_dir($user_dir)) { mkdir($user_dir, 0777, true); }

    $filename = $baseName . ".pdf";
    $count = 1;

    // Versioning Logic: Check if file exists, if so, add (1), (2)...
    while (file_exists($user_dir . "/" . $filename)) {
        $filename = $baseName . "(" . $count . ").pdf";
        $count++;
    }

    if (file_put_contents($user_dir . "/" . $filename, $pdfData)) {
        echo "success";
    } else {
        echo "failed";
    }
}
?>


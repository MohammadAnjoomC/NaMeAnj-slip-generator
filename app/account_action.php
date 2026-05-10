<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) { exit("Unauthorized"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    $user_dir = "../user/" . $username;

    if (!is_dir($user_dir)) { mkdir($user_dir, 0777, true); }

    // 1. Profile Picture Logic
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['tmp_name'] !== '') {
        $target_file = $user_dir . "/profile.jpg";
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file);
    }

    // 2. Default Text Logic
    if (isset($_POST['default_l1']) || isset($_POST['default_l2']) || isset($_POST['default_l3'])) {
        $user_db_path = __DIR__ . '/../database/users.json';
        if (file_exists($user_db_path)) {
            $users = json_decode(file_get_contents($user_db_path), true);
            foreach ($users as &$user) {
                if ($user['username'] === $username) {
                    $user['default_l1'] = $_POST['default_l1'] ?? '';
                    $user['default_l2'] = $_POST['default_l2'] ?? '';
                    $user['default_l3'] = $_POST['default_l3'] ?? '';
                    // Update session too for instant access in editor
                    $_SESSION['default_l1'] = $user['default_l1'];
                    $_SESSION['default_l2'] = $user['default_l2'];
                    $_SESSION['default_l3'] = $user['default_l3'];
                    break;
                }
            }
            file_put_contents($user_db_path, json_decode(json_encode($users), true)); // ensure valid JSON
            file_put_contents($user_db_path, json_encode($users, JSON_PRETTY_PRINT));
        }
    }

    header("Location: account.php?msg=success");
    exit();
}
?>

<?php
/**
 * NaMeAnj | Admin Upload Logic
 * Purpose: Receives Base64 cropped images and saves them as .jpg files in the library.
 */
session_start();

// --- 1. SECURITY CHECK ---
// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    exit("Unauthorized Access");
}

// --- 2. DIRECTORY SETUP ---
// Set path to save template
$template_dir = __DIR__ . '/../assets/templates/';
if (!file_exists($template_dir)) {
    mkdir($template_dir, 0777, true);
}

// --- 3. DATA PROCESSING ---
// Check image data and filename from POST
if (isset($_POST['image']) && isset($_POST['filename'])) {
    
    $raw_data = $_POST['image']; // Base64 String
    $original_name = $_POST['filename'];

    // Clean special characters from filename
    $clean_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $original_name);
    
    // Create clean filename without long timestamp, handling duplicates gracefully
    $final_filename = $clean_name . ".jpg";
    $counter = 1;
    while (file_exists($template_dir . $final_filename)) {
        $final_filename = $clean_name . "_" . $counter . ".jpg";
        $counter++;
    }

    // Extract image file from Base64 data
    // (e.g., take only xxxx from "data:image/jpeg;base64,xxxx")
    try {
        list($type, $raw_data) = explode(';', $raw_data);
        list(, $raw_data)      = explode(',', $raw_data);
        $decoded_image = base64_decode($raw_data);

        // Save file to server
        if (file_put_contents($template_dir . $final_filename, $decoded_image)) {
            echo "success";
        } else {
            echo "error_saving_file";
        }
    } catch (Exception $e) {
        echo "error_processing_data";
    }
} else {
    echo "invalid_request";
}
?>



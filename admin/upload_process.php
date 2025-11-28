<?php
session_start();
include '../db.php'; 

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Composer autoloader එක include කරනවා
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['upload_btn'])) {

    if (empty($_POST['category_id'])) {
        $_SESSION['upload_message'] = "Error: කරුණාකර Category එකක් තෝරන්න.";
        header('Location: bulk_upload.php');
        exit();
    }
    $selectedCategoryId = (int)$_POST['category_id'];

    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] != 0) {
        $_SESSION['upload_message'] = "Error: File එක Upload කිරීමේදී දෝෂයක් ඇතිවිය.";
        header('Location: bulk_upload.php');
        exit();
    }
        
    $uploadedFilePath = $_FILES['excel_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($uploadedFilePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        if ($highestRow < 2) {
            $_SESSION['upload_message'] = "Error: Excel ගොනුව හිස් හෝ දත්ත අඩංගු නැත.";
            header('Location: bulk_upload.php');
            exit();
        }

        $successful_uploads = 0;
        $failed_rows = 0;
        $failed_row_numbers = [];

        // ඔයාගේ 'add_videos.php' එකේ තියෙන logic එකටම ගැලපෙන්න හදලා තියෙන්නේ
        $stmt = $conn->prepare("INSERT INTO videos (category_id, video_link, thumbnail_link, title, duration) VALUES (?, ?, ?, ?, ?)");

        for ($row = 2; $row <= $highestRow; $row++) {
            
            $videoLink = $sheet->getCell('A' . $row)->getValue();
            $thumbnailLink = $sheet->getCell('B' . $row)->getValue();
            $title = $sheet->getCell('C' . $row)->getValue();
            $duration = $sheet->getCell('D' . $row)->getValue();

            if (empty($title) || empty($videoLink)) {
                $failed_rows++;
                $failed_row_numbers[] = $row;
                continue; 
            }
            
            // Prepared Statement එකක් පාවිච්චි කරනවා SQL Injection වලින් සම්පූර්ණයෙන්ම බේරෙන්න
            $stmt->bind_param("issss", $selectedCategoryId, $videoLink, $thumbnailLink, $title, $duration);

            if ($stmt->execute()) {
                $successful_uploads++;
            } else {
                $failed_rows++;
                $failed_row_numbers[] = $row;
            }
        }
        
        $stmt->close();

        $message = "ක්‍රියාවලිය අවසන්! සාර්ථකව Videos " . $successful_uploads . " ක් ඇතුලත් කරන ලදී.";
        if ($failed_rows > 0) {
            $message .= "<br>අසාර්ථක වූ පේළි ගණන: " . $failed_rows . " (Excel Rows: " . implode(', ', $failed_row_numbers) . ")";
        }
        $_SESSION['upload_message'] = $message;

    } catch (Exception $e) {
        $_SESSION['upload_message'] = "Error processing file: File is corrupted or not a valid Excel file.";
    }

    header('Location: bulk_upload.php');
    exit();
}
?>
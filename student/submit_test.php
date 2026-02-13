<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $test_id = $_POST['test_id'];
    $test_type = $_POST['test_type']; // Full, Task 1, Task 2
    $user_id = $_SESSION['user_id'];

    $submission_data = '';

    if ($test_type === 'Full') {
        $task1 = $_POST['answer_task1'] ?? '';
        $task2 = $_POST['answer_task2'] ?? '';
        $submission_data = json_encode(['task1' => $task1, 'task2' => $task2]);
    } else {
        $submission_data = $_POST['answer'] ?? '';
    }

    // Validate
    if (empty($submission_data)) {
        flash('error', "Cannot submit empty test.");
        redirect("take_test.php?id=$test_id");
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO test_results (user_id, test_id, type, submission_data, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $test_id, $test_type, $submission_data]);
        $result_id = $pdo->lastInsertId();

        flash('success', "Test submitted successfully! Evaluation pending.");
        redirect("result.php?id=$result_id");
    } catch (PDOException $e) {
        flash('error', "Database error: " . $e->getMessage());
        redirect("mock_test.php");
    }
} else {
    redirect("mock_test.php");
}
?>
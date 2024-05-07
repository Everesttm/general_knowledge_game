<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'path/to/your/logfile.log');
ob_start();

session_start();
require_once 'database.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getCategories':
        getCategories();
        break;
    case 'getQuestions':
        getQuestions($_GET['categoryId'] ?? null);
        break;
    case 'updateScore':
        if (isset($_POST['score']) && isset($_SESSION['userID'])) {
            updateScore($db, $_SESSION['userID'], $_POST['score']);
        }
        break;
    case 'markQuestionAnswered':
        if (isset($_POST['question_id'])) {
            markQuestionAsAnswered($_POST['question_id']);
        }
        break;
    default:
        echoCleanJson(['error' => 'Invalid action']);
}

function getCategories() {
    global $db;
    $query = "SELECT id, title AS name FROM j_category ORDER BY id";
    $result = $db->query($query);
    if ($result) {
        echoCleanJson($result->fetch_all(MYSQLI_ASSOC));
    } else {
        echoCleanJson(['error' => 'Failed to fetch categories: ' . $db->error]);
    }
}

function getQuestions($categoryId) {
    global $db;
    if (!$categoryId) {
        echoCleanJson(['error' => 'Category ID is required']);
        return;
    }
    $query = "SELECT j_question.id, j_question.question, j_question.answer, j_value.value, j_question.is_answered
              FROM j_question
              JOIN j_value ON j_question.value_id = j_value.id
              WHERE j_question.category_id = ?";
    if ($stmt = $db->prepare($query)) {
        $stmt->bind_param('i', $categoryId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $questions = $result->fetch_all(MYSQLI_ASSOC);
            echoCleanJson($questions);
        } else {
            echoCleanJson(['error' => 'Failed to execute prepared statement: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echoCleanJson(['error' => 'Failed to prepare statement: ' . $db->error]);
    }
}

function markQuestionAsAnswered($questionId) {
    global $db;
    $query = "UPDATE j_question SET is_answered = 1 WHERE id = ?";
    if ($stmt = $db->prepare($query)) {
        $stmt->bind_param("i", $questionId);
        if ($stmt->execute()) {
            if ($stmt->affected_rows === 0) {
                echoCleanJson(['error' => 'No question was updated, possibly the ID was not found.']);
            } else {
                echoCleanJson(['success' => true]);
            }
        } else {
            echoCleanJson(['error' => 'Failed to execute the query to update question status: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echoCleanJson(['error' => 'Failed to prepare statement: ' . $db->error]);
    }
}

function updateScore($db, $userId, $score) {
    $query = "UPDATE j_user SET score = score + ? WHERE id = ?";
    if ($stmt = $db->prepare($query)) {
        $stmt->bind_param("ii", $score, $userId);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Error updating score: " . $db->error;
    }
}

function echoCleanJson($data) {
    ob_clean();
    echo json_encode($data);
    exit;
}

?>

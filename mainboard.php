<?php
session_start();

if (!isset($_SESSION['userID']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

include 'database.php';

$userId = $_SESSION['userID'];

$query = "SELECT game_state, score FROM j_user LEFT JOIN j_game_states ON j_user.id = j_game_states.userID WHERE j_user.id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $initialGameState = json_decode($row['game_state'], true);
    $_SESSION['score'] = $row['score'] ?? 0;
} else {
    $initialGameState = null;
    $_SESSION['score'] = 0; 
}

$encodedGameState = json_encode($initialGameState);

$stmt->close();
$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>General Knowledge Game</title>
    <link rel="stylesheet" href="mainBoard.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script defer src="game.js"></script>
</head>
<body>
    <div id="mainContainer">
        <header>
            <h1>General Knowledge Game</h1>
        </header>
        <nav>
            <button id="help">Help</button>
            <a href="logout.php" class="button">Logout</a>
            <button id="reset-game"> Reset Score </button>
        </nav>
        <section id="gameBoard">
            <div>CHOOSE YOUR CATEGORY</div>
            <div id="category-container"></div>
            <p id="selected-category"></p>
            <div id="question-container"></div>

        </section>
        <footer>
            <div id="game-info">
                <div>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
                <p id="score-value">Your Score: <span id="current-score"><?php echo $_SESSION['score']; ?></span></p>
                <p id="game-timer">Time: <span>00:00:00</span></p>
            </div>
        </footer>
    </div>
</body>
</html>


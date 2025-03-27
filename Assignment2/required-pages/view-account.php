<?php
session_start();
include "./includes/library.php";
$pdo = connectdb();

//if user is not logged in redirect them back to log in page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

//fecth user detail
$query = "select username, email, api_key from USERS where id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

//regenerate api key if requested
if($_SERVER['REQUEST_METHOD'] === 'POST') { // regenerate button is submitted
    $new_api_key = bin2hex(random_bytes(16));
    //update user api key in the db
    $query = "update USERS set api_key = ?, api_date = NOW() where id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$new_api_key, $_SESSION['user_id']]);

    //update api key in the session
    $_SESSION['api_key'] = $new_api_key;

    // Redirect with a success message
    header("Location: view-account.php?success=API+key+successfully+regenerated+!");
    exit();
}
$timeout_duration = 600; // 10'

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: homepage.php?timeout=true");
    exit();
}

// Update the last activity time
$_SESSION['LAST_ACTIVITY'] = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> View Account </title>
    <link rel="stylesheet" href="./styles/main.css">
</head>

<body id="view-acc">
    <div id="header">
        <a href="homepage.php"> MovieLand </a>
        <img src="./tv.png" alt="TV retro style"/>
    </div>

    <div id="acc-info">
        <h1> Account Details </h1>
        <ol>
            <li>Username: <?php echo ($user['username']); ?></li>
            <li>Email: <?php echo ($user['email']); ?></li>
            <li>API Key: <?php echo ($_SESSION['api_key']); ?></li>
        </ol>
        <form method="post" action="">
            <button type="submit">Regenerate API Key</button>
            <a href="logout.php">Log out</a>
        </form>
        <?php if (isset($_GET['success'])): ?>
        <p class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></p>
        <?php endif; ?>
    </div>
    <footer>&copy; MovieLand, Inc. 2024</footer>
</body>
</html>

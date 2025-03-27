<?php
session_start();
include "./includes/library.php";
include "./includes/response.php";
$pdo = connectdb();
$username = $_POST['username'] ?? " ";
$password = $_POST['password'] ?? " ";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(empty($username)) {
        $errors['username'] = "You must provide a username";
    }

    // fetch user from the database
    $query = "select id, password, api_key from USERS where username = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //user is found in the db and password is correct
    //compare entered password vs password of the user stored in the db
    if($user && password_verify($password, $user['password'])) {
        //store user id and api key in the session array
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['api_key'] = $user['api_key'];
        
        //redirect to view-account
        header("Location: view-account.php");
        exit();
    }
    else { 
        json_response(401, ["error" => "Invalid username or password"]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> Login </title>
    <link rel="stylesheet" href="./styles/main.css">
</head>

<body id="login">
    <div id="header">
        <a href="homepage.php"> MovieLand </a>
        <img src="./tv.png" alt="TV retro style"/>
    </div>

    <div id="login-form">
        <h1> Login </h1>
        <form method="post" >
            <label for="username">Username: </label>
            <input type="text" id="username" name="username" value="<?= $username ?>"/>
            <span class="error <?= !isset($errors['name']) ? 'hidden' : '' ?>">
                <?= $errors['username'] ?? '' ?>
            </span>
            <br>

            <label for="password">Password: </label>
            <input type="password" id="password" name="password"><br>

            <button id="submit" type="submit" name="submit"><strong>Submit</strong></button>
        </form>
    </div>
    <footer>&copy; MovieLand, Inc. 2024</footer>
</body>
</html>

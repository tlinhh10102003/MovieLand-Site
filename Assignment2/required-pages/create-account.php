<?php
include "./includes/library.php";
$pdo = connectdb();
$username = $_POST['username'] ?? " ";
$email = $_POST['email'] ?? " ";
$password = $_POST['password'] ?? " ";
$errors = [];
$successMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    // if username is empty 
    if(empty($username)) {
        $errors['username'] = "You must provide a username";
    }
    else {
        //validate username uniqueness
        $query = "select id from USERS where username = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $errors['username'] = "Username already existed";
        }
    }

    //validate email
    if (empty(filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $errors['email'] = "Please provide a valid email address";
    }

    //validate pwd 
    if(empty($password)) {
        $errors['password'] = 'Please enter your password';
    }
    elseif (strlen($password) < 13  || !preg_match('/[0-9]/', $password) || !preg_match('/[~`!@#$%^&*()\-_=+{}\[\]|\\:;"<>,.?\/]/', $password) || !preg_match('/[A-Z]/', $password)) {
        $errors['password'] = "Password must be at least 12 characters long, minimum of 1 numeric character, 
                    minimum of 1 special character, and minimum of 1 uppercase letter";
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $api_key = bin2hex(random_bytes(16));
        // Insert the user into the database
        $query = "insert into USERS (username, email, password, api_key, api_date) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$username, $email, $password_hash, $api_key]);

        // Set the success message after successful account creation
        $successMessage = "Account created successfully! Your API key is: $api_key";
    } 
}
?>
<!DOCTYPE html>
<html lang="en">                                                
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <link rel="stylesheet" href="./styles/main.css">
</head>

<body id="create-acc">
    <div id="header">
        <a href="homepage.php"> MovieLand </a>
        <img src="./tv.png" alt="TV retro style"/>
    </div>

    <div id="sign-up">
        <h1> Create Account </h1>
        <form method="post" >
            <label for="username">Username: </label>
            <input type="text" id="username" name="username" value="<?= $username ?>"><br>
            <span class="error <?= !isset($errors['name']) ? 'hidden' : '' ?>">
                <?= $errors['username'] ?? '' ?>
            </span>

            <label for="email">Email: </label>
            <input type="text" id="email" name="email" value="<?= $email ?>"><br>
            <span class="error <?= !isset($errors['email']) ? 'hidden' : '' ?>">
                <?= $errors['email'] ?? '' ?>    
            </span>

            <label for="password">Password: </label>
            <input type="password" id="password" name="password"><br>
            <span class="error <?= !isset($errors['password']) ? 'hidden' : '' ?>">
                <?= $errors['password'] ?? '' ?>
            </span>

            <button id="submit" type="submit" name="submit"><strong>Create Account</strong></button>
        </form>
    </div>

    <div id="success-message">
        <?php 
        if (!empty($successMessage)) {
            echo "<p>$successMessage</p>";
        }
        ?>
    </div>

    <footer>&copy; MovieLand, Inc. 2024</footer>
</body>
</html>

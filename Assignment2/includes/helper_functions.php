<?php
function getUserId($apiKey) {
    $pdo = connectdb();
    $query = "select id from USERS where api_key = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$apiKey]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // return user ID if found, null if not found
    return $user ? $user['id'] : null;
}

function getApiKey() {
    // chekc if the API key is provided in the request headers
    if (isset($_SERVER['HTTP_X_API_KEY'])) {
        return $_SERVER['HTTP_X_API_KEY'];
    }
    return null;
}

function validateApiKey() {
    $api_key = getApiKey();
    if (!$api_key) {
        json_response(403, ['error' => 'An API key is required!']);
        exit;
    }

    $user_id = getUserId($api_key);
    if (!$user_id) {
        json_response(403, ['error' => 'Invalid API key!']);
        exit;
    }

    return $user_id;
}

function getMovieID($user_id, $id) {
    $pdo = connectdb();
    $query = "select movie_id from COMPLETED_WATCH_LIST where id = ? and user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute(([$id, $user_id]));
    $movie_id = $stmt->fetch(PDO::FETCH_ASSOC);;
    // return movie ID if found, null if not found
    return $movie_id ? $movie_id['movie_id'] : null;
}
?>

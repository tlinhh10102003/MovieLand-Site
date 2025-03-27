<?php 
require_once "library.php";
require_once "response.php";
require_once "helper_functions.php";

function handleUsersRouting($method, $endpoint) {
    if($method === "GET"){
        if (isset($endpoint[1]) && is_numeric($endpoint[1]) && isset($endpoint[2]) && $endpoint[2] === "stats"){
            getUserStat($endpoint[1]);
        }
    }
    elseif($method === "POST")
    {
        if(isset($endpoint[1]) && $endpoint[1] === "session")
        {
            userSession();
        }
    }
    else {
        json_response(405, ['error' => 'This method is not allowed. We are unable to respond to this request.']);
    }
}

function getUserStat($id) {
    $user_id = validateApiKey();

    $pdo = connectdb();
    $stats = []; // initialize empty array store watching stats

    // filter for most-watched movies if requested
    if (isset($_GET['most_watched']) && $_GET['most_watched'] === 'true') {
        $query = "select m.title, cwl.times_watched
                  from COMPLETED_WATCH_LIST cwl
                  join MOVIES m ON cwl.movie_id = m.id
                  where cwl.user_id = ?
                  order by cwl.times_watched DESC
                  limit 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        $stats['most_watched_movies'] = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    else {
        // get total movies watched
        // get total watched times
        // get avarage rating
        // get number of movies plan to watch
        $query = "SELECT 
        (SELECT COUNT(*) FROM COMPLETED_WATCH_LIST WHERE user_id = ?) AS total_movies_watched,
        (SELECT SUM(times_watched) FROM COMPLETED_WATCH_LIST WHERE user_id = ?) AS total_watched_times,
        (SELECT AVG(rating) FROM COMPLETED_WATCH_LIST WHERE user_id = ?) AS average_rating,
        (SELECT COUNT(*) FROM TO_WATCH_LIST WHERE user_id = ?) AS plan_to_watch;
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    }
    json_response(200, $stats);
}

function userSession(){
    // get data from json body
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    if (!isset($data['username']) || !isset($data['password'])) {
        json_response(400, ['error' => 'Username and password are required1']);
        return;
    }

    $pdo = connectdb();
    // fetch user from the database
    $query = "select id, password, api_key from USERS where username = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$data['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //user is found in the db and password is correct
    //compare entered password vs password of the user stored in the db
    if(!$user || !password_verify($data['password'], $user['password'])) {
        json_response(401, ["error" => "Invalid username or password"]);
        return;
    }
    else {
        $api_key = $user['api_key'];
        $user_id = $user['id']; 

        json_response(200, [
            'Your API key' => $api_key,
            'user_id' => $user_id // Include user_id in the response
        ]);
    }
}
?>

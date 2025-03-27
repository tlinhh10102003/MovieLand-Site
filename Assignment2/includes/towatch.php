<?php 
require_once "library.php";
require_once "response.php";
require_once "helper_functions.php";

function handleToWatchListRouting($method, $endpoint) {
    if($method === "GET"  && $endpoint[1] === "entries") {
        getAllEntries();
    }
    elseif($method === "POST"  && $endpoint[1] === "entries") {
        if(isset($endpoint[2]) && is_numeric($endpoint[2]) && isset($endpoint[3]) && $endpoint[3] === "watched")
        {
            markAsWatched($endpoint[2]);
        }
        else {
            addToWatchListEntry();
        }
    }
    elseif($method === "PUT"  && $endpoint[1] === "entries" && isset($endpoint[2]) && is_numeric($endpoint[2])) {
        updateToWatchListEntry($endpoint[2]);
    }
    elseif($method === "PATCH" && isset($endpoint[2]) && isset($endpoint[3]) && $endpoint[3] === "priority") {
        updateToWatchListPriority($endpoint[2]);
    }
    elseif($method === "DELETE" && isset($endpoint[2])) {
        deleteToWatchListEntry($endpoint[2]);
    }
    else {
        // other method
        json_response(405, ['error' => 'This method is not allowed. We are unable to respond to this request.']);
    }
}

// get method
function getAllEntries(){
    $user_id = validateApiKey();

    $pdo = connectdb();

    $priority = isset($_GET['priority']) ? (int)$_GET['priority'] : null;
// filter towatch by priority if it appears in the url
    if($priority) {
        $query = "SELECT twl.id, twl.user_id, twl.priority, m.id as movieID, m.title, m.overview, m.rating, twl.notes 
                  FROM TO_WATCH_LIST twl
                  JOIN MOVIES m ON m.id = twl.movie_id
                  WHERE user_id = ? AND twl.priority <= ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $priority]);
    } else { // else just return all movies in the to watch list
        $query = "SELECT twl.id, twl.user_id, twl.priority, m.id as movieID, m.title, m.overview, m.rating, twl.notes, m.poster 
                  FROM TO_WATCH_LIST twl
                  JOIN MOVIES m ON m.id = twl.movie_id
                  WHERE user_id = ?
                  ORDER BY twl.priority";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
    }
    
    $twl = $stmt->fetchAll(PDO::FETCH_ASSOC); // fetchAll to get all matching entries
    json_response(200, $twl); 
}

// post methiod
function addToWatchListEntry() {
    $user_id = validateApiKey();
    // get data from JSON body
    $data = json_decode(file_get_contents('php://input'), JSON_PRETTY_PRINT);
    if (!$data) {
        $data = $_POST;
    }

    if (!isset($data['movie_id']) || !isset($data['priority']) || !isset($data['notes'])) {
        json_response(400, ['error' => 'Add to Watch List: Missing required fields']);
        return;
    }

    $pdo = connectdb();

    // Check if the movie is already in the user's watchlist
    $checkWatchList = "SELECT COUNT(*) FROM TO_WATCH_LIST WHERE user_id = ? AND movie_id = ?";
    $stmt = $pdo->prepare($checkWatchList);
    $stmt->execute([$user_id, $data['movie_id']]);
    $watchlist_exist = $stmt->fetchColumn();

    $checkCompletedList = "SELECT COUNT(*) FROM COMPLETED_WATCH_LIST WHERE user_id = ? AND movie_id = ?";
    $stmt = $pdo->prepare($checkCompletedList);
    $stmt->execute([$user_id, $data['movie_id']]);
    $completedwatch_list = $stmt->fetchColumn();

    if ($watchlist_exist || $completedwatch_list) {
        json_response(409, ['error' => 'Movie is already in your list']);
        return;
    }

    $query = "insert into TO_WATCH_LIST (user_id, movie_id, priority, notes) values (?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $data['movie_id'], $data['priority'], $data['notes']]);

    json_response(201, ['message' => 'Entry added to watchlist']);
}

function markAsWatched($id) {
    // Retrieve API key and validate it
    $user_id = validateApiKey();

    // Get the request data from the JSON body (like notes and rating)
    $data = json_decode(file_get_contents('php://input'), true); 
    if (!$data) {
        $data = $_POST; 
    }

    // Default the note to an empty string if not provided
    $note = isset($data['note']) ? trim($data['note']) : "";
    $rating = isset($data['rating']) ? $data['rating'] : null;

    $pdo = connectdb();
    try {
        $pdo->beginTransaction(); // Start a transaction to ensure atomicity

        // Check if the movie exists in the "To Watch" list for the current user
        $checkQuery = "SELECT * FROM TO_WATCH_LIST WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($checkQuery);
        $stmt->execute([$id, $user_id]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        // if (!$entry) {
        //     // If the movie isn't in the "To Watch" list, send an error
        //     json_response(404, ['error' => 'Movie not found in To Watch List']);
        //     return;
        // }

        // Remove the movie from the "To Watch" list
        $deleteQuery = "DELETE FROM TO_WATCH_LIST WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($deleteQuery);
        $stmt->execute([$id, $user_id]);

        // Insert the movie into the "Completed List"
        $insertQuery = "INSERT INTO COMPLETED_WATCH_LIST (user_id, movie_id, notes, rating, date_last_watched) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute([$user_id, $entry['movie_id'], $note, $rating]);

        // Commit the transaction if everything is successful
        $pdo->commit();

        // Send a successful response
        json_response(200, ['message' => 'Movie marked as watched successfully']);
    } catch (Exception $e) {
        // Rollback the transaction if an error occurred
        $pdo->rollBack();
        json_response(500, ['error' => 'Failed to mark entry as watched', 'details' => $e->getMessage()]);
    }
}


function updateToWatchListEntry($id) {
    $user_id = validateApiKey();

    $pdo = connectdb();
    
    // Check if the entry exists
    $queryCheck = "SELECT * FROM TO_WATCH_LIST WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($queryCheck);
    $stmt->execute([$id, $user_id]);
    $exists = $stmt->fetchColumn();

    // Get data from the JSON body
    $data = json_decode(file_get_contents('php://input'), JSON_PRETTY_PRINT);
    if (!$data) {
        $data = $_POST;
    }

    if (!isset($data['movie_id']) || !isset($data['priority'])) {
        json_response(400, ['error' => 'Update Watch List: Missing required fields']);
        return;
    }

    $notes = isset($data['notes']) ? $data['notes'] : "";

    // If entry exists, update it
    if($exists) {
        $query = "UPDATE TO_WATCH_LIST 
                  SET movie_id = ?, priority = ?, notes = ?
                  WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$data['movie_id'], $data['priority'], $notes, $id, $user_id]);
        json_response(200, ['message' => 'Entry updated successfully']);
    } else {
        // If entry doesn't exist, create a new entry
        $query = "INSERT INTO TO_WATCH_LIST (user_id, movie_id, priority, notes) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $data['movie_id'], $data['priority'], $notes]);
        json_response(201, ['message' => 'Entry added successfully']);
    }
}

// patch method
function updateToWatchListPriority($id) {
    $user_id = validateApiKey();

    // get data from JSON body
    $data = json_decode(file_get_contents('php://input'), JSON_PRETTY_PRINT);
    if (!$data) {
        $data = $_POST;
    }

    if (!isset($data['priority'])) {
        json_response(400, ['error' => 'Update Watch List Priority: Missing required fields']);
        return;
    }

    // check if entry exists
    $pdo = connectdb();
    $checkQuery = "select * from TO_WATCH_LIST where id = ? and user_id = ?";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute([$id, $user_id]);
    $exists = $stmt->fetchColumn();

    // if exist then update priority
    if($exists) {
        $query = "update TO_WATCH_LIST set priority = ? where user_id = ? and id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$data['priority'], $user_id, $id]);
        json_response(200, ['message' => 'Entry priority updated successfully']);
    }
    // no entry exists then error response
    else {
        json_response(404, ['error' => 'Entry not found in the database']);
    }
}

// delete method
function deleteToWatchListEntry($id) {
    $user_id = validateApiKey();

    // get data from JSON body
    $data = json_decode(file_get_contents('php://input'), JSON_PRETTY_PRINT);
    if (!$data) {
        $data = $_POST;
    }

    if (!isset($data['movie_id'])) {
        json_response(400, ['error' => 'Delete Watch List: Missing required fields']);
        return;
    }

    // check if entry exists
    $pdo = connectdb();
    $checkQuery = "select * from TO_WATCH_LIST where id = ? and user_id = ?";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute([$id, $user_id]);
    $exists = $stmt->fetchColumn();

    // if exist then delete appropriate movie
    if($exists) {
        $query = "delete from TO_WATCH_LIST where movie_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$data['movie_id']]);
        json_response(200, ['message' => 'Movie deleted successfully']);
    }
    // // no entry exists then error response
    // else {
    //     json_response(404, ['error' => 'Entry not found in the database']);
    // }
}

?>

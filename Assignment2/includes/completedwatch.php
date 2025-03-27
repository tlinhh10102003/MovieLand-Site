<?php 
require_once "library.php";
require_once "response.php";
require_once "helper_functions.php";

function handleCompletedWatchListRouting($method, $endpoint) {
    if ($method === "GET") {
        if(isset($endpoint[1]) && $endpoint[1] === 'entries') {
            if(isset($endpoint[2]) && is_numeric($endpoint[2])) {
                if(isset($endpoint[3]) && $endpoint[3] === "times-watched") {
                    getCompletedWatchTimesWatched($endpoint[2]);
                }
                elseif(isset($endpoint[3]) && $endpoint[3] === "rating") {
                    getCompletedWatchRating($endpoint[2]);
                }
            }
            else {
                getAllCompletedWatchEntries();
            }
        }
    }
    elseif($method === "POST") {
        if (isset($endpoint[1]) && $endpoint[1] === 'entries') {
            addCompletedWatchListEntry();
        } else {
            json_response(404, ['error' => 'Invalid endpoint']);
        }
    }
    elseif($method === "PATCH") {
        // /completedwatchlist/entries/{id}
        if(isset($endpoint[1]) && $endpoint[1] === 'entries' && isset($endpoint[2]) && is_numeric($endpoint[2])) {
            // /completedwatchlist/entries/{id}/times-watched
            if(isset($endpoint[3]) && $endpoint[3] === "times-watched") {
                updateCompletedWatchTimesWatched($endpoint[2]);
            }
            // /completedwatchlist/entries/{id}/rating
            elseif(isset($endpoint[3]) && $endpoint[3] === "rating") {
                updateCompletedWatchRating($endpoint[2]);
            }
        }
    }
    elseif($method === "DELETE" && isset($endpoint[1]) && $endpoint[1] === 'entries' && isset($endpoint[2]) && is_numeric($endpoint[2])) {
        deleteEntryFromCompletedWatch($endpoint[2]);
    }
    else {
        json_response(405, ['error' => 'This method is not allowed. We are unable to respond to this request.']);
    }
}

//GET /completedwatchlist/entries
function getAllCompletedWatchEntries(){
    $user_id = validateApiKey();

    $pdo = connectdb();
    // filter minimum rating filter if max_rating is in the url
    if (isset($_GET['max_rating']) && $_GET['max_rating'] === 'true') {
        $query = "select movie_id, title, MAX(COMPLETED_WATCH_LIST.rating) as max_rating from COMPLETED_WATCH_LIST 
                join MOVIES on MOVIES.id = COMPLETED_WATCH_LIST.movie_id where user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
    }
    else {
        $query = "select cwl.id, 
                 cwl.user_id, 
                 cwl.rating as userRating, 
                 m.id as movieID, 
                 m.title, 
                 m.overview, 
                 m.rating, 
                 m.poster, 
                 cwl.notes, 
                 cwl.times_watched, 
                 DATE(cwl.date_last_watched) as date_last_watched
          from COMPLETED_WATCH_LIST cwl 
          join MOVIES m ON m.id = cwl.movie_id 
          where user_id = ?
          ORDER BY 
          DATE(cwl.date_last_watched) DESC, 
          cwl.date_last_watched DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
    }
    $cwl = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json_response(200, $cwl);
}

//GET /completedwatchlist/entries/{id}/times-watched
function getCompletedWatchTimesWatched($id){
    $user_id = validateApiKey();

    $pdo = connectdb();
    $query = "select times_watched from COMPLETED_WATCH_LIST where id = ? and user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id, $user_id]);
    $times_watched = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$times_watched) {
        json_response(404, ['error' => 'Entry not found in the database']);
    }
    json_response(200, $times_watched);
}

//GET /completedwatchlist/entries/{id}/rating
function getCompletedWatchRating($id){
    $user_id = validateApiKey();

    $pdo = connectdb();
    $query = "select rating from COMPLETED_WATCH_LIST where id = ? and user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id, $user_id]);
    $rating = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$rating) {
        json_response(404, ['error' => 'Entry not found in the database']);
    }
    json_response(200, $rating);
}

function addCompletedWatchListEntry() {
    $user_id = validateApiKey();

    // get data from JSON body
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    // verify required data to insert in table
    if (!isset($data['movie_id']) || !isset($data['rating']) || !isset($data['notes']) || !isset($data['date_watched']) || !isset($data['times_watched'])) {
        json_response(400, ['error' => 'Completed: Missing required fields']);
        return;
    }

    $pdo = connectdb();

    try {
        // use transaction to complete multiple taks: insert data and update rating in movies
        $pdo->beginTransaction();
        $query = "insert into COMPLETED_WATCH_LIST (user_id, movie_id, rating, notes, date_watched, date_last_watched, times_watched) 
                  VALUES (?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $data['movie_id'], $data['rating'], $data['notes'], $data['date_watched'], $data['times_watched']]);
    
        // update rating
        recomputeAvgRating($pdo, $data['movie_id'], $data['rating']);
        
        // commit if everything went well
        $pdo->commit();
        json_response(201, ['message' => 'New entry added to completed watchlist successfully']);
    } catch (Exception $e) {
        // rollbakc  if an error occurred
        $pdo->rollBack();
        json_response(500, ['error' => 'Failed to add entry', 'details' => $e->getMessage()]);
    }
}


//PATCH /completedwatchlist/entries/{id}/rating
function updateCompletedWatchRating($id){
    $user_id = validateApiKey();
    if ($user_id) {
        $movie_id = getMovieID($user_id, $id);
    }

    // get data from JSON body
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    if (!isset($data['new_rating']) || !is_numeric($data['new_rating'])) {
        json_response(400, ['error' => 'Invalid or missing new_rating field']);
        return;
    }    

    $pdo = connectdb();

    try {
        // use transaction to complete multiple taks
        $pdo->beginTransaction();
        //check if entry exists
        $query = "select rating from COMPLETED_WATCH_LIST where id = ? and user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id, $user_id]);
        $old_rating = $stmt->fetch(PDO::FETCH_ASSOC);

        // if no entry exists
        if (!$old_rating) {
            json_response(404, ['error' => 'Entry not found']);
            return;
        }
        $updateQuery = "update COMPLETED_WATCH_LIST set rating = ? where id = ? and user_id = ?";
        $stmt = $pdo ->prepare($updateQuery);
        $stmt->execute([$data['new_rating'], $id, $user_id]);

        // update rating in movies table
        recomputeAvgRatingAfterUpdate($pdo, $movie_id, $old_rating['rating'], $data['new_rating']);
        
        // commit if everything went well
        $pdo->commit();
        json_response(200, ['message' => 'Rating updated successfully', 'rating' => $data['new_rating']]);

    } catch (Exception $e) {
        // rollback if an error occurred
        $pdo->rollBack();
        json_response(500, ['error' => 'Failed to updating rating of given entry', 'details' => $e->getMessage()]);
    }
}

// PATCH /completedwatchlist/entries/{id}/times-watched
function updateCompletedWatchTimesWatched($id){
    $user_id = validateApiKey();
    if ($user_id) {
        $movie_id = getMovieID($user_id, $id);
    }

    $pdo = connectdb();

    try {
        // use transaction to complete multiple task
        $pdo->beginTransaction();
        //check if entry exists
        $query = "select times_watched from COMPLETED_WATCH_LIST where id = ? and user_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id, $user_id]);
        $times_watched = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$times_watched) {
            json_response(400, ['error' => 'Entry not found in the database']);
        }

        $updated_times_watched = $times_watched['times_watched'] + 1;

        // update complted watch list table
        $updateQuery = "update COMPLETED_WATCH_LIST set times_watched = ?, date_last_watched = NOW() 
                        where id = ? and user_id = ?";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([$updated_times_watched, $id, $user_id]);

        $pdo->commit();
        json_response(200, ['message' => '"times_watched" and "date_last_watched" updated successfully', 'times_watched' => $updated_times_watched]);
    }
    catch(Exception $e) {
        $pdo->rollBack();
        json_response(500, ['error' => 'Failed to update times watched', 'details' => $e->getMessage()]);
    }
}

// DELETE /completedwatchlist /entries/{id}
function deleteEntryFromCompletedWatch($id) {
    $user_id = validateApiKey();

    // get data from json body
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    if(!isset($data['movie_id'])) {
        json_response(400, ['error' => 'Missing required field']);
        return;
    }
    $pdo = connectdb();
    // check if entry exists
    $pdo = connectdb();
    $checkQuery = "select * from COMPLETED_WATCH_LIST where id = ? and user_id = ?";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute([$id, $user_id]);
    $exists = $stmt->fetchColumn();

    if($exists) {
        // delete
        $query = "delete from COMPLETED_WATCH_LIST where movie_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$data['movie_id']]);
        json_response(200, ['message' => 'Movie deleted successfully']);
    }
    elseif(!$exists) {
        json_response(400, ['error' => 'Entry not found in the database']);
    }
}

//recompute rating
function recomputeAvgRating($pdo, $movie_id, $new_rating) {
    $query = "select rating, rating_count from MOVIES where id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$movie_id]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);

    // if the movie exists
    if ($movie) {
        $old_rating = $movie['rating'];
        $old_rating_count = $movie['rating_count'];

        if ($old_rating_count === 0) {
            $new_avg_rating = $new_rating; 
            $new_rating_count = 1; 
        } else {
            $new_rating_count = $old_rating_count + 1;
            $new_avg_rating = (($old_rating * $old_rating_count) + $new_rating) / $new_rating_count;
        }

        // update the movies table
        $updateQuery = "update MOVIES set rating = ?, rating_count = ? where id = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$new_avg_rating, $new_rating_count, $movie_id]);
    }
}

function recomputeAvgRatingAfterUpdate($pdo, $movie_id, $old_rating, $new_rating) {
    $query = "select rating, rating_count from MOVIES WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$movie_id]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    if($movie) {
        $old_avg_rating = $movie['rating'];
        $count = $movie['rating_count'];

        if ($count > 1) {
            $new_avg_rating = (($old_avg_rating * $count) - $old_rating + $new_rating) / $count;
        } else {
            $new_avg_rating = $new_rating;  // if no rating exists before new rating = new avg rating
        }

        $updateQuery = "update MOVIES set rating = ?, rating_count = ? where id = ?";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([$new_avg_rating, $count, $movie_id]);
    }
}
?>

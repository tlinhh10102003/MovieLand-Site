<?php 
require_once "library.php";
require_once "response.php";
require_once "helper_functions.php";

function handleMoviesRouting($method, $endpoint) {
    if ($method === "GET") {
        // GET & /movies/{id}
        if (isset($endpoint[1]) && is_numeric($endpoint[1])) { 
            // GET & /movies/{id}/rating
            if(isset($endpoint[2]) && $endpoint[2] === "rating") {
                // retrieve rating detail of a specific movie
                getMovieRating($endpoint[1]);
            }
            else {
                // retrieve detail for specific movie with given id
                getMovie($endpoint[1]);
            }
        }
        // GET & /movies/search
        elseif(isset($endpoint[1]) && $endpoint[1] ==="search") {
            searchMovies();
        }
        // GET & /movies/ - retrieve all movies
        else {
            getAllMovies();
        }
    } else {
        // if method is not GET
        json_response(405, ['error' => 'This method is not allowed. We are unable to respond to this request.']);
    }
}

function getAllMovies() {
    $pdo = connectdb();

    $title = isset($_GET['title']) ? $_GET['title'] : null;
    if($title) {
        $query = "select id, title, overview, rating, poster, release_date from MOVIES where title like ? LIMIT 1000";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['%' . $title . '%']);
    }
    else {
        $query = "select id, title, overview, rating, poster, release_date from MOVIES LIMIT 1000";
        $stmt = $pdo->query($query);
    }    
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json_response(200, $movies);
}

function getMovie($id) {
    $pdo = connectdb();
    $query = "select * from MOVIES where id = ?"; 
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC); 

    // if movie with given id exists
    if ($movie) {
        json_response(200, $movie);
    } else {
        json_response(404, ['error' => 'Movie not found']);
    }
}

function getMovieRating($id) {
    $pdo = connectdb();
    $query = "select rating from MOVIES where id = ?"; 
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $rating = $stmt->fetch(PDO::FETCH_ASSOC); 

    if ($rating) {
        json_response(200, ['rating' => $rating['rating']]);
    } else {
        json_response(404, ['error' => 'Movie not found']);
    }
}

function searchMovies() {
    // $api_key = getApiKey();
    // if (!$api_key) {
    //     json_response(403, ['error' => 'An API key is required']);
    //     return;
    // }

    // $user_id = getUserId($api_key);
    // if (!$user_id) {
    //     json_response(403, ['error' => 'Invalid API key']);
    //     return;
    // }

    // Get search query from the request
    $query = isset($_GET['q']) ? $_GET['q'] : '';
    error_log("Received query: " . $query);
    $pdo = connectdb();
    $stmt = $pdo->prepare("SELECT * FROM MOVIES WHERE title LIKE ? LIMIT 30");
    $stmt->execute(["%$query%"]);
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($movies) {
        json_response(200, ['results' => $movies]);
    } else {
        json_response(404, ['message' => 'No movies found']);
    }
}
?>

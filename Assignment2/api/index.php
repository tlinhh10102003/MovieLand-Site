<?php 

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: *");
    // header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-KEY");
    header("Access-Control-Allow-Headers: *");
    
    http_response_code(200); 
    exit();
}

require_once "../includes/library.php";
require_once "../includes/response.php";
require_once "../includes/movies.php";
require_once "../includes/towatch.php";
require_once "../includes/completedwatch.php";
require_once "../includes/users.php";

//Get the request method from the server array
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$uri = parse_url($uri);

// Define the consistent BASE URL for your API
define('__BASE__', '/~litran/3430/assn/assn2-tlinhh10102003/api/');
$endpoint = explode('/', trim(str_replace(__BASE__, '', $uri['path']), '/'));

// Check if the endpoint is set correctly
if (count($endpoint) < 1 || empty($endpoint[0])) {
    json_response(404, ['error' => 'Endpoint not found']);
    exit;
}

switch ($endpoint[0]) {
    case 'movies':
        handleMoviesRouting($method, $endpoint);
        break;
    case 'towatchlist':
        handleToWatchListRouting($method, $endpoint);
        break;
    case 'completedwatchlist':
        handleCompletedWatchListRouting($method, $endpoint);
        break;
    case 'users':
        handleUsersRouting($method, $endpoint);
        break;
    default:
        json_response(404, ['error' => 'Endpoint not found']);
}
?>

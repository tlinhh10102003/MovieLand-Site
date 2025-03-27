<?php 
function json_response($statusCode, $response_data = []) {
    // set HTTP status code
    http_response_code($statusCode);

    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");  
    header("Access-Control-Allow-Methods: *");
    header("Access-Control-Allow-Headers: *");

    // Encode response data
    $json_encode_data = json_encode($response_data, JSON_PRETTY_PRINT);
    header('Content-Length: ' . strlen($json_encode_data));
    echo $json_encode_data;
    exit();
}
?>

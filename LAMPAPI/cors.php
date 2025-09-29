<?php
// CORS headers for SwaggerHub + browsers (to allow the credentials)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Max-Age: 86400");

// handle preflight 
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // no content
    exit();
}

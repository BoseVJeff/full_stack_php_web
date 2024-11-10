<?php
declare (strict_types = 1);

require_once "../src/utils/utils.php";
require_once "../src/api/db.php";

// echoln("Hello from ". $_SERVER['REQUEST_URI']);
// echoln("This will attempt to access the file named " . basename($_SERVER["REQUEST_URI"]));

// The filename is the last uri segment
function getFilename(string $url): string
{
    $uri_path     = parse_url($url, PHP_URL_PATH);
    $uri_segments = explode('/', $uri_path);
    return $uri_segments[count($uri_segments) - 1];
}

$method      = $_SERVER["REQUEST_METHOD"];
$uploads_dir = "../uploads/";
// $filename=basename($_SERVER["REQUEST_URI"]);
$filename    = getFilename($_SERVER['REQUEST_URI']);
$upload_path = $uploads_dir . $filename;
$range       = null;

// $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// $uri_segments = explode('/', $uri_path);
// print_r($uri_segments[count($uri_segments)-1]);

function isAllowed(string $file): bool
{
    // TODO: Implement this
    if (Utils::checkVar($_SERVER['PHP_AUTH_USER'], 'test') & Utils::checkVar($_SERVER['PHP_AUTH_PW'], 'test')) {
        return true;
    } else {
        return false;
    }
}

function getOriginalName(string $name): string
{
    // TODO: Implement this
    return "login data.png";
}

$file_exists = isset($filename) & $filename !== "" & file_exists($upload_path);

switch ($method) {
    case 'GET':
        if ($file_exists) {
            if (isAllowed($filename)) {
                if ($range == null) {
                    http_response_code(200);
                    header('Content-Disposition: attachment; filename="' . getOriginalName($filename) . '"');
                    readfile($upload_path);
                } else {
                    // TODO: Implement this
                }
            } else {
                http_response_code(401);
                header('WWW-Authenticate: Bearer, Basic');
                echo "Client is not allowed to access file";
            }
        } else {
            http_response_code(404);
            echo "File '" . $filename . "' does not exist on this server";
        }
        break;

    case 'POST':
        http_response_code(405);
        echo "Unimplemented!";

    default:
        http_response_code(405);
        break;
}

<?php
declare (strict_types = 1);

require_once "../src/utils/utils.php";
require_once "../src/api/db.php";
require_once "../src/api/base.php";

// echoln("Hello from ". $_SERVER['REQUEST_URI']);
// echoln("This will attempt to access the file named " . basename($_SERVER["REQUEST_URI"]));

$user = null;
if (isset($_SERVER['PHP_AUTH_USER'])) {
    $username = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];

    $user = User::getUser($username, $password);
} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth = explode(" ", trim($_SERVER['HTTP_AUTHORIZATION']));
    if ($auth[0] != "Bearer") {
        http_response_code(400);
        echo json_encode(["error" => "Bad Auth Header!"]);
        exit();
    }
    $token = $auth[1];
    $user  = User::getTokenUser($token);

} else {
    http_response_code(401);
    header('WWW-Authenticate: Basic, Bearer');
    echo json_encode(["error" => "Credentials Required!"]);
    exit();
}

if ($user == null) {
    http_response_code(401);
    header('WWW-Authenticate: Basic, Bearer');
    echo json_encode(["error" => "Invalid Credentials!"]);
    exit();
}

// The filename is the last uri segment
function getFilename(string $url): string
{
    $uri_path     = parse_url($url, PHP_URL_PATH);
    $uri_segments = explode('/', $uri_path);
    return $uri_segments[count($uri_segments) - 1];
}

$method      = $_SERVER["REQUEST_METHOD"];
$uploads_dir = "api/files/" . $user->name . "/";
// $filename=basename($_SERVER["REQUEST_URI"]);
$filename    = getFilename($_SERVER['REQUEST_URI']);
$upload_path = $uploads_dir . $filename;
$range       = null;

// $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// $uri_segments = explode('/', $uri_path);
// print_r($uri_segments[count($uri_segments)-1]);

function isAllowed(string $file): bool
{
    if (isset($_SERVER['PHP_AUTH_USER'])) {
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        $user = User::getUser($username, $password);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = explode(" ", trim($_SERVER['HTTP_AUTHORIZATION']));
        if ($auth[0] != "Bearer") {
            http_response_code(400);
            echo json_encode(["error" => "Bad Auth Header!"]);
            exit();
        }
        $token = $auth[1];
        $user  = User::getTokenUser($token);

    } else {
        http_response_code(401);
        header('WWW-Authenticate: Basic, Bearer');
        echo json_encode(["error" => "Credentials Required!"]);
        exit();
    }

    if ($user == null) {
        http_response_code(401);
        header('WWW-Authenticate: Basic, Bearer');
        echo json_encode(["error" => "Invalid Credentials!"]);
        exit();
    }

    if ($user->getAccessLevel($file) >= Permission::read_only()->level) {
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

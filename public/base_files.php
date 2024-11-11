<?php
declare (strict_types = 1);

require_once "../src/utils/utils.php";
require_once "../src/api/db.php";
require_once "../src/api/user_file.php";
require_once "../src/api/base.php";

function authenticateUser(): ?User
{
    if (isset($_SERVER['PHP_AUTH_USER'])) {
        return User::getUser($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
    }

    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = explode(" ", trim($_SERVER['HTTP_AUTHORIZATION']));
        if ($auth[0] !== "Bearer") {
            http_response_code(400);
            echo json_encode(["error" => "Bad Auth Header!"]);
            exit();
        }
        return User::getTokenUser($auth[1]);
    }

    http_response_code(401);
    header('WWW-Authenticate: Basic, Bearer');
    echo json_encode(["error" => "Credentials Required!"]);
    exit();
}

function sendFile(string $filepath, UserFile $file, ?string $rangeHeader = null): void
{
    $size         = $file->getSize();
    $mime         = $file->getMimeType();
    $originalName = $file->getOriginalName();

    if ($rangeHeader === null) {
        // Send entire file
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $originalName . '"');
        header('Content-Length: ' . $size);
        header('Accept-Ranges: bytes');
        readfile($filepath);
        return;
    }

    // Parse range header
    if (! preg_match('/bytes=\d*-\d*/', $rangeHeader)) {
        http_response_code(416);
        header("Content-Range: bytes */$size");
        exit();
    }

    $range = str_replace('bytes=', '', $rangeHeader);
    $range = explode('-', $range);
    $start = empty($range[0]) ? 0 : intval($range[0]);
    $end   = empty($range[1]) ? ($size - 1) : intval($range[1]);

    if ($start >= $size || $end >= $size || $start > $end) {
        http_response_code(416);
        header("Content-Range: bytes */$size");
        exit();
    }

    $length = $end - $start + 1;

    http_response_code(206);
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $originalName . '"');
    header('Content-Length: ' . $length);
    header('Accept-Ranges: bytes');
    header("Content-Range: bytes $start-$end/$size");

    $fp = fopen($filepath, 'rb');
    fseek($fp, $start);
    $buffer = 8192;
    while ($length > 0 && ! feof($fp)) {
        $read = min($buffer, $length);
        echo fread($fp, $read);
        $length -= $read;
    }
    fclose($fp);
}

function getFilename(string $url): string
{
    $uri_path     = parse_url($url, PHP_URL_PATH);
    $uri_segments = explode('/', $uri_path);
    return $uri_segments[count($uri_segments) - 1];
}

function isAllowed(User $user, string $file): bool
{
    return $user->getAccessLevel($file) >= Permission::read_only()->level;
}

// Main execution
$user = authenticateUser();
if ($user === null) {
    http_response_code(401);
    header('WWW-Authenticate: Basic, Bearer');
    echo json_encode(["error" => "Invalid Credentials!"]);
    exit();
}

$method      = $_SERVER["REQUEST_METHOD"];
$uploads_dir = "api/files/" . $user->name . "/";
$filename    = getFilename($_SERVER['REQUEST_URI']);
$upload_path = $uploads_dir . $filename;

$file = $user->getFile($filename);

// Prevent directory traversal attacks
if (strpos(realpath($upload_path), realpath($uploads_dir)) !== 0) {
    http_response_code(403);
    echo json_encode(["error" => "Access Denied"]);
    exit();
}

$file_exists = ! empty($filename) && file_exists($upload_path);

switch ($method) {
    case 'GET':
        if ($file_exists) {
            if (isAllowed($user, $filename)) {
                $range = $_SERVER['HTTP_RANGE'] ?? null;
                sendFile($upload_path, $file, $range);
            } else {
                http_response_code(403);
                echo json_encode(["error" => "Access Denied"]);
            }
        } else {
            http_response_code(404);
            echo json_encode(["error" => "File '$filename' not found"]);
        }
        break;

    case 'POST':
        http_response_code(405);
        echo json_encode(["error" => "Method not implemented"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

// ---------------------------------------
// declare (strict_types = 1);

// require_once "../src/utils/utils.php";
// require_once "../src/api/db.php";
// require_once "../src/api/base.php";

// // echoln("Hello from ". $_SERVER['REQUEST_URI']);
// // echoln("This will attempt to access the file named " . basename($_SERVER["REQUEST_URI"]));

// $user = null;
// if (isset($_SERVER['PHP_AUTH_USER'])) {
//     $username = $_SERVER['PHP_AUTH_USER'];
//     $password = $_SERVER['PHP_AUTH_PW'];

//     $user = User::getUser($username, $password);
// } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
//     $auth = explode(" ", trim($_SERVER['HTTP_AUTHORIZATION']));
//     if ($auth[0] != "Bearer") {
//         http_response_code(400);
//         echo json_encode(["error" => "Bad Auth Header!"]);
//         exit();
//     }
//     $token = $auth[1];
//     $user  = User::getTokenUser($token);

// } else {
//     http_response_code(401);
//     header('WWW-Authenticate: Basic, Bearer');
//     echo json_encode(["error" => "Credentials Required!"]);
//     exit();
// }

// if ($user == null) {
//     http_response_code(401);
//     header('WWW-Authenticate: Basic, Bearer');
//     echo json_encode(["error" => "Invalid Credentials!"]);
//     exit();
// }

// // The filename is the last uri segment
// function getFilename(string $url): string
// {
//     $uri_path     = parse_url($url, PHP_URL_PATH);
//     $uri_segments = explode('/', $uri_path);
//     return $uri_segments[count($uri_segments) - 1];
// }

// $method      = $_SERVER["REQUEST_METHOD"];
// $uploads_dir = "api/files/" . $user->name . "/";
// // $filename=basename($_SERVER["REQUEST_URI"]);
// $filename    = getFilename($_SERVER['REQUEST_URI']);
// $upload_path = $uploads_dir . $filename;
// $range       = null;

// // $uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// // $uri_segments = explode('/', $uri_path);
// // print_r($uri_segments[count($uri_segments)-1]);

// function isAllowed(string $file): bool
// {
//     if (isset($_SERVER['PHP_AUTH_USER'])) {
//         $username = $_SERVER['PHP_AUTH_USER'];
//         $password = $_SERVER['PHP_AUTH_PW'];

//         $user = User::getUser($username, $password);
//     } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
//         $auth = explode(" ", trim($_SERVER['HTTP_AUTHORIZATION']));
//         if ($auth[0] != "Bearer") {
//             http_response_code(400);
//             echo json_encode(["error" => "Bad Auth Header!"]);
//             exit();
//         }
//         $token = $auth[1];
//         $user  = User::getTokenUser($token);

//     } else {
//         http_response_code(401);
//         header('WWW-Authenticate: Basic, Bearer');
//         echo json_encode(["error" => "Credentials Required!"]);
//         exit();
//     }

//     if ($user == null) {
//         http_response_code(401);
//         header('WWW-Authenticate: Basic, Bearer');
//         echo json_encode(["error" => "Invalid Credentials!"]);
//         exit();
//     }

//     if ($user->getAccessLevel($file) >= Permission::read_only()->level) {
//         return true;
//     } else {
//         return false;
//     }
// }

// function getOriginalName(string $name): string
// {
//     // TODO: Implement this
//     return "login data.png";
// }

// $file_exists = isset($filename) & $filename !== "" & file_exists($upload_path);

// switch ($method) {
//     case 'GET':
//         if ($file_exists) {
//             if (isAllowed($filename)) {
//                 if ($range == null) {
//                     http_response_code(200);
//                     header('Content-Disposition: attachment; filename="' . getOriginalName($filename) . '"');
//                     readfile($upload_path);
//                 } else {
//                     // TODO: Implement this
//                 }
//             } else {
//                 http_response_code(401);
//                 header('WWW-Authenticate: Bearer, Basic');
//                 echo "Client is not allowed to access file";
//             }
//         } else {
//             http_response_code(404);
//             echo "File '" . $filename . "' does not exist on this server";
//         }
//         break;

//     case 'POST':
//         http_response_code(405);
//         echo "Unimplemented!";

//     default:
//         http_response_code(405);
//         break;
// }

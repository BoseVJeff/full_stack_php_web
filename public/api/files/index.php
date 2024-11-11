<?php
declare (strict_types = 1);

require_once dirname($_SERVER['DOCUMENT_ROOT']) . "/src/api/base.php";
require_once dirname($_SERVER['DOCUMENT_ROOT']) . "/src/api/user.php";
require_once dirname($_SERVER['DOCUMENT_ROOT']) . "/src/utils/utils.php";

/**
 * Handles /api/files
 */

$http_method = $_SERVER['REQUEST_METHOD'];

header("Content-Type: application/json");

// Only POST is allowed

if ($http_method == "POST") {
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

    $files = [];

    try {
        // echo json_encode($_FILES['file']);
        if (isset($_FILES['file'][0])) {
            // Multiple Files
            for ($i = 0; $i < count($_FILES['file']); $i++) {
                // $file = $_FILES['file'][$i];

                $files[] = $user->addFile($_FILES['file']['tmp_name'][$i], $_FILES['file']['name'][$i], $_FILES['file']['size'][$i], $_FILES['file']['type'][$i]);
            }
        } else {
            // Single file
            // $file = $_FILES['file'];

            $files[] = $user->addFile($_FILES['file']['tmp_name'], $_FILES['file']['name'], $_FILES['file']['size'], $_FILES['file']['type']);
        }

        http_response_code(200);
        echo json_encode(["files" => $files]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error uploading files", "server" => $_SERVER]);
    }
} elseif ($http_method == "GET") {
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

    $files = $user->getAllFiles();

    $file_id_list = [];

    foreach ($files as $file) {
        $file_id_list[] = $file->getName();
    }

    http_response_code(200);
    echo json_encode(["file" => $file_id_list]);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Only POST is supported!"]);
}

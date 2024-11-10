<?php
declare (strict_types = 1);

require_once dirname($_SERVER['DOCUMENT_ROOT']) . "/src/api/base.php";
require_once dirname($_SERVER['DOCUMENT_ROOT']) . "/src/api/user.php";
require_once dirname($_SERVER['DOCUMENT_ROOT']) . "/src/utils/utils.php";

/**
 * Handles /api/user/token/
 */

$http_method = $_SERVER['REQUEST_METHOD'];

header("Content-Type: application/json");

// Only DELETE is allowed

if ($http_method == "DELETE") {
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

    $user->deleteUser();
    http_response_code(200);
    echo json_encode(["status" => "success"]);

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
        echo json_encode(["error" => "Invalid Credentials!"]);
        exit();
    }

    http_response_code(200);
    echo json_encode([
        "name"  => $user->name,
        "email" => $user->email,
    ]);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Only DELETE is supported!"]);
}

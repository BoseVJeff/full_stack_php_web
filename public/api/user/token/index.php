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

// Only POST is allowed

if ($http_method == "POST") {
    if (! isset($_SERVER['PHP_AUTH_USER'])) {
        http_response_code(401);
        header('WWW-Authenticate: Basic');
        header('HTTP/1.0 401 Unauthorized');
        echo json_encode(["error" => "Credentials Required!"]);
    } else {
        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        $user = User::getUser($username, $password);

        if ($user == null) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid Credentials!"]);
        } else {
            $label = isset($_POST['label']) ? $_POST['label'] : null;
            try {
                $token = $user->createToken($label);

                if ($token == null) {
                    // Highly unlikely, keeping the lowercase c for identification
                    http_response_code(400);
                    echo json_encode(["error" => "Invalid Credentials!"]);
                } else {
                    http_response_code(200);
                    http_response_code(400);
                    echo json_encode(["token" => $token]);
                }
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["error" => "Error generating token!"]);
            }
        }
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
        echo json_encode(["error" => "Invalid Credentials!"]);
        exit();
    }
    http_response_code(200);
    echo json_encode(["tokens" => $user->getTokens()]);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Only GET,POST is supported!"]);
}

<?php
declare (strict_types = 1);

require_once dirname($_SERVER['DOCUMENT_ROOT']) . "/src/api/base.php";
require_once dirname($_SERVER['DOCUMENT_ROOT']) . "/src/api/user.php";
require_once dirname($_SERVER['DOCUMENT_ROOT']) . "/src/utils/utils.php";

/**
 * Handles /api/user/create/
 */

header("Content-Type: application/json");

$http_method = $_SERVER['REQUEST_METHOD'];

// Only `POST` is supported

if ($http_method != "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Only GET is supported!"]);
} else {
    if (! isset($_POST['username']) || ! isset($_POST['password'])) {
        http_response_code(400);
        echo json_encode(["error" => "Both `username` and `password` need to be defined. `email` is optional."]);
    } else {

        $username = $_POST['username'];
        $password = $_POST['password'];
        $email    = isset($_POST['email']) ? $_POST['email'] : null;

        $user = User::createUser($username, $password, $email);

        if ($user == null) {
            // TODO: Figure out a better response code
            http_response_code(400);
            echo json_encode(["error" => "An account with this name already exists. Choose another name."]);
        } else {
            http_response_code(201);
            echo json_encode([
                "name"  => $user->name,
                "email" => $user->email,
            ]);
        }
    }
}

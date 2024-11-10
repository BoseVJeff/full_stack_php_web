<?php declare (strict_types = 1);
require_once "../../src/api/base.php";
require_once "../../src/api/db.php";
require_once "../../src/api/user.php";

header("Content-Type: application/json");

// echo "<br>Testing Database connection...";

// if($conn==null) {
//     echo "<br>Error connecting to database!";
// } else {
//     echo "<br>Connection established! Closing connection...";
//     $conn=null;
//     echo "<br>Connection closed!";
// }

$db = new Database();

$username  = "Test1";
$password  = "asd";
$password2 = "asdf";
$email     = "meme@meme.meme";
$email2    = "meme@meme.com";

$userMeta = [];
$user     = $db->createUser($username, $password, $email);
if ($user == null) {
    $userMeta['username'] = null;
    $userMeta['userid']   = null;
    $userMeta['email']    = null;
    // $userMeta['password']  = null;
    $userMeta['created']   = false;
    $userMeta['error']     = $db->getLastError()->errorInfo;
    $userMeta['errorCode'] = $db->getLastError()->getCode();
} else {
    $userMeta['username'] = $user->name;
    $userMeta['userid']   = $user->id;
    $userMeta['email']    = $user->email;
    // $userMeta['password']  = $user->password;
    $userMeta['created']   = true;
    $userMeta['error']     = null;
    $userMeta['errorCode'] = null;
}

$reUserMeta = [];
$user       = $db->createUser($username, $password, $email);
if ($user == null) {
    $reUserMeta['username']  = null;
    $reUserMeta['created']   = false;
    $reUserMeta['error']     = $db->getLastError()->errorInfo;
    $reUserMeta['errorCode'] = $db->getLastError()->getCode();
} else {
    $reUserMeta['username']  = $user->name;
    $reUserMeta['created']   = true;
    $reUserMeta['error']     = null;
    $reUserMeta['errorCode'] = null;
}

$loginMeta = [];
$user      = $db->getUser($username, $password);
if ($user == null) {
    $loginMeta['username']  = null;
    $loginMeta['userid']    = null;
    $loginMeta['created']   = false;
    $loginMeta['error']     = $db->getLastError()->errorInfo;
    $loginMeta['errorCode'] = $db->getLastError()->getCode();
} else {
    $loginMeta['username']  = $user->name;
    $loginMeta['userid']    = $user->id;
    $loginMeta['created']   = true;
    $loginMeta['error']     = null;
    $loginMeta['errorCode'] = null;
}

$changedDetailsMeta = [];
$db->setEmail($email2, $user);
$db->setPassword($password2, $user);
$user = $db->getUser($username, $password2);
if ($user == null) {
    $changedDetailsMeta['username'] = null;
    $changedDetailsMeta['userid']   = null;
    $changedDetailsMeta['email']    = null;
    // $changedDetailsMeta['password']  = null;
    $changedDetailsMeta['created']   = false;
    $changedDetailsMeta['error']     = $db->getLastError()->errorInfo;
    $changedDetailsMeta['errorCode'] = $db->getLastError()->getCode();
} else {
    $changedDetailsMeta['username'] = $user->name;
    $changedDetailsMeta['userid']   = $user->id;
    $changedDetailsMeta['email']    = $user->email;
    // $changedDetailsMeta['password']  = $user->password;
    $changedDetailsMeta['created']   = true;
    $changedDetailsMeta['error']     = null;
    $changedDetailsMeta['errorCode'] = null;
}

$deleteMeta = [];

$user = $db->deleteUser($user);
$user = $db->getUser($username, $password2);
if ($user == null) {
    $deleteMeta['username'] = null;
    $deleteMeta['userid']   = null;
    $deleteMeta['created']  = false;
} else {
    $deleteMeta['username'] = $user->name;
    $deleteMeta['userid']   = $user->id;
    $deleteMeta['created']  = true;
}

$db = null;

$user       = User::createUser("Class Test", "qwerty");
$newUser    = ($user == null) ? [] : $user->toJson();
$user       = null;
$user       = User::getUser("Class Test", "qwerty");
$loggedUser = $user->toJson();

// From https://www.php.net/manual/en/function.password-hash.php
/**
 * This code will benchmark your server to determine how high of a cost you can
 * afford. You want to set the highest cost that you can without slowing down
 * you server too much. 10 is a good baseline, and more is good if your servers
 * are fast enough. The code below aims for ≤ 350 milliseconds stretching time,
 * which is an appropriate delay for systems handling interactive logins.
 */
$timeTarget = 0.350; // 350 milliseconds

$cost = 10;
do {
    $cost++;
    $start = microtime(true);
    password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
    $end = microtime(true);
} while (($end - $start) < $timeTarget);

// echo "Appropriate Cost Found: " . $cost;

$data = [
    "__DIR__"                    => __DIR__,
    "\$_SERVER['DOCUMENT_ROOT']" => $_SERVER['DOCUMENT_ROOT'],
    "BCRYPT Hash Cost"           => $cost,
    "create_user"                => $userMeta,
    "recreate_user"              => $reUserMeta,
    "login_user"                 => $loginMeta,
    "updated_details"            => $changedDetailsMeta,
    "delete_user"                => $deleteMeta,
];

echo json_encode($data);

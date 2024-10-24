<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="../styles/base.css">
    <?php
    include "../../src/utils/meta_head.php";
    ?>
    <style>
        @property --radial-stop {
            syntax: '<percentage>';
            inherits: false;
            initial-value: 0%;
        }

        body {
            display: flex;
            place-items: center;
            justify-content: center;

            background: rgb(238, 174, 202);
            background: radial-gradient(circle, rgba(238, 174, 202, 1) var(--radial-stop), rgba(148, 187, 233, 1) 100%);

            animation: pulse 10s infinite;
        }

        @keyframes pulse {
            0% {
                --radial-stop: 0%;
            }

            50% {
                --radial-stop: 25%;
            }

            100% {
                --radial-stop: 0%;
            }
        }

        #login-form {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: center;
            row-gap: 0.25rem;

            border: 1px solid rgba(0, 0, 0, 0);
            border-radius: 0.5rem;
            padding: 0.5rem;

            background-color: transparent;

            backdrop-filter: blur(10px);
        }

        form>input[type=reset] {
            margin-block-start: 1rem;
        }
    </style>
</head>

<body>
    <form action="#" method="get" id="login-form">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" autocomplete="username" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" autocomplete="new-password" required>

        <label for="email">Email</label>
        <input type="email" id="email" autocomplete="email">

        <input type="reset" value="Reset">
        <input type="submit" value="Sign Up">


    </form>
</body>

</html>
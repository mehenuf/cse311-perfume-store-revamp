<?php
session_start();
include('../config/dbcon.php');

//for registration form
//if registration button is pressed then this portion will execute
if (isset($_POST['signup_btn'])) {
    //taking info provided in the form into a variable
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $contacts = $_POST['contacts'];
    $dob = $_POST['dob'];

    //sql query to check whether given email exists in database
    $mailcheck_stmt = mysqli_prepare($con, "SELECT email FROM customer WHERE email = ?");
    mysqli_stmt_bind_param($mailcheck_stmt, 's', $email);
    mysqli_stmt_execute($mailcheck_stmt);
    $mailcheck_query_run = mysqli_stmt_get_result($mailcheck_stmt);

    //sql query to check whether given username exists in database
    $usernamecheck_stmt = mysqli_prepare($con, "SELECT username FROM customer WHERE username = ?");
    mysqli_stmt_bind_param($usernamecheck_stmt, 's', $username);
    mysqli_stmt_execute($usernamecheck_stmt);
    $usernamecheck_query_run = mysqli_stmt_get_result($usernamecheck_stmt);

    //if email already exists in the database then warning will be shown and then will take us to registration form again
    if (mysqli_num_rows($mailcheck_query_run) > 0) {
        $_SESSION['message'] = "An account with this e-mail already exists. Try another mail.";
        header('Location: ../register.php');
    } else {
        //if the username already exists in the database then this warning will be displayed
        if (mysqli_num_rows($usernamecheck_query_run) > 0) {
            $_SESSION['message'] = "An account with this username already exists. Try another username.";
            header('Location: ../register.php');
        } else {
            //if the confirmation password doesn't match with the password then warning will show
            //if the password matches with confirm passwords and every other conditions are met then the info will be inserted into database
            if ($password == $repassword) {
                // Never store the password itself, only a one-way hash of it --
                // so a stolen copy of the database never hands out real passwords.
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                $insert_stmt = mysqli_prepare($con,
                    "INSERT INTO customer (username, password, name, email, contacts, address, dob)
                     VALUES (?, ?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($insert_stmt, 'sssssss',
                    $username, $hashed, $name, $email, $contacts, $address, $dob);
                $insert_query_run = mysqli_stmt_execute($insert_stmt);

                //checking if insertion is working or not
                if ($insert_query_run) {
                    $_SESSION['message'] = "Congrats! You've successfully registered in our store.";
                    header('Location: ../login.php');
                } else {
                    //if insertion to database fails
                    $_SESSION['message'] = "Something went wrong";
                    header('Location: ../register.php');
                }
            } else {
                //if confirm password doesn't match with password
                $_SESSION['message'] = "Passwords do not match. Please use same password in the both field.";
                header('Location: ../register.php');
            }
        }
    }
} elseif (isset($_POST['login_btn'])) {
    //for login
    //will check whether the login button was pressed. If, then this part will execute
    $query_username = $_POST['var_username'];
    $query_password = (string) $_POST['var_password'];

    // Look the account up by username only -- the password never appears in
    // SQL, it is checked in PHP against the stored hash below.
    $login_stmt = mysqli_prepare($con, "SELECT * FROM customer WHERE username = ?");
    mysqli_stmt_bind_param($login_stmt, 's', $query_username);
    mysqli_stmt_execute($login_stmt);
    $userdata = mysqli_fetch_assoc(mysqli_stmt_get_result($login_stmt));

    $authenticated = false;
    if ($userdata) {
        $storedHash = $userdata['password'];
        if (password_verify($query_password, $storedHash)) {
            $authenticated = true;
        } elseif (hash_equals($storedHash, $query_password)) {
            // A row created before hashing was added still holds a plain
            // password. Accept it this one last time, then upgrade it
            // immediately so it is never compared in plain text again.
            $authenticated = true;
            $upgraded = password_hash($query_password, PASSWORD_DEFAULT);
            $rehash_stmt = mysqli_prepare($con, "UPDATE customer SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($rehash_stmt, 'si', $upgraded, $userdata['id']);
            mysqli_stmt_execute($rehash_stmt);
        }
    }

    if ($authenticated) {
        // A fresh session id on every privilege change, so a session id
        // handed out before login cannot be reused to ride in as this user.
        session_regenerate_id(true);

        $_SESSION['auth'] = true;

        $userid = $userdata['id'];
        $username = $userdata['username'];
        $usermail = $userdata['email'];
        $user_admin_check = $userdata['admin_check'];
        $_SESSION['auth_user'] = [
            'user_id' => $userid,
            'username' => $username,
            'email' => $usermail
        ];

        $_SESSION['admin_check'] = $user_admin_check;

        // Fold a guest session cart into the now-known account cart rather
        // than losing it the moment this visitor logs in.
        if (!empty($_SESSION['guest_cart'])) {
            $check_stmt = mysqli_prepare($con, "SELECT id, perfume_qty FROM cart WHERE user_id = ? AND perfume_id = ?");
            $update_stmt = mysqli_prepare($con, "UPDATE cart SET perfume_qty = ? WHERE id = ?");
            $insert_stmt = mysqli_prepare($con, "INSERT INTO cart (user_id, perfume_id, perfume_qty) VALUES (?, ?, ?)");

            foreach ($_SESSION['guest_cart'] as $guestPerfumeId => $guestQty) {
                $guestPerfumeId = (int) $guestPerfumeId;
                $guestQty = (int) $guestQty;

                mysqli_stmt_bind_param($check_stmt, 'ii', $userid, $guestPerfumeId);
                mysqli_stmt_execute($check_stmt);
                $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt));

                if ($existing) {
                    $mergedQty = (int) $existing['perfume_qty'] + $guestQty;
                    mysqli_stmt_bind_param($update_stmt, 'ii', $mergedQty, $existing['id']);
                    mysqli_stmt_execute($update_stmt);
                } else {
                    mysqli_stmt_bind_param($insert_stmt, 'iii', $userid, $guestPerfumeId, $guestQty);
                    mysqli_stmt_execute($insert_stmt);
                }
            }
            unset($_SESSION['guest_cart']);
        }

        if ($_SESSION['admin_check'] == 1) {
            $_SESSION['message'] = 'Welcome Admin!';
            header('Location: ../admin/index.php');
        } else {
            $_SESSION['message'] = $_SESSION['auth_user']['username'] . ", you have successfully logged in!";
            header('Location: ../index.php');
        }
    } else {

        $_SESSION['message'] = "Credentials doesn't match or inexistent.";
        header('Location: ../login.php');
    }
}

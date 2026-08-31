<?php
session_start();
include('../config/dbcon.php');

//for registration form
//if registration button is pressed then this portion will execute
if (isset($_POST['signup_btn'])) {
    //taking info provided in the form into a variable
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $repassword = mysqli_real_escape_string($con, $_POST['repassword']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $contacts = mysqli_real_escape_string($con, $_POST['contacts']);
    $dob = mysqli_real_escape_string($con, $_POST['dob']);

    //sql query to check whether given email exists in database
    $mailcheck_query = "SELECT email FROM customer WHERE email='$email';";
    $mailcheck_query_run = mysqli_query($con, $mailcheck_query);

    //sql query to check whether given username exists in database
    $usernamecheck_query = "SELECT username FROM customer WHERE username='$username';";
    $usernamecheck_query_run = mysqli_query($con, $usernamecheck_query);

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
                //inserting info from the registration form to database
                $insert_query = "INSERT INTO customer (username, password, name, email, contacts, address, dob) VALUES ('$username', '$password', '$name', '$email', '$contacts', '$address', '$dob');";
                $insert_query_run = mysqli_query($con, $insert_query);

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
    $query_username = mysqli_real_escape_string($con, $_POST['var_username']);
    $query_password = mysqli_real_escape_string($con, $_POST['var_password']);

    $login_query = "SELECT * FROM customer WHERE username = '$query_username' AND password ='$query_password';";
    $login_query_run = mysqli_query($con, $login_query);

    if (mysqli_num_rows($login_query_run) > 0) {
        $_SESSION['auth'] = true;

        $userdata = mysqli_fetch_array($login_query_run);
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

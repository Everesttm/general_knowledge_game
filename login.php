<?php
session_start();
include "database.php";

$firstnameErr = $lastnameErr = $userIDRegErr = $passwrdRegErr = "";
$userIDLoginErr = $passwrdLoginErr = "";
$firstname = $lastname = $userID = $passwrd = "";
$errorsReg = $errorsLogin = false;
$registrationSuccess = false;

function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register'])) {
        if (empty(trim($_POST["firstname"]))) {
            $firstnameErr = "Firstname is required.";
            $errorsReg = true;
        } else {
            $firstname = test_input($_POST["firstname"]);
            if (!preg_match("/^[a-zA-Z ]*$/", $firstname)) {
                $firstnameErr = "Only letters and white space allowed.";
                $errorsReg = true;
            }
        }
        if (empty(trim($_POST["lastname"]))) {
            $lastnameErr = "Lastname is required.";
            $errorsReg = true;
        } else {
            $lastname = test_input($_POST["lastname"]);
            if (!preg_match("/^[a-zA-Z ]*$/", $lastname)) {
                $lastnameErr = "Only letters and white space allowed.";
                $errorsReg = true;
            }
        }
        if (empty(trim($_POST["userID"]))) {
            $userIDRegErr = "Email is required.";
            $errorsReg = true;
        } else {
            $userID = test_input($_POST["userID"]);
            if (!filter_var($userID, FILTER_VALIDATE_EMAIL)) {
                $userIDRegErr = "Invalid email format.";
                $errorsReg = true;
            }
        }
        if (empty(trim($_POST["passwrd"]))) {
            $passwrdRegErr = "Password is required.";
            $errorsReg = true;
        } else {
            $passwrd = test_input($_POST["passwrd"]);
            if (!preg_match("/^(?=.*[a-zA-Z]).{6,}$/", $passwrd)) {
                $passwrdRegErr = "Password must be at least 6 characters long.";
                $errorsReg = true;
            }
        }
        if (!$errorsReg) {
            $sql = "INSERT INTO j_user (firstname, lastname, userID, passwrd, last_login) VALUES (?, ?, ?, ?, NOW())";
            if ($stmt = $db->prepare($sql)) {
                $stmt->bind_param("ssss", $firstname, $lastname, $userID, $passwrd);
                if ($stmt->execute()) {
                    $registrationSuccess = true; 
                } else {
                    echo "Something went wrong. Please try again later.";
                }
                $stmt->close();
            }
        }
    } elseif (isset($_POST['login'])) {
        if (empty(trim($_POST["userID"]))) {
            $userIDLoginErr = "Email is required.";
            $errorsLogin = true;
        } else {
            $userID = test_input($_POST["userID"]);
        }
        if (empty(trim($_POST["passwrd"]))) {
            $passwrdLoginErr = "Password is required.";
            $errorsLogin = true;
        } else {
            $passwrd = test_input($_POST["passwrd"]);
        }
        if (!$errorsLogin) {
            $sql = "SELECT id, passwrd, firstname FROM j_user WHERE userID = ?";
            if ($stmt = $db->prepare($sql)) {
                $stmt->bind_param("s", $userID);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $stored_password, $firstname);
                    $stmt->fetch();
                    if ($passwrd == $stored_password) {
                        $_SESSION['userID'] = $id;
                        $_SESSION['username'] = $firstname;  // Store the username in the session
                        header('Location: mainboard.php');
                        exit;
                    } else {
                        $passwrdLoginErr = "The password you entered was not valid.";
                    }
                } else {
                    $userIDLoginErr = "No user found with that email.";
                }
                $stmt->close();
            }
        }
    }
    $db->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>General Knowledge Game Login & Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <section class="top">
        <div><h1> General Knowledge Game Login Panel </h1></div>
    </section>
</header>
<div id="container">
    <article>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h2>Register</h2>
            <div class="placeholders">
                <label for="FirstName">First name *</label>
                <input type="text" id="FirstName" name="firstname" placeholder="Enter firstname" value="<?php echo htmlspecialchars($firstname); ?>">
                <span class="error-message"><?php echo $firstnameErr;?></span>
            </div>
            <div class="placeholders">
                <label for="LastName">Last name *</label>
                <input type="text" id="LastName" name="lastname" placeholder="Enter lastname" value="<?php echo htmlspecialchars($lastname); ?>">
                <span class="error-message"><?php echo $lastnameErr;?></span>
            </div>
            <div class="placeholders">
                <label for="UserID">UserID *</label>
                <input type="text" id="UserID" name="userID" placeholder="Enter email" value="<?php echo htmlspecialchars($userID); ?>">
                <span class="error-message"><?php echo $userIDRegErr;?></span>
            </div>
            <div class="placeholders">
                <label for="Passwrd">Password *</label>
                <input type="password" id="Passwrd" name="passwrd" placeholder="Enter password" value="<?php echo htmlspecialchars($passwrd); ?>">
                <span class="error-message"><?php echo $passwrdRegErr;?></span>
            </div>
            <div class="placeholders">
                <button type="submit" name="register" class="login">Register</button>
                <?php if ($registrationSuccess) { echo "<p>Registration successful. Please log in.</p>"; } ?>
            </div>
        </form>
    </article>
    <article>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h2>Login</h2>
            <div class="placeholders">
                <label for="UserID">UserID *</label>
                <input type="text" id="UserID" name="userID" placeholder="Enter email" value="<?php echo htmlspecialchars($userID); ?>">
                <span class="error-message"><?php echo $userIDLoginErr;?></span>
            </div>
            <div class="placeholders">
                <label for="Passwrd">Password *</label>
                <input type="password" id="Passwrd" name="passwrd" placeholder="Enter password" value="<?php echo htmlspecialchars($passwrd); ?>">
                <span class="error-message"><?php echo $passwrdLoginErr;?></span>
            </div>
            <div class="placeholders">
                <button type="submit" name="login" class="login">Login</button>
            </div>
        </form>
    </article>
</div>
</body>
</html>

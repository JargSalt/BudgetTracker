<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
 
sec_session_start();
 
if (login_check($mysqli) == true) {
    $logged = 'in';
} else {
    $logged = 'out';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Secure Login: Log In</title>
        <link rel="stylesheet" href="styles/login.css" />
        <script type="text/JavaScript" src="js/sha512.js"></script> 
        <script type="text/JavaScript" src="js/forms.js"></script> 
    </head>
    <body>
        <div id='header'>
            <span> Budget Buddy </span>
        </div>
        <div id='signin'>
        <?php
        if (isset($_GET['error'])) {
            echo '<p class="error">Error Logging In!</p>';
        }
        if (login_check($mysqli) == true) {
            echo '<p>Currently logged ' . $logged . ' as ' . htmlentities($_SESSION['username']) . '.</p>';
            echo '<p>Do you want to change user? <a href="includes/logout.php">Log out</a></p>';
            echo '<p>Your Page: <a href="protected_page.php">Home</a></p>';
        }
        else { 
            echo '<form action="includes/process_login.php" method="post" name="login_form">'.                      
                    'Email: <input type="text" name="email" />&nbsp;&nbsp;Password: <input type="password"'. 
                        'name="password" id="password"/><br><br><input type="button" value="Login"'. 
                            'onclick="formhash(this.form, this.form.password);" />&nbsp;&nbsp;<input type="button"'.
                                'value="Register" onclick="location.href = \'register.php\';"></form>';
        }
        ?>
        </div>     
    </body>
</html>
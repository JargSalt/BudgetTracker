<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
 
sec_session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Secure Login: Protected Page</title>
        <link rel="stylesheet" href="styles/userpage.css" />
    </head>
    <body>
        <?php if (login_check($mysqli) == true) : ?>
        <div id="header">
            <span>Welcome <?php echo htmlentities($_SESSION['username']); ?>!</span>
        </div>
        <p><button onclick="window.location = 'index.php';">Login Page</button></p>
        <div id="content">
            <p> Budget Buddy is a comprehensive budget tracking application that allows you to keep
                on top of your spending and finances. Click below to see your personalized budget. </p>
        <p><button onclick="window.location = 'budget_main_view.php';">Your Budget</button></p>
        </div>
        <?php else : ?>
            <p>
                <span class="error">You are not authorized to access this page.</span> Please <a href="index.php">login</a>.
            </p>
        <?php endif; ?>
    </body>
</html>
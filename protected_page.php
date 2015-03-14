<?php
include_once 'includes/db_connect.php';
include_once 'includes/functions.php';
 
sec_session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Budget Buddy</title>
        <link rel="stylesheet" href="styles/userpage.css" />
    </head>
    <body>
        <div id="container">
        <?php if (login_check($mysqli) == true) : ?>
        <div id="header">
            <span>Welcome <?php echo htmlentities($_SESSION['username']); ?>!</span>
        </div>
        <p><button onclick="window.location = 'index.php';">Login Page</button></p>
        <div id="content">
            <p> Budget Buddy is a comprehensive budget tracking application that allows you to keep
                on top of your spending and finances. You are on your way to making your budget... </p>
            <p> When you arrive at the budget page, make a new category with the "New Category" button.
                You can specify the name and spending goal for that category. After you have done this,
                you can start adding transactions to that category to keep track of your spending!
                You can also make sub-categories, and edit existing categories. You can also obtain
                a unique URL for your budget so that you can share it with your friends! Let's get started!</p>
        <p><button onclick="window.location = 'budget_main_view.php';">Your Budget</button></p>
        </div>
        <?php else : ?>
            <p>
                <span class="error">You are not authorized to access this page.</span> Please <a href="index.php">login</a>.
            </p>
        <?php endif; ?>
        </div>
    </body>
</html>
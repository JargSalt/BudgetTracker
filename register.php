<?php
include_once 'includes/register.inc.php';
include_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Registration Form</title>
        <script type="text/JavaScript" src="js/sha512.js"></script> 
        <script type="text/JavaScript" src="js/forms.js"></script>
        <link rel="stylesheet" href="styles/register.css" />
    </head>
    <body>
        <!-- Registration form to be output if the POST variables are not
        set or if the registration script caused an error. -->
        <div id='header'>
            <span> Register with Us </span>
        </div>
        <div id='menu'>
        <input type="button" value="Home" onclick="location.href = 'index.php'">
        </div>
        <?php
        if (!empty($error_msg)) {
            echo $error_msg;
        }
        ?>
        <div id='rform'>
        <form action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>" 
                method="post" 
                name="registration_form">
            Username: <input type='text' name='username' id='username' onfocus="inform(1)"/><br><br>
            Email: <input type="text" name="email" id="email" onfocus="inform(2)"/><br><br>
            Password: <input type="password" name="password" id="password" onfocus="inform(3)"/><br><br>
            Confirm password: <input type="password" name="confirmpwd" id="confirmpwd" onfocus="inform(3)"/><br><br>
            <input type="button" 
                   value="Register" 
                   onclick="return regformhash(this.form,
                                   this.form.username,
                                   this.form.email,
                                   this.form.password,
                                   this.form.confirmpwd);" /> 
        </form>
        </div>
        <div id='criteria'><span>Welcome!</span></div>
        <script>
            function inform(val) {
                var x = val;
                switch (x) {
                    case 1:
                        document.getElementById("criteria").innerHTML = "<span>Usernames may contain only digits, upper and lower case letters and underscores.</span>";
                        break;
                    case 2:
                        document.getElementById("criteria").innerHTML = "<span>Emails must have a valid email format.</span>";
                        break;
                    case 3:
                        document.getElementById("criteria").innerHTML = "<span>Passwords must be at least 6 characters long, and contain:<br><br>"+
                                                              "At least one uppercase letter (A..Z)<br>"+
                                                              "At least one lowercase letter (a..z)<br>"+
                                                              "At least one number (0..9)<br></span>";
                        break;
                    default:
                        document.getElementById("criteria").innerHTML = "";
                }
            }
        </script>
    </body>
</html>
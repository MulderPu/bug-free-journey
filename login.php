<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>CabsOnline Login</title>
    </head>
    <body>
        <h1>Login to CabsOnline</h1>


        <?php
            session_start();

            //initialize var
            $email = '';
            $emailErr = '';
            $passwdErr = '';
            $required = array('email','password');
            $error = false;

            //check if submitted
            if($_SERVER['REQUEST_METHOD'] == "POST"){
                //check if email is empty
                if(empty($_POST['email'])){
                    $emailErr = "Email is required.";
                }else{
                    $email = test_input($_POST['email']);
                    //validate email format
                    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                        $emailErr = "Invalid email format. Please re-enter again.";
                        $email = '';
                        $error = true;
                    }
                }

                //check if password is empty
                if(empty($_POST['password'])){
                    $passwdErr = "Password is required.";
                }

                //loop all the field to make sure all required field is filled
                foreach ($required as $field){
                    if(empty($_POST[$field])){
                        $error = true;
                    }
                }

                //if all fields is filled
                if(!$error){
                    $conn = connectToDB(); // connect to db
                    $customerEmail = $_POST['email'];
                    $customerPasswd = $_POST['password'];

                    //collect user data as session
                    $_SESSION['form_email'] = $customerEmail;

                    validateAdmin($customerEmail, $customerPasswd ,$conn);

                    //check if customer email exist in databases
                    validateCustomer($customerEmail, $customerPasswd ,$conn);
                    mysqli_close($conn); //close connections
                }
            }

            //check if the data has unnessary input
            function test_input($data) {
              $data = trim($data); //remv unnessary data
              $data = stripslashes($data); //remv backslash
              $data = htmlspecialchars($data); //keep data safe
              return $data;
            }

            //connect to database
            function connectToDB(){
                $dbhost = 'localhost';
                $dbuser = 'root';
                $dbpass = '';
                $dbname = 'assignment1db';

                //query to connect into mysql account
                $connection = mysqli_connect($dbhost, $dbuser, $dbpass);
                if(!$connection ){
                 die("Could not connect to mysql. " );
                }

                //query to use database selected
                $connect_db = mysqli_select_db($connection,$dbname);
                if(!$connect_db ){
                 die("Could not connect to database. ");
                }

                return $connection;
            }

            //validate user email and password
            function validateCustomer($customerEmail, $customerPasswd ,$conn){
                //query user email
                $queryEmail = "SELECT customer_email FROM customer WHERE customer_email = '$customerEmail'";
                $selectQuery = mysqli_query($conn,$queryEmail);
                $numRows = mysqli_num_rows($selectQuery);

                //query if email exist in databases
                if($numRows != 0){
                    $queryPw = "SELECT password FROM customer WHERE customer_email = '$customerEmail'";
                    $selectPw = mysqli_query($conn,$queryPw);

                    while($rows = mysqli_fetch_assoc($selectPw)){
                        $checkPassword = $rows['password'];
                    }

                    //query if password is matched
                    if($customerPasswd === $checkPassword){
                        header('Location: booking.php');
                    }else{
                        echo "Login failed. Password is incorrect.";
                    }
                }
            }

            function validateAdmin($customerEmail, $customerPasswd ,$conn){
                if($customerEmail === "admin@admin.com"){
                    if($customerPasswd === "admin"){
                        header('Location: admin.php');
                    }else{
                        echo "Please try again.";
                    }
                }
            }

        ?>

        <form class="form_login" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <table>
                <tr>
                    <td>
                        <label for="Email">Email: </label>
                    </td>
                    <td>
                        <input type="text" name="email" value="<?php echo $email; ?>">
                        <span class="error">* <?php echo $emailErr;?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="Password">Password: </label>
                    </td>
                    <td>
                        <input type="password" name="password" value="">
                        <span class="error">* <?php echo $passwdErr;?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" name="login" value="Log in">
                    </td>
                </tr>
            </table>
        </form>

        <h2>New member? <a href="register.php"><u>Register now</u></a></h2>
    </body>
</html>

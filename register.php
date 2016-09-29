<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="author" content="Mulder Pu Ming Fei">
        <title>CabsOnline Registration</title>
    </head>
    <body>
        <h1>Register to CabsOnline</h1>
        <p>
            Please fill the fields below to complete your registration.<br><br>
        </p>

        <?php
            //initialize var
            $name = '';
            $nameErr = '';
            $passwdErr = '';
            $cPasswdErr = '';
            $email = '';
            $emailErr = '';
            $contact = '';
            $contactErr = '';
            $required = array('name', 'password','confirm_password', 'email', 'phone');
            $error = false;

            //post request true
            if($_SERVER["REQUEST_METHOD"] == "POST"){
                //validate name and only allow the user to enter alphabet
                if(empty($_POST['name'])){
                    $nameErr = "Name is required.";
                }
                else{
                    $name = test_input($_POST['name']);

                    //no numbers allowed
                    if(!preg_match("/^[a-zA-Z ]*$/",$name)) {
                        $nameErr = "Only letters and white space allowed.";
                        $name = '';
                        $error = true;
                    }
                }

                if(empty($_POST['password'])){
                    $passwdErr = "Password is required.";
                }

                if(empty($_POST['confirm_password'])){
                    $cPasswdErr = "Confirm password is required.";
                }else {
                    //check if confirm password is matched with password entered
                    if($_POST['password'] != $_POST['confirm_password'] ){
                        $cPasswdErr = "Password is wrong. Please re-enter again.";
                        $error = true;
                    }
                }

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

                if(empty($_POST['phone'])){
                    $contactErr = "Phone number is required.";
                }else{
                    $contact = test_input($_POST['phone']);

                    //only numbers allowed
                    if(preg_match("/^[a-zA-Z ]*$/",$contact)) {
                        $contactErr = "Only numbers is allowed.";
                        $error = true;
                    }
                }

                foreach ($required as $field){
                    if(empty($_POST[$field])){
                        $error = true;
                    }
                }

                if(!$error){
                    $conn = connectToDB();
                    $name = $_POST['name'];
                    $customerPasswd = $_POST['password'];
                    $customerEmail = $_POST['email'];
                    $customerContact = $_POST['phone'];

                    //validate customer email
                    checkCustomerEmail($customerEmail, $conn);

                    //insert new entry
                    $query = "INSERT INTO customer (customer_name, password, customer_email, customer_contact)
                            VALUES ('$name','$customerPasswd','$customerEmail','$customerContact')";

                    //query entry
                    mysqli_query($conn,$query);

                    //empty fields
                    $name = '';
                    $email = '';
                    $contact = '';

                    //relocate to login page
                    header('Location: login.php');
                    //close connections
                    mysqli_close($conn);
                }

            }

            function test_input($data) {
              $data = trim($data); //remv unnessary data
              $data = stripslashes($data); //remv backslash
              $data = htmlspecialchars($data); //keep data safe
              return $data;
            }

            //connect to databases
            function connectToDB(){
                $dbhost = 'localhost';
                $dbuser = 'root';
                $dbpass = '';
                $dbname = 'assignment1db';
                $queryToCreateDB = " CREATE DATABASE IF NOT EXISTS $dbname";
                $queryCustomerTB = "CREATE TABLE IF NOT EXISTS customer (
                    customer_name VARCHAR(70) NOT NULL,
                    password VARCHAR(32) NOT NULL,
                    customer_contact VARCHAR(50) NOT NULL,
                    customer_email VARCHAR(70) NOT NULL,
                    PRIMARY KEY (customer_email)
                )";

                //connection to mysql
                $connection = mysqli_connect($dbhost, $dbuser, $dbpass);
                if(!$connection ){
                 die("Could not connect to mysql. " );
                }

                //query to create database if not exist
                mysqli_query($connection, $queryToCreateDB);

                //select database to connect
                $connect_db = mysqli_select_db($connection,$dbname);
                if(!$connect_db ){
                 die("Could not connect to database. ");
                }

                //query to create table if customer table not exist
                mysqli_query($connection, $queryCustomerTB);
                return $connection;
            }

            //check if customer's email existed
            function checkCustomerEmail($customerEmail,$conn){
                $queryEmail = "SELECT customer_email FROM customer WHERE customer_email = '$customerEmail'";
                $selectQuery = mysqli_query($conn,$queryEmail);
                $numRows = mysqli_num_rows($selectQuery);
                if($numRows != 0){
                    echo "This email has been used.<br>";
                }
            }
        ?>

        <form class="form_register" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <table>
                <tr>
                    <td>
                        <label for="Name" >Name: </label>
                    </td>
                    <td>
                        <input type="text" name="name" value="<?php echo $name;?>">
                        <span class="error">* <?php echo $nameErr;?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="Password" >Password: </label>
                    </td>
                    <td>
                        <input type="password" name="password" value="">
                        <span class="error">* <?php echo $passwdErr;?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="Confirm password" >Confirm password: </label>
                    </td>
                    <td>
                        <input type="password" name="confirm_password" value="">
                        <span class="error">* <?php echo $cPasswdErr;?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="Email" >Email: </label>
                    </td>
                    <td>
                        <input type="text" name="email" value="<?php echo $email;?>">
                        <span class="error">* <?php echo $emailErr;?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="Phone" >Phone: </label>
                    </td>
                    <td>
                        <input type="text" name="phone" value="<?php echo $contact;?>">
                        <span class="error">* <?php echo $contactErr;?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" name="register" value="Register">
                    </td>
                </tr>
            </table>
        </form>

        <h2>Already registered? <a href="login.php"><u>Login here</u></a></h2>

    </body>

</html>

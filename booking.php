<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>CabsOnline Booking</title>
    </head>
    <body>
        <h1>Booking a cab</h1>
        <p>
            Please fill the fields below to book a taxi.<br>
        </p>

        <?php
            session_start();
            if($_SESSION['form_email']){
                $bookingEmail = $_SESSION['form_email'];
                echo "<p>Hello ".$bookingEmail.", </p>";
            }

            //initialize var
            $name = '';
            $nameErr = '';
            $contact = '';
            $contactErr = '';
            $unitNum = '';
            $unitNumErr = '';
            $streetNum = '';
            $streetNumErr = '';
            $streetName = '';
            $streetNameErr = '';
            $suburb = '';
            $suburbErr = '';
            $dest = '';
            $destErr = '';
            $pickupDate = '';
            $pickupDateErr = '';
            $pickupTime = '';
            $pickupTimeErr = '';

            //set timezone
            date_default_timezone_set("Asia/Kuching");
            $currentDate = date('Y/m/d');
            $currentTime = date('H:i');

            //array of field required to fill
            $required = array(
                'name',
                'contact',
                'street_number',
                'street_name',
                'suburb',
                'destination',
                'pickup_date',
                'pickup_time',
            );
            $error = false;

            //server check post request
            if($_SERVER['REQUEST_METHOD'] == "POST"){
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

                if(empty($_POST['contact'])){
                    $contactErr = "Phone number is required.";
                }else{
                    $contact = test_input($_POST['contact']);

                    //only numbers are allowed
                    if(preg_match("/^[a-zA-Z ]*$/",$contact)) {
                        $contactErr = "Only numbers is allowed.";
                        $error = true;
                    }
                }

                if(empty($_POST['unit_number'])){
                    $unitNumErr = '';
                }else{
                    $unitNum = test_input($_POST['unit_number']);

                    //only numbers are allowed
                    if(preg_match("/^[a-zA-Z ]*$/",$unitNum)) {
                        $unitNumErr = "Only numbers is allowed.";
                        $error = true;
                    }

                }

                if(empty($_POST['street_number'])){
                    $streetNumErr = "Street number is required.";
                }else{
                    $streetNum = test_input($_POST['street_number']);

                    //only numbers are allowed
                    if(preg_match("/^[a-zA-Z ]*$/",$streetNum)) {
                        $streetNumErr = "Only numbers is allowed.";
                        $error = true;
                    }
                }

                if(empty($_POST['street_name'])){
                    $streetNameErr = "Street name is required.";
                }else{
                    $streetName = test_input($_POST['street_name']);

                    //no numbers allowed
                    if(!preg_match("/^[a-zA-Z ]*$/",$streetName)) {
                        $streetNameErr = "Only letters and white space allowed.";
                        $streetName = '';
                        $error = true;
                    }
                }

                if(empty($_POST['suburb'])){
                    $suburbErr = "Suburb is required.";
                }else {
                    $suburb = test_input($_POST['suburb']);

                    //no numbers are allowed
                    if(!preg_match("/^[a-zA-Z ]*$/",$suburb)) {
                        $suburbErr = "Only letters and white space allowed.";
                        $suburb = '';
                        $error = true;
                    }
                }

                if(empty($_POST['destination'])){
                    $destErr = "Destination suburb is required.";
                }else {
                    $dest = test_input($_POST['destination']);

                    //no numbers are allowed
                    if(!preg_match("/^[a-zA-Z ]*$/",$dest)) {
                        $destErr = "Only letters and white space allowed.";
                        $dest = '';
                        $error = true;
                    }
                }

                if(empty($_POST['pickup_date'])){
                    $pickupDateErr = "Empty date. Please fill in the date. (mm/dd/yyyy)";
                }else {
                    $pickupDate = test_input($_POST['pickup_date']);

                    $convertInputDate = strtotime($pickupDate);
                    $convertCurrentDate = strtotime($currentDate);

                    //only allow later date
                    if($convertInputDate < $convertCurrentDate){
                        $pickupDateErr = "Error date input. Must be at least today. (mm/dd/yyyy)";
                        $error = true;
                    }
                }

                if(empty($_POST['pickup_time'])){
                    $pickupTimeErr = "Empty time. Please fill in the time.";
                }else{
                    $pickupTime = test_input($_POST['pickup_time']);
                    $convertInputTime = strtotime($pickupTime);
                    $convertCurrentTime = strtotime($currentTime);

                    //only allow pickup time to have at least 1 hour
                    if($convertInputTime < $convertCurrentTime){
                        $oneHour = date('H:i',strtotime('+1 hour'));
                        $convertOneHour = strtotime($oneHour);
                        if($convertInputTime < $convertOneHour){
                            $pickupTimeErr = "Error time input. Needed to be at least one hour from now.";
                            $error = true;
                        }
                    }

                }

                foreach ($required as $field){
                    if(empty($_POST[$field])){
                        $error = true;
                    }
                }

                if(!$error){
                    $conn = connectToDB();

                    //insert info if no error
                    $query = "INSERT INTO booking (
                        booking_email,
                        passenger_name,
                        passenger_contact,
                        unit_number,
                        street_number,
                        street_name,
                        suburb,
                        destination,
                        pickup_date,
                        pickup_time,
                        booking_date,
                        booking_time
                    ) VALUES(
                        '$bookingEmail',
                        '$name',
                        '$contact',
                        '$unitNum',
                        '$streetNum',
                        '$streetName',
                        '$suburb',
                        '$dest',
                        '$pickupDate',
                        '$pickupTime',
                        '$currentDate',
                        '$currentTime'
                        )";

                    //query to save input into db
                    mysqli_query($conn,$query);

                    //get current booking ref assigned to the request
                    $queryRefNum = "SELECT booking_number FROM booking WHERE booking_email = '$bookingEmail' ORDER BY booking_number DESC";
                    $getRefNum=mysqli_query($conn,$queryRefNum);

                    $rows = mysqli_fetch_assoc($getRefNum);
                    $refNum = $rows['booking_number'];


                    echo "<p>Thank you! Your booking reference number is
                        ".$refNum.". We will pick up the passengers in front of your
                        provided address at ".$pickupTime." on ". $pickupDate.". </p>";

                    $name = '';
                    $contact = '';
                    $unitNum = '';
                    $streetNum = '';
                    $streetName = '';
                    $suburb = '';
                    $dest = '';
                    $pickupDate = '';
                    $pickupTime = '';

                    mysqli_close($conn);
                }
            }

            function connectToDB(){
                $dbhost = 'localhost';
                $dbuser = 'root';
                $dbpass = '';
                $dbname = 'assignment1db';
                $queryToCreateDB = " CREATE DATABASE IF NOT EXISTS $dbname";
                $queryBookingTB = "CREATE TABLE IF NOT EXISTS booking (
                    booking_number INT(10) NOT NULL AUTO_INCREMENT,
                    booking_email VARCHAR(70) NOT NULL,
                    passenger_name VARCHAR(70) NOT NULL,
                    passenger_contact VARCHAR(50) NOT NULL,
                    unit_number VARCHAR(5) NULL,
                    street_number VARCHAR(30) NOT NULL,
                    street_name VARCHAR(30) NOT NULL,
                    suburb VARCHAR(100) NOT NULL,
                    destination VARCHAR(100) NOT NULL,
                    pickup_date DATE NOT NULL,
                    pickup_time TIME NOT NULL,
                    booking_date DATE NOT NULL,
                    booking_time TIME NOT NULL,
                    status VARCHAR(10) NOT NULL DEFAULT 'unassigned',
                    FOREIGN KEY (booking_email) REFERENCES customer (customer_email),
                    PRIMARY KEY (booking_number)
                )";

                //connect to mysql
                $connection = mysqli_connect($dbhost, $dbuser, $dbpass);
                if(!$connection ){
                 die("Could not connect to mysql. " );
                }

                //create database if not exist
                mysqli_query($connection, $queryToCreateDB);

                //select db and connect
                $connect_db = mysqli_select_db($connection,$dbname);
                if(!$connect_db ){
                 die("Could not connect to db. ");
                }

                //create booking if not exist
                mysqli_query($connection, $queryBookingTB);
                return $connection;
            }

            function test_input($data) {
              $data = trim($data); //remv unnessary data
              $data = stripslashes($data); //remv backslash
              $data = htmlspecialchars($data); //keep data safe
              return $data;
            }
        ?>

        <form class="form_booking" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <table>
                <tr>
                    <td>
                        <label for="Passenger Name">Passenger Name: </label>
                    </td>
                    <td>
                        <input type="text" name="name" value="<?php echo $name;?>">
                        <span class="error">* <?php echo $nameErr;?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="Contact">Contact phone of the passenger: </label>
                    </td>
                    <td>
                        <input type="text" name="contact" value="<?php echo $contact;?>">
                        <span class="error">* <?php echo $contactErr;?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="Address">Pick up address: </label>
                    </td>
                    <td>
                        <label for="Unit number">Unit number <input type="text" name="unit_number" value="<?php echo $unitNum;?>"></label>
                        <span class="error"><?php echo $unitNumErr;?></span><br>
                        <label for="Street number">Street number <input type="text" name="street_number" value="<?php echo $streetNum;?>"></label>
                        <span class="error">* <?php echo $streetNumErr;?></span><br>
                        <label for="Street name">Street name <input type="text" name="street_name" value="<?php echo $streetName;?>"></label>
                        <span class="error">* <?php echo $streetNameErr;?></span><br>
                        <label for="Suburb">Suburb <input type="text" name="suburb" value="<?php echo $suburb;?>"></label>
                        <span class="error">* <?php echo $suburbErr;?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="Destination Suburb">Destination suburb: </label>
                    </td>
                    <td>
                        <input type="text" name="destination" value="<?php echo $dest;?>">
                        <span class="error">* <?php echo $destErr;?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="Pickup Date">Pickup date: </label>
                    </td>
                    <td>
                        <input type="date" name="pickup_date" value="<?php echo $pickupDate;?>">
                        <span class="error">* <?php echo $pickupDateErr;?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="Pickup Time">Pickup Time: </label>
                    </td>
                    <td>
                        <input type="time" name="pickup_time" value="<?php echo $pickupTime;?>">
                        <span class="error">* <?php echo $pickupTimeErr;?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="submit" name="book" value="Book">
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>

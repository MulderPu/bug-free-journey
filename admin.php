<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>CabsOnline Admin Page</title>
    </head>

    <?php
        session_start();
        if($_SESSION['form_email']){
            $userEmail = $_SESSION['form_email'];
            echo "<p>Hello ".$userEmail.", </p>";
        }

        //used for checking user request within 2 hours from current time
        function checkRequest($conn){
            //set default timezone
            date_default_timezone_set("Asia/Kuching");
            $currentDate = date('Y-m-d');
            $currentTime = date('H:i');

            $twoHour = date('H:i',strtotime('+2 hour'));
            $convertTwoHour = strtotime($twoHour);
            $convertCurrentTime = strtotime($currentTime);

            //show user request if pickup time is within 2 hours from current time
            if($convertCurrentTime < $convertTwoHour){
                $queryRequestData = "SELECT booking_number,
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
                customer_email,
                customer_name
                FROM booking INNER JOIN customer on booking_email = customer_email
                WHERE status = 'unassigned'
                AND pickup_date = '$currentDate'";
            }

            $selectData = mysqli_query($conn,$queryRequestData);

            echo "<table border='1'>";
            echo "
            <tr>
                <th>
                    reference #
                </th>
                <th>
                    customer name
                </th>
                <th>
                    passenger name
                </th>
                <th>
                    passenger contact phone
                </th>
                <th>
                    pick-up address
                </th>
                <th>
                    destination suburb
                </th>
                <th>
                    pick-time
                </th>
            </tr>";

            while($rows = mysqli_fetch_assoc($selectData)){
                $bookingNum = $rows['booking_number'];
                $customerName = $rows['customer_name'];
                $name = $rows['passenger_name'];
                $contact = $rows['passenger_contact'];
                $unitNum = $rows['unit_number'];
                $streetNum = $rows['street_number'];
                $streetName = $rows['street_name'];
                $suburb = $rows['suburb'];
                $destination = $rows['destination'];
                $pickupDate = $rows['pickup_date'];
                $pickupTime = $rows['pickup_time'];

                if(!empty($unitNum)){
                    $slash = "/";
                    echo "
                    <tr>
                        <td>
                            {$bookingNum}
                        </td>
                        <td>
                            {$customerName}
                        </td>
                        <td>
                            {$name}
                        </td>
                        <td>
                            {$contact}
                        </td>
                        <td>
                            {$unitNum}{$slash}{$streetNum} {$streetName} , {$suburb}
                        </td>
                        <td>
                            {$destination}
                        </td>
                        <td>
                            {$pickupDate} {$pickupTime}
                        </td>
                    </tr>
                    ";
                }else{
                    echo "
                    <tr>
                        <td>
                            {$bookingNum}
                        </td>
                        <td>
                            {$customerName}
                        </td>
                        <td>
                            {$name}
                        </td>
                        <td>
                            {$contact}
                        </td>
                        <td>
                            {$unitNum}{$streetNum} {$streetName} , {$suburb}
                        </td>
                        <td>
                            {$destination}
                        </td>
                        <td>
                            {$pickupDate} {$pickupTime}
                        </td>
                    </tr>
                    ";
                }
            }
            echo "</table>";
        }

        //check and update booking status
        function checkRefNum($conn, $referenceNum){
            $queryRef = "SELECT booking_number FROM booking WHERE booking_number = '$referenceNum' AND status = 'unassigned'";
            $getQuery = mysqli_query($conn,$queryRef);
            $numRows = mysqli_num_rows($getQuery);

            if($numRows != 0){
                //update query to assigned
                $query = "UPDATE booking SET status = 'assigned' WHERE booking_number = '$referenceNum'";
                mysqli_query($conn,$query);
            }else{
                echo "Invalid reference number or status is assigned.";
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
    ?>
    <body>
        <h3>1. Click below button to search for all unassigned booking requests with a pick-up time within 2 hours.</h3>
        <form class="form_list_all" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <input type="submit" name="listAll" value="List all"><br><br>
        </form>

        <?php
            //if server received post request and the name is listAll
            if(isset($_POST['listAll']) && $_SERVER['REQUEST_METHOD'] == "POST"){
                //connect to db
                $conn = connectToDB();
                //check all user request
                checkRequest($conn);
                //close connections
                mysqli_close($conn);
            }
        ?>
        <hr>
        <h3>2. Input a reference number below and click "update" button to assign a taxi to that request</h3><br>
        <form class="form_update" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
            <label for="Reference number">Reference number:
                <input type="text" name="reference_number" value="">
                <input type="submit" name="update" value="update">
            </label>
        </form>
        <?php
            //if server received post request and the name is update
            if(isset($_POST['update']) && $_SERVER['REQUEST_METHOD'] == "POST"){
                $referenceNum = '';
                $error = false;

                //check if field is empty
                if(empty($_POST['reference_number'])){
                    echo "* Reference number is required.";
                }else{
                    $referenceNum = test_input($_POST['reference_number']);

                    //only allow numbers
                    if(preg_match("/^[a-zA-Z ]*$/",$referenceNum)) {
                        echo "* Only numbers is allowed.";
                        $error = true;
                    }
                }

                //if all field is filled
                if(!$error){
                    //connect to db
                    $conn = connectToDB();
                    //check all user request
                    checkRefNum($conn, $referenceNum);
                    //close connections
                    mysqli_close($conn);
                }
            }
        ?>
    </body>
</html>

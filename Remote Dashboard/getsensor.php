<?php
        require_once 'mysqldb.php';
        header('Content-Type: application/json');

        getSensor();

        function getSensor(){
            global $db_host, $db_user, $db_pass, $db_name;

            $json_sensor1 = array();
            $json_sensor2 = array();
            $json_sensor3 = array();
            $json_array = array();

            /* start connection */
            $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    
            /* check connection */
            if (mysqli_connect_errno()) {
                printf("Connection failed: %s\n", mysqli_connect_error());
                exit();
            }

            // get/display datetime and sensor value        
            // ordered by ID (retrieves the highest ID number in descending order)
            $sensor1 = '(SELECT ID, Date, sensorName, sensorValue  FROM sensor WHERE sensorName = "SENSOR 1" ORDER BY ID DESC LIMIT 10) ORDER BY ID ASC';
            $sensor2 = '(SELECT ID, Date, sensorName, sensorValue  FROM sensor WHERE sensorName = "SENSOR 2" ORDER BY ID DESC LIMIT 10) ORDER BY ID ASC';
            $sensor3 = '(SELECT ID, Date, sensorName, sensorValue  FROM sensor WHERE sensorName = "SENSOR 3" ORDER BY ID DESC LIMIT 10) ORDER BY ID ASC';
        
            // ordered by date (retrieves the most recent in descending order)
            // $sensor1 = '(SELECT ID, Date, sensorName, sensorValue  FROM sensor WHERE sensorName = "SENSOR 1" ORDER BY Date DESC LIMIT 50) ORDER BY ID ASC';
            // $sensor2 = '(SELECT ID, Date, sensorName, sensorValue  FROM sensor WHERE sensorName = "SENSOR 2" ORDER BY Date DESC LIMIT 50) ORDER BY ID ASC';
            // $sensor3 = '(SELECT ID, Date, sensorName, sensorValue  FROM sensor WHERE sensorName = "SENSOR 3" ORDER BY Date DESC LIMIT 50) ORDER BY ID ASC';
        
            $json_sensor1 = sqlQuery($conn,$sensor1);
            $json_sensor2 = sqlQuery($conn,$sensor2);
            $json_sensor3 = sqlQuery($conn,$sensor3);
    
            /* close connection */
            mysqli_close($conn);

            $json_array = array_merge($json_sensor1, $json_sensor2, $json_sensor3);
            $json_array = json_encode($json_array, JSON_NUMERIC_CHECK);

            echo $json_array;
        }
        
        function sqlQuery($conn,$sql_query){
            $json_array_temp = array();
            if($query = mysqli_query($conn,$sql_query)){
                while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)){
                    $json_array_temp[] = $row;
                }
                /* free result set */
                mysqli_free_result($query);
            }
            return $json_array_temp;
        }
?>
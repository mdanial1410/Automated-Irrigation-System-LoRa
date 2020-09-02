<?php
    require_once 'mysqldb.php';

    if(isset($_POST['id']) and isset($_POST['status'])){
        $id = $_POST['id'];
        $status = $_POST['status'];
        updateValues($id, $status);
        // getValues();
    }

    function getValues(){
        /*
        This function retrieves the values from the database
        and store it in an array.
        */
        global $db_host, $db_user, $db_pass, $db_name;
        $data = array();
        
        /* start connection */
        $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    
        /* check connection */
        if (mysqli_connect_errno()) {
            printf("Connection failed: %s\n", mysqli_connect_error());
            exit();
        }
    
        $sql = 'SELECT * FROM actuator ORDER BY ID';
        if($query = mysqli_query($conn,$sql)){
            while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)){
                $data[] = $row;
            
                // Display into html table
                echo "<tr>";
                echo "<td>{$row['ID']}</td>";
                echo "<td>{$row['name']}</td>";
                echo "<td>
                        <input type='button' id='{$row['ID']}' value='{$row['value']}' name='{$row['name']}'>
                    </td>";
                echo "</tr>";
            }
        
        /* free result set */
        mysqli_free_result($query);
        }
    
        /* close connection */
        mysqli_close($conn);

        socket($data);
    }

    function updateValues($id, $status){
        /*
        This function updates the database with 
        values retrieved from POST.
        */

        global $db_host, $db_user, $db_pass, $db_name;

        /* start connection */
        $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    
        /* check connection */
        if (mysqli_connect_errno()) {
           printf("Connection failed: %s\n", mysqli_connect_error());
           exit();
        }
        
        // Prevent SQL injection
        $status = mysqli_real_escape_string($conn, $status);
        $id = mysqli_real_escape_string($conn, $id);

        $sql = "UPDATE actuator SET value='$status' WHERE ID=$id";
        // $sql = "INSERT INTO actuator (value, name) VALUES ('$status', 'Water Pump')";
    
        mysqli_query($conn,$sql);
    
        /* close connection */
        mysqli_close($conn);
        getValues();
    }
?>
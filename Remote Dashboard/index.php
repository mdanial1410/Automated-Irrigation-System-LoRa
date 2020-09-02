<?php
    require 'mysql.php';
?>
     <!DOCTYPE HTML>
     <html>
     <head>
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <link rel="stylesheet" type="text/css" media="screen" href="style.css" />
     <!-- jQuery Script -->
     <script
            src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
            crossorigin="anonymous">
    </script>

    <script>
    $( document ).ready(function() {
            // jQuery code
            // jQuery code available after the page has fully loaded
            $(".table #tbody1").on('click', ':button', function(){
                    id = $(this).prop("id");
                    console.log('button ' + id + ' pressed');

                    if($(this).prop('value') == 'ON'){
                        status = 'OFF';
                    }else{
                        status = 'ON';
                    }

                    // load table with updated values
                    $('#tbody1').load("mysql.php", {
                        id: id,
                        status: status
                    }, function(){
                        console.log('table loaded');
                    });
                });
    });
    </script>

     <script>
     window.onload = function() {      
     var updateInterval = 5000;
     var sensor1Data = [];
     var sensor2Data = [];
     var sensor3Data = [];
     var s1oldID = 0;
     var s2oldID = 0;
     var s3oldID = 0;
     var flag = 0;
      
     var chart = new CanvasJS.Chart("chartContainer", {
         zoomEnabled: true,
         title: {
             text: "Soil Moisture Reading"
         },
         axisX: {
             title: "chart updates every " + updateInterval / 1000 + " secs"
         },
         axisY:{
             includeZero: false
         }, 
         toolTip: {
             shared: true
         },
         legend: {
             cursor:"pointer",
             verticalAlign: "top",
             fontSize: 22,
             fontColor: "dimGrey",
             itemclick : toggleDataSeries
         },
         data: [{ 
                 type: "spline",
                 name: "Sensor 1",
                 dataPoints: sensor1Data
             },
             {
                 type: "spline",
                 name: "Sensor 2",
                 dataPoints: sensor2Data
             },
             {
                 type: "spline",
                 name: "Sensor 3",
                 dataPoints: sensor3Data
             }]
     });

     setInterval(function(){updateChart()}, updateInterval);
      
     function toggleDataSeries(e) {
         if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
             e.dataSeries.visible = false;
         }
         else {
             e.dataSeries.visible = true;
         }
         chart.render();
     }
    
     function updateChart() {
        $.getJSON("http://SERVER_IP/getsensor.php", addData2);    //change server ip accordingly
     }

     function addData(data){
         // try using ID to filter new values.
         // eg: newData[i].ID != oldData[i].ID
         // only plot new data. shift graph when datapoints > than a value
        for (var i = 0; i < data.length; i++) {
            if(data[i].sensorName == 'SENSOR 1'){
		        sensor1Data.push({
                    x: new Date(data[i].Date), 
                    y: Number(data[i].sensorValue)
		        });
                
                s1oldID = data[i].ID;
            }
      
            if(data[i].sensorName == 'SENSOR 2'){
		        sensor2Data.push({
                    x: new Date(data[i].Date), 
                    y: Number(data[i].sensorValue)
		        });
                s2oldID = data[i].ID;
            }

            if(data[i].sensorName == 'SENSOR 3'){
		        sensor3Data.push({
                    x: new Date(data[i].Date), 
                    y: Number(data[i].sensorValue)
		        });
                s3oldID = data[i].ID;
            }
	    } 
	    chart.render();
    }

    function addData2(data){
         // try using ID to filter new values.
         // eg: newData[i].ID != oldData[i].ID
         // only plot new data. shift graph when datapoints > than a value

        for (var i = 0; i < data.length; i++) {
            if(data[i].sensorName == 'SENSOR 1'){
                if(s1oldID < data[i].ID){
                    sensor1Data.push({
                        x: new Date(data[i].Date), 
                        y: Number(data[i].sensorValue)
		            });
                    s1oldID = data[i].ID;
                }
            }
      
            if(data[i].sensorName == 'SENSOR 2'){
                if(s2oldID < data[i].ID){
		            sensor2Data.push({
                        x: new Date(data[i].Date), 
                        y: Number(data[i].sensorValue)
		            });
                    s2oldID = data[i].ID;
                }
            }

            if(data[i].sensorName == 'SENSOR 3'){
                if(s3oldID < data[i].ID){
                    sensor3Data.push({
                        x: new Date(data[i].Date), 
                        y: Number(data[i].sensorValue)
		            });
                    s3oldID = data[i].ID;
                }
            }
	    }

        if(sensor1Data.length > 50){
            sensor1Data.shift();
        }
        if(sensor2Data.length > 50){
            sensor2Data.shift();
        }
        if(sensor3Data.length > 50){
            sensor3Data.shift();
        }

	    chart.render();
    }

    $.getJSON("http://SERVER_IP/getsensor.php", addData);   //change server ip accordingly
      
    }
     </script>
     </head>
     <body>
     <div id="chartContainer" style="height: 370px; width: 100%;"></div>
     <script src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
     <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

     <div class="table">
        <table>
            <thead>
                <tr>
                    <th>ID:</th>
                    <th>Name:</th>
                    <th>Status:</th>
                </tr>
            </thead>
            <tbody id='tbody1'>
                <?php
                    getValues();
                ?>
            </tbody>
        </table>
    </div>

     </body>
     </html>                              
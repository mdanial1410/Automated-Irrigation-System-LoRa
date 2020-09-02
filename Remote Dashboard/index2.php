     <!DOCTYPE HTML>
     <html>
     <head>
     <script>
     window.onload = function() {      
     var updateInterval = 2000;
     var sensor1Data = [];
     var sensor2Data = [];
     var sensor3Data = [];
      
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
                 type: "line",
                 name: "Sensor 1",
                 dataPoints: sensor1Data
             },
             {
                 type: "line",
                 name: "Sensor 2",
                 dataPoints: sensor2Data
             },
             {
                 type: "line",
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
        $.getJSON("http://13.229.201.172/Socket-4/getsensor.php", addData);
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
            }
      
            if(data[i].sensorName == 'SENSOR 2'){
		        sensor2Data.push({
                    x: new Date(data[i].Date), 
                    y: Number(data[i].sensorValue)
		        });
            }

            if(data[i].sensorName == 'SENSOR 3'){
		        sensor3Data.push({
                    x: new Date(data[i].Date), 
                    y: Number(data[i].sensorValue)
		        });
            }
	    }
	    chart.render();
    }

    $.getJSON("http://13.229.201.172/Socket-4/getsensor.php", addData);
      
    }
     </script>
     </head>
     <body>
     <div id="chartContainer" style="height: 370px; width: 100%;"></div>
     <script src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
     <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
     </body>
     </html>                              
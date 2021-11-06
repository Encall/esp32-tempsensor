<?php
include_once('esp-database.php');
if ($_GET["readingsCount"]) {
    $data = $_GET["readingsCount"];
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $readings_count = $_GET["readingsCount"];
}
// default readings count set to 20
else {
    $readings_count = 20;
}

if ($_GET["locationdata"]) {
    $data = $_GET["locationdata"];
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $location_data = $_GET["locationdata"];
}
// default readings count set to 20
else {
    $location_data = "Freezer1";
}

$last_reading = getLastReadings($location_data);
$last_reading_temp = $last_reading["value1"];
$last_reading_humi = $last_reading["value2"];
$last_reading_time = $last_reading["reading_time"];

// Uncomment to set timezone to - 1 hour (you can change 1 to any number)
//$last_reading_time = date("Y-m-d H:i:s", strtotime("$last_reading_time - 1 hours"));
// Uncomment to set timezone to + 7 hours (you can change 7 to any number)
//$last_reading_time = date("Y-m-d H:i:s", strtotime("$last_reading_time + 7 hours"));

$min_temp = minReading($readings_count, 'value1', $location_data);
$max_temp = maxReading($readings_count, 'value1', $location_data);
$avg_temp = avgReading($readings_count, 'value1', $location_data);

$min_humi = minReading($readings_count, 'value2', $location_data);
$max_humi = maxReading($readings_count, 'value2', $location_data);
$avg_humi = avgReading($readings_count, 'value2', $location_data);
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="esp-style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<header class="header">
    <h1> NKP Sensor Data</h1>
    <h2> NODE : <?php echo $location_data; ?></h2>
    <form id="form1" method="get">
        <input type="number" name="readingsCount" min="1" placeholder="Number of readings value= (<?php echo $readings_count; ?>)">
        Node :
        <?php
        include_once('esp-database.php');
        $mysqli = new mysqli($servername, $username, $password, $dbname);

        if ($mysqli->connect_error)
            die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());


        $query = "SELECT DISTINCT location FROM SensorData";
        $result = mysqli_query($mysqli, $query);

        ?>
        <select name="locationdata">
            <?php
            while ($row = mysqli_fetch_array($result)) {
                echo "<option value='" . $row['location'] . "'>" . $row['location'] . "</option>";
            }
            ?>
        </select>

        <input type="submit" value="submit">
    </form>
</header>

<body>
    <p>Last reading: <?php echo $last_reading_time; ?></p>
    <section class="content">
        <div class="box gauge--1">
            <h3>TEMPERATURE</h3>
            <div class="mask">
                <div class="semi-circle"></div>
                <div class="semi-circle--mask"></div>
            </div>
            <p style="font-size: 30px;" id="temp">--</p>
            <table cellspacing="5" cellpadding="5">
                <tr>
                    <th colspan="3">Temperature <?php echo $readings_count; ?> readings</th>
                </tr>
                <tr>
                    <td>Min</td>
                    <td>Max</td>
                    <td>Average</td>
                </tr>
                <tr>
                    <td><?php echo $min_temp['min_amount']; ?> &deg;C</td>
                    <td><?php echo $max_temp['max_amount']; ?> &deg;C</td>
                    <td><?php echo round($avg_temp['avg_amount'], 2); ?> &deg;C</td>
                </tr>
            </table>
        </div>
        <div class="box gauge--2">
            <h3>HUMIDITY</h3>
            <div class="mask">
                <div class="semi-circle"></div>
                <div class="semi-circle--mask"></div>
            </div>
            <p style="font-size: 30px;" id="humi">--</p>
            <table cellspacing="5" cellpadding="5">
                <tr>
                    <th colspan="3">Humidity <?php echo $readings_count; ?> readings</th>
                </tr>
                <tr>
                    <td>Min</td>
                    <td>Max</td>
                    <td>Average</td>
                </tr>
                <tr>
                    <td><?php echo $min_humi['min_amount']; ?> %</td>
                    <td><?php echo $max_humi['max_amount']; ?> %</td>
                    <td><?php echo round($avg_humi['avg_amount'], 2); ?> %</td>
                </tr>
            </table>
        </div>
    </section>
    <?php
    echo   '<h2> View Latest ' . $readings_count . ' Readings</h2>
            <table cellspacing="5" cellpadding="5" id="tableReadings">
                <tr>
                    <th>ID</th>
                    <th>Sensor</th>
                    <th>Location</th>
                    <th>Value 1</th>
                    <th>Value 2</th>
                    <th>Value 3</th>
                    <th>Timestamp</th>
                </tr>';

    $result = getAllReadings($readings_count, $location_data);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row_id = $row["id"];
            $row_sensor = $row["sensor"];
            $row_location = $row["location"];
            $row_value1 = $row["value1"];
            $row_value2 = $row["value2"];
            $row_value3 = $row["value3"];
            $row_reading_time = $row["reading_time"];
            // Uncomment to set timezone to - 1 hour (you can change 1 to any number)
            //$row_reading_time = date("Y-m-d H:i:s", strtotime("$row_reading_time - 1 hours"));
            // Uncomment to set timezone to + 7 hours (you can change 7 to any number)
            //$row_reading_time = date("Y-m-d H:i:s", strtotime("$row_reading_time + 7 hours"));

            echo '<tr>
                    <td>' . $row_id . '</td>
                    <td>' . $row_sensor . '</td>
                    <td>' . $row_location . '</td>
                    <td>' . $row_value1 . '</td>
                    <td>' . $row_value2 . '</td>
                    <td>' . $row_value3 . '</td>
                    <td>' . $row_reading_time . '</td>
                  </tr>';
        }
        echo '</table>';
        $result->free();
    }
    ?>

    <script>
        var value1 = <?php echo $last_reading_temp; ?>;
        var value2 = <?php echo $last_reading_humi; ?>;
        setTemperature(value1);
        setHumidity(value2);

        function setTemperature(curVal) {
            //set range for Temperature in Celsius -5 Celsius to 38 Celsius
            var minTemp = -5.0;
            var maxTemp = 38.0;
            //set range for Temperature in Fahrenheit 23 Fahrenheit to 100 Fahrenheit
            //var minTemp = 23;
            //var maxTemp = 100;

            var newVal = scaleValue(curVal, [minTemp, maxTemp], [0, 180]);
            $('.gauge--1 .semi-circle--mask').attr({
                style: '-webkit-transform: rotate(' + newVal + 'deg);' +
                    '-moz-transform: rotate(' + newVal + 'deg);' +
                    'transform: rotate(' + newVal + 'deg);'
            });
            $("#temp").text(curVal + ' ºC');
        }

        function setHumidity(curVal) {
            //set range for Humidity percentage 0 % to 100 %
            var minHumi = 0;
            var maxHumi = 100;

            var newVal = scaleValue(curVal, [minHumi, maxHumi], [0, 180]);
            $('.gauge--2 .semi-circle--mask').attr({
                style: '-webkit-transform: rotate(' + newVal + 'deg);' +
                    '-moz-transform: rotate(' + newVal + 'deg);' +
                    'transform: rotate(' + newVal + 'deg);'
            });
            $("#humi").text(curVal + ' %');
        }

        function scaleValue(value, from, to) {
            var scale = (to[1] - to[0]) / (from[1] - from[0]);
            var capped = Math.min(from[1], Math.max(from[0], value)) - from[0];
            return ~~(capped * scale + to[0]);
        }
        $(document).ready(function() {
            function getData() {
                $.ajax({
                    type: 'POST',
                    url: 'esp-weather-station.php',
                    success: function(data) {
                        $('#output').html(data);
                    }
                });
            }
            getData();
            setInterval(function() {
                getData();
            }, 30000); // it will refresh your data every 1 sec
        });

        function change() {
            document.getElementById("form1").submit();
        }
    </script>
</body>

</html>
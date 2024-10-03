<?php
// Your OpenCage API key
$apiKey = '4482f31f6d9847bf8240c8447e0b354a';

// If this is a POST request, handle the device info submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the IP address of the client
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    // Get the POST data (device info)
    $data = json_decode(file_get_contents("php://input"), true);
    $browser = $data['browser'];
    $platform = $data['platform'];
    $time = $data['time'];
    $latitude = $data['latitude'];
    $longitude = $data['longitude'];

    // Use OpenCage Geocoder API to get a human-readable location
    $url = "https://api.opencagedata.com/geocode/v1/json?q=$latitude+$longitude&key=$apiKey";
    $response = file_get_contents($url);
    $geoData = json_decode($response, true);

    // Extract the formatted location from the API response
    $location = $geoData['results'][0]['formatted'];

    // Prepare the data to write with a better format
    $logEntry = "-----------------------------\n";
    $logEntry .= "IP Address: $ipAddress\n";
    $logEntry .= "Browser: $browser\n";
    $logEntry .= "Platform: $platform\n";
    $logEntry .= "Latitude: $latitude\n";
    $logEntry .= "Longitude: $longitude\n";
    $logEntry .= "Location: $location\n";
    $logEntry .= "Timestamp: $time\n";
    $logEntry .= "-----------------------------\n\n";

    // Save the data to a log file
    file_put_contents('log.txt', $logEntry, FILE_APPEND);

    // Respond with the captured data in JSON format
    $response = [
        'ipAddress' => $ipAddress,
        'browser' => $browser,
        'platform' => $platform,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'location' => $location,
        'time' => $time
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capture Device Info and Location</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            background-color: #0056b3;
        }
        #device-info {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            display: none;
        }
        h1 {
            color: #333;
        }
        p {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Capture Device Info and Location</h1>
        <p>Click the button below to capture your device information and location:</p>
        <button onclick="captureDeviceInfo()">Capture Info</button>

        <!-- Area to display captured information -->
        <div id="device-info"></div>
    </div>

    <script>
        function captureDeviceInfo() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const deviceInfo = {
                        browser: navigator.userAgent,
                        platform: navigator.platform,
                        time: new Date().toISOString(),
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude
                    };

                    // Send the data to PHP
                    fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(deviceInfo)
                    }).then(response => response.json())
                      .then(data => {
                          // Display the data on the screen
                          const infoDiv = document.getElementById('device-info');
                          infoDiv.style.display = 'block'; // Show the div
                          infoDiv.innerHTML = `
                            <h2>Captured Device Information</h2>
                            <p><strong>IP Address:</strong> ${data.ipAddress}</p>
                            <p><strong>Browser:</strong> ${data.browser}</p>
                            <p><strong>Platform:</strong> ${data.platform}</p>
                            <p><strong>Latitude:</strong> ${data.latitude}</p>
                            <p><strong>Longitude:</strong> ${data.longitude}</p>
                            <p><strong>Location:</strong> ${data.location}</p>
                            <p><strong>Timestamp:</strong> ${data.time}</p>
                          `;
                      });
                }, function(error) {
                    alert('Error getting location: ' + error.message);
                });
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        }
    </script>
</body>
</html>

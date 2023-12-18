<?php
// Database connection parameters
$servername = "127.0.0.1";
$username = "root";
$password = "CraajDec@2022%";
$dbname = "sample_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tables = array("users", "orders");

foreach ($tables as $table) {
    // Query to extract data from MySQL table
    $sql = "SELECT * FROM $table"; // Use the current table in the iteration
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // CSV file creation
        $file = fopen("$table.csv", 'w');

        // Fetch column names and write them to the CSV file
        $column_names = array();
        while ($row = $result->fetch_assoc()) {
            $row = array_map('utf8_decode', $row); // Optional: Handle non-ASCII characters
            if (empty($column_names)) {
                $column_names = array_keys($row);
                fputcsv($file, $column_names);
            }
            fputcsv($file, $row);
        }

        fclose($file);
    } else {
        echo "No data found for table: $table";
    }
}

$conn->close();
?>

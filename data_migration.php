<?php
$sourceDBConfig = array(
  'host' => '127.0.0.1',
  'user' => 'root',
  'password' => 'CraajDec@2022%',
  'database' => 'source_database'
);

$destDBConfig = array(
  'host' => '127.0.0.1',
  'user' => 'root',
  'password' => 'CraajDec@2022%',
  'database' => 'destination_database'  
);

$sourceConnection = new mysqli($sourceDBConfig['host'], $sourceDBConfig['user'], $sourceDBConfig['password'], $sourceDBConfig['database']);
if ($sourceConnection->connect_error) {
  die("Connection failed: " . $sourceConnection->connect_error);
}

$destConnection = new mysqli($destDBConfig['host'], $destDBConfig['user'], $destDBConfig['password'], $destDBConfig['database']);
if ($destConnection->connect_error) {
  die("Connection failed: " . $destConnection->connect_error);
}

echo "Connected to source_database!\n";

$createTableQuery = "
  CREATE TABLE IF NOT EXISTS user (
    id INT PRIMARY KEY,
    username VARCHAR(255),
    email VARCHAR(255),
    address VARCHAR(255),
    profile_info TEXT
  )
";

if ($destConnection->query($createTableQuery) === TRUE) {
  echo "Created user table in destination_database!\n";

  $result = $sourceConnection->query("SELECT * FROM user");
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $id = $row["id"];
      $username = $row["username"];
      $email = $row["email"];
      $address = $row["address"];
      $profile_info = $row["profile_info"];

      $insertQuery = "INSERT INTO user (id, username, email, address, profile_info) VALUES ($id, '$username', '$email', '$address', '$profile_info')";
      if ($destConnection->query($insertQuery) === TRUE) {
        echo "Inserted user with ID $id into destination_database\n";
      } else {
        echo "Error inserting user: " . $destConnection->error;
      }
    }
  } else {
    echo "No users to insert\n";
  }
} else {
  echo "Error creating table: " . $destConnection->error;
}

$sourceConnection->close();
$destConnection->close();
?>

<?php
// DB Setup Script
require_once 'config/db.php';

echo "Connected to MySQL successfully.\n";

// Read and execute schema
$schema = file_get_contents('database/schema.sql');
if ($schema === false) {
    die("Failed to read schema.sql\n");
}

if (mysqli_multi_query($conn, $schema)) {
    echo "Schema executed successfully.\n";
    do {
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($conn));
} else {
    die("Schema execution failed: " . mysqli_error($conn) . "\n");
}

$conn->close();
echo "DB setup complete. You can now run the server.\n";
?>


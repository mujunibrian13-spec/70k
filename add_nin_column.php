<?php
/**
 * Add NIN Column Migration Script
 * Adds NIN column to members table if it doesn't exist
 */

require_once 'config/db_config.php';

echo "<h2>Add NIN Column to Members Table</h2>";

// Check if NIN column exists
$query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'members' AND COLUMN_NAME = 'nin'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 4px;'>";
    echo "<strong style='color: #155724;'>✓ NIN column already exists in members table</strong>";
    echo "</div>";
} else {
    echo "<p>NIN column not found. Creating it...</p>";
    
    // Add NIN column
    $alter_query = "ALTER TABLE members ADD COLUMN nin VARCHAR(14) UNIQUE NULL AFTER phone";
    
    if ($conn->query($alter_query)) {
        echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 4px;'>";
        echo "<strong style='color: #155724;'>✓ NIN column added successfully!</strong><br>";
        echo "Column: <code>nin</code> (VARCHAR(14), UNIQUE)<br>";
        echo "Position: After <code>phone</code> column";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 4px;'>";
        echo "<strong style='color: #721c24;'>✗ Error adding NIN column:</strong><br>";
        echo htmlspecialchars($conn->error);
        echo "</div>";
    }
}

// Show current members table structure
echo "<h3>Current Members Table Structure</h3>";
$show_query = "DESCRIBE members";
$show_result = $conn->query($show_query);

if ($show_result) {
    echo "<table style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left;'>Field</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left;'>Type</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left;'>Null</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left;'>Key</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left;'>Default</th>";
    echo "</tr>";
    
    while ($row = $show_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'><code>" . htmlspecialchars($row['Field']) . "</code></td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . $row['Null'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . ($row['Key'] ?: '-') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . ($row['Default'] ?: '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>Next Steps</h3>";
echo "<ol>";
echo "<li>Members can now register with their 14-digit NIN</li>";
echo "<li>The NIN field is unique - each member can only use one NIN</li>";
echo "<li><a href='register.php'><strong>Go to Registration Page</strong></a></li>";
echo "</ol>";

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 40px;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        h2 { 
            color: #1e40af; 
            border-bottom: 3px solid #1e40af;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        h3 {
            color: #555;
            margin-top: 20px;
        }
        p { 
            line-height: 1.8;
            color: #333;
        }
        code { 
            background: #f5f5f5; 
            padding: 4px 8px; 
            border-radius: 3px; 
            font-family: 'Courier New', monospace;
            color: #1e40af;
        }
        table {
            background: white;
        }
        th, td {
            text-align: left;
        }
        a {
            color: #1e40af;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
</div>
</body>
</html>

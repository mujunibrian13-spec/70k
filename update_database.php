<?php
/**
 * Database Update Script
 * Adds NIN column to existing members table
 */

require_once 'config/db_config.php';

echo "<h2>Database Update - Add NIN Column</h2>";
echo "<p>This will update your existing database to include the NIN (National ID Number) field.</p>";

// Check if NIN column already exists
$check_query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_NAME = 'members' AND COLUMN_NAME = 'nin'";
$check_result = $conn->query($check_query);

if ($check_result && $check_result->num_rows > 0) {
    echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 4px; border-left: 4px solid #28a745;'>";
    echo "<strong style='color: #155724; font-size: 16px;'>✓ NIN column already exists</strong><br>";
    echo "Your database is already up to date!";
    echo "</div>";
} else {
    echo "<p>Adding NIN column to members table...</p>";
    
    // Add NIN column
    $alter_query = "ALTER TABLE members ADD COLUMN nin VARCHAR(14) UNIQUE NULL AFTER phone";
    
    if ($conn->query($alter_query)) {
        echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 4px; border-left: 4px solid #28a745;'>";
        echo "<strong style='color: #155724; font-size: 16px;'>✓ NIN column added successfully!</strong><br><br>";
        echo "<p><strong>Changes made:</strong></p>";
        echo "<ul>";
        echo "<li>Column name: <code>nin</code></li>";
        echo "<li>Data type: <code>VARCHAR(14)</code></li>";
        echo "<li>Constraint: <code>UNIQUE</code> - Each member can only use one NIN</li>";
        echo "<li>Position: After <code>phone</code> column</li>";
        echo "</ul>";
        echo "</div>";
        
        // Add index for NIN
        $index_query = "CREATE INDEX idx_nin ON members(nin)";
        if ($conn->query($index_query)) {
            echo "<div style='background: #cce5ff; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
            echo "✓ Index created on NIN column for better performance";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 4px; border-left: 4px solid #dc3545;'>";
        echo "<strong style='color: #721c24;'>✗ Error adding NIN column:</strong><br>";
        echo htmlspecialchars($conn->error);
        echo "</div>";
    }
}

// Show current members table structure
echo "<h3>Current Members Table Structure</h3>";
$describe_query = "DESCRIBE members";
$describe_result = $conn->query($describe_query);

if ($describe_result) {
    echo "<table style='border-collapse: collapse; width: 100%; margin: 15px 0; background: white;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left; font-weight: bold;'>Field</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left; font-weight: bold;'>Type</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left; font-weight: bold;'>Null</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left; font-weight: bold;'>Key</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left; font-weight: bold;'>Default</th>";
    echo "</tr>";
    
    while ($row = $describe_result->fetch_assoc()) {
        $is_nin = ($row['Field'] === 'nin') ? ' style="background: #fff3cd;"' : '';
        echo "<tr$is_nin>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'><code>" . htmlspecialchars($row['Field']) . "</code>";
        if ($row['Field'] === 'nin') echo " <strong>← NEW</strong>";
        echo "</td>";
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
echo "<li>Members can now <a href='register.php' style='color: #1e40af; font-weight: bold;'>register with their 14-digit NIN</a></li>";
echo "<li>The NIN field is unique - prevents duplicate NINs</li>";
echo "<li>NIN is required during registration</li>";
echo "<li>Existing members without NIN will need to update their profiles</li>";
echo "</ol>";

echo "<h3>SQL Commands (Manual Update)</h3>";
echo "<p>If you prefer to update manually, run these SQL commands:</p>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;'><code>";
echo "ALTER TABLE members ADD COLUMN nin VARCHAR(14) UNIQUE NULL AFTER phone;\n";
echo "CREATE INDEX idx_nin ON members(nin);";
echo "</code></pre>";

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
            max-width: 900px;
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
            margin-top: 25px;
            margin-bottom: 15px;
        }
        p { 
            line-height: 1.8;
            color: #333;
            margin: 10px 0;
        }
        code { 
            background: #f5f5f5; 
            padding: 4px 8px; 
            border-radius: 3px; 
            font-family: 'Courier New', monospace;
            color: #d63384;
        }
        pre {
            font-size: 13px;
            line-height: 1.4;
        }
        a {
            color: #1e40af;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        ul, ol {
            margin: 10px 0 10px 20px;
        }
        li {
            margin: 8px 0;
        }
        table {
            background: white;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
<div class="container">
</div>
</body>
</html>

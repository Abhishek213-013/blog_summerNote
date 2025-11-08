<?php
echo "<h1>Web Server GD Test</h1>";

// Test GD in web context
if (extension_loaded('gd')) {
    echo "✅ Web Server: GD is LOADED<br>";
    $gd_info = gd_info();
    echo "GD Version: " . $gd_info['GD Version'] . "<br>";
} else {
    echo "❌ Web Server: GD is NOT LOADED<br>";
}

echo "PHP Version: " . PHP_VERSION . "<br>";
echo "php.ini: " . php_ini_loaded_file() . "<br>";
?>
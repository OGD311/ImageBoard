<?php

use Dotenv\Dotenv;

require dirname(__DIR__, 1) . "/vendor/autoload.php";

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

try {

    $mysqli = new mysqli(
        hostname: $_ENV['DB_HOST'],
        username: $_ENV['DB_USERNAME'],
        password: $_ENV['DB_PASSWORD'],
        database: $_ENV['DB_DATABASE'],
        port: (int) $_ENV['DB_PORT'] ?? 3306
    );

    if ($mysqli->connect_errno) {
        die("Connection error: " . $mysqli->connect_error);
    }

} catch (Exception) {
    $mysqli = new mysqli(
        hostname: $_ENV['DB_HOST'], 
        username: $_ENV['DB_USERNAME'], 
        password: $_ENV['DB_PASSWORD'],
        port: (int) $_ENV['DB_PORT'] ?? 3306
    );
    
    if ($mysqli->connect_errno) {
        die("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
    }
    
    $sqlFile = __DIR__ . '/imageboard-tester-db.sql'; 
    $sqlContent = str_replace('imageboard-tester-db', $_ENV['DB_DATABASE'], file_get_contents($sqlFile));
 
    
    // Remove any comments and empty lines
    $sqlContent = preg_replace('/(--.*)|(#.*)|(\s*[\r\n]+\s*)/', '', $sqlContent);
    $sqlContent = trim($sqlContent);
    
    // Split the SQL content into individual queries
    $sqlQueries = array_filter(explode(';', $sqlContent));
    
    // Start transaction
    $mysqli->begin_transaction();
    
    try {
        foreach ($sqlQueries as $query) {
            $query = trim($query);
            
            if (!empty($query)) {
                // Ensure each query ends with a semicolon for proper execution
                if (substr($query, -1) !== ';') {
                    $query .= ';';
                }
    
                if (!$mysqli->query($query)) {
                    throw new Exception("Error executing query: " . $query . "\nMySQL error: (" . $mysqli->errno . ") " . $mysqli->error);
                }
            }
        }
    
        // Commit transaction if all queries succeed
        $mysqli->commit();
    } catch (Exception $e) {
        // Rollback transaction if an error occurs
        $mysqli->rollback();
        echo "Transaction rolled back due to error: " . $e->getMessage() . "\n";
    }

    $sqlFile = __DIR__ . '/triggers-db.sql'; 
    $sqlContent =  file_get_contents($sqlFile) ;

    

    $sqlTriggers = array_map(function($trigger) {
        return trim($trigger) . ' END;';  
    }, explode('END;', trim($sqlContent)));
     
    $sqlTriggers = array_filter($sqlTriggers);
     
    var_dump($sqlTriggers); 

    foreach ($sqlTriggers as $trigger) {
        if ($trigger != 'END;') {
            if ($mysqli->query($trigger) === TRUE) {
                echo "Trigger created successfully.\n";
            } else {
                echo "Error creating trigger: " . $mysqli->error . "\n";
            }
        }
    }
  
    $mysqli->close();
    
    $mysqli = new mysqli(hostname: $_ENV['DB_HOST'], username: $_ENV['DB_USERNAME'], password: $_ENV['DB_PASSWORD'], database: $_ENV['DB_DATABASE']);

    echo '<br><br><br> Database successfully created! Please refresh the page and check on your PhpMyAdmin that everything is there.';
}

return $mysqli;
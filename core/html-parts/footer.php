<?php

if (! isset($mysqli)) {
    $mysqli =  require __DIR__ . "/storage/database.php";
}
$mysqliStatistics = mysqli_get_connection_stats($mysqli);
$result = $mysqli->query('SHOW GLOBAL STATUS;', MYSQLI_USE_RESULT);

while ($row = $result->fetch_assoc()) {

    $statistics[$row['Variable_name']] = $row['Value'];

}
 
$result->close();
$mysqli->close();

$statistics = array_merge($statistics, $mysqliStatistics);

echo <<<EOF
<br>
<div>
    <span>
        Total Queries: {$statistics['Questions']}
        Bytes S/R: {$statistics['bytes_sent']} / {$statistics['bytes_received']} 
        Total Connections: {$statistics['Connections']}
        Slow Queries: {$statistics['Slow_queries']}
    </span>
</div>
EOF;

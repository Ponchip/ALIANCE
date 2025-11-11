<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

function exportData($format = 'json') {
    $historyFile = __DIR__ . '/data_history.json';
    
    if (!file_exists($historyFile)) {
        return ['error' => 'No data available'];
    }
    
    $data = json_decode(file_get_contents($historyFile), true);
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="graph_data.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Timestamp', 'Time', 'RPM', 'Temperature']);
        
        foreach ($data as $point) {
            fputcsv($output, [
                $point['timestamp'],
                $point['time'],
                $point['rpm'],
                $point['temp']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    // По умолчанию возвращаем JSON
    return $data;
}

// Получаем параметры экспорта
$format = isset($_GET['format']) ? $_GET['format'] : 'json';

echo json_encode(exportData($format), JSON_PRETTY_PRINT);
?>
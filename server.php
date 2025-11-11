<?php
header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Включение лога ошибок
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Функция для генерации реалистичных тестовых данных с монотонным увеличением оборотов
class DataGenerator {
    private $lastRpm = 500;
    private $lastTemp = 20;
    private $trend = 1; // 1 для роста, -1 для падения
    private $dataCounter = 0;
    
    public function generateData() {
        $this->dataCounter++;
        
        // Генерируем плавное изменение оборотов с тенденцией к росту
        $baseChange = rand(10, 50);
        $randomFactor = rand(-20, 30);
        $rpmChange = ($baseChange + $randomFactor) * $this->trend;
        
        $this->lastRpm += $rpmChange;
        
        // Ограничиваем диапазон оборотов и обеспечиваем монотонный рост
        $this->lastRpm = max(500, min(10000, $this->lastRpm));
        
        // Периодически меняем тенденцию, но чаще в сторону роста
        if (rand(1, 100) > 85) {
            $this->trend = rand(0, 1) ? 1 : -1;
        }
        
        // Температура зависит от оборотов с инерцией
        $targetTemp = 20 + ($this->lastRpm - 500) / (10000 - 500) * 80;
        $tempChange = ($targetTemp - $this->lastTemp) * 0.1 + rand(-3, 3);
        $this->lastTemp += $tempChange;
        
        // Ограничиваем температуру
        $this->lastTemp = max(0, min(120, $this->lastTemp));
        
        return [
            'rpm' => round($this->lastRpm),
            'temp' => round($this->lastTemp, 1),
            'counter' => $this->dataCounter
        ];
    }
}

// Создаем генератор данных (сохраняем состояние между запросами)
session_start();
if (!isset($_SESSION['dataGenerator'])) {
    $_SESSION['dataGenerator'] = new DataGenerator();
}
$generator = $_SESSION['dataGenerator'];

// Функция для сохранения данных в файл (для отладки)
function logData($rpm, $temp) {
    $logFile = __DIR__ . '/data_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] RPM: $rpm, Temp: $temp\n";
    
    // Сохраняем только последние 1000 записей
    if (file_exists($logFile)) {
        $lines = file($logFile);
        if (count($lines) > 1000) {
            array_shift($lines);
        }
        file_put_contents($logFile, implode('', $lines) . $logEntry);
    } else {
        file_put_contents($logFile, $logEntry);
    }
}

// Функция для чтения истории данных
function getHistoryData($limit = 100) {
    $historyFile = __DIR__ . '/data_history.json';
    
    if (file_exists($historyFile)) {
        $data = json_decode(file_get_contents($historyFile), true);
        return array_slice($data, -$limit);
    }
    
    return [];
}

// Функция для сохранения данных в историю
function saveToHistory($rpm, $temp, $counter) {
    $historyFile = __DIR__ . '/data_history.json';
    $timestamp = time();
    
    $dataPoint = [
        'timestamp' => $timestamp,
        'time' => date('H:i:s', $timestamp),
        'rpm' => $rpm,
        'temp' => $temp,
        'counter' => $counter
    ];
    
    $history = [];
    if (file_exists($historyFile)) {
        $history = json_decode(file_get_contents($historyFile), true);
    }
    
    $history[] = $dataPoint;
    
    // Сохраняем только последние 5000 точек
    if (count($history) > 5000) {
        $history = array_slice($history, -5000);
    }
    
    file_put_contents($historyFile, json_encode($history));
}

// Обработка GET запроса (основные данные)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = $generator->generateData();
    
    // Логируем данные
    logData($data['rpm'], $data['temp']);
    
    // Сохраняем в историю
    saveToHistory($data['rpm'], $data['temp'], $data['counter']);
    
    echo $data['rpm'] . ',' . $data['temp'];
    exit;
}

// Обработка POST запроса для получения истории
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true);
    
    if (isset($requestData['action'])) {
        switch ($requestData['action']) {
            case 'get_history':
                $limit = isset($requestData['limit']) ? intval($requestData['limit']) : 100;
                $history = getHistoryData($limit);
                echo json_encode($history);
                break;
                
            case 'get_stats':
                $history = getHistoryData(1000);
                $rpms = array_column($history, 'rpm');
                $temps = array_column($history, 'temp');
                
                $stats = [
                    'total_points' => count($history),
                    'rpm_avg' => count($rpms) ? round(array_sum($rpms) / count($rpms)) : 0,
                    'temp_avg' => count($temps) ? round(array_sum($temps) / count($temps), 1) : 0,
                    'rpm_max' => count($rpms) ? max($rpms) : 0,
                    'temp_max' => count($temps) ? max($temps) : 0
                ];
                
                echo json_encode($stats);
                break;
                
            default:
                // Возвращаем текущие данные по умолчанию
                $data = $generator->generateData();
                echo $data['rpm'] . ',' . $data['temp'];
        }
    } else {
        // По умолчанию возвращаем текущие данные
        $data = $generator->generateData();
        echo $data['rpm'] . ',' . $data['temp'];
    }
    exit;
}

// Если метод не поддерживается
http_response_code(405);
echo "Method not allowed";
?>
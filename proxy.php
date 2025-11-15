<?php
// test_proxy.php - Диагностика прокси

$proxyHost = 'res.geonix.com';
$proxyPort = 10000;
$proxyUser = '182bb865f406dbc6';
$proxyPass = 'bIfaznji';

echo "<h2>Тест прокси-сервера</h2>";
echo "<pre>";

// Проверка 1: Доступен ли хост
echo "1. Проверка DNS для $proxyHost...\n";
$ip = gethostbyname($proxyHost);
if ($ip === $proxyHost) {
    echo "   ❌ ОШИБКА: Не удалось разрешить DNS\n";
} else {
    echo "   ✅ IP адрес: $ip\n";
}

// Проверка 2: Доступен ли порт
echo "\n2. Проверка подключения к $proxyHost:$proxyPort...\n";
$connection = @fsockopen($proxyHost, $proxyPort, $errno, $errstr, 5);
if (!$connection) {
    echo "   ❌ ОШИБКА: $errstr ($errno)\n";
} else {
    echo "   ✅ Порт доступен\n";
    fclose($connection);
}

// Проверка 3: Включен ли cURL
echo "\n3. Проверка cURL...\n";
if (function_exists('curl_version')) {
    $version = curl_version();
    echo "   ✅ cURL версия: " . $version['version'] . "\n";
    echo "   Протоколы: " . implode(', ', $version['protocols']) . "\n";
} else {
    echo "   ❌ cURL не установлен\n";
}

// Проверка 4: Попытка подключения через прокси
echo "\n4. Тест подключения через прокси...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.ipify.org?format=json',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_PROXY => $proxyHost . ':' . $proxyPort,
    CURLOPT_PROXYUSERPWD => $proxyUser . ':' . $proxyPass,
    CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_VERBOSE => true
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$info = curl_getinfo($ch);

if ($error) {
    echo "   ❌ ОШИБКА: $error\n";
    echo "   Код ошибки: " . curl_errno($ch) . "\n";
} else {
    echo "   ✅ УСПЕШНО!\n";
    echo "   Ответ: $response\n";
}

echo "\n5. Информация о запросе:\n";
echo "   HTTP код: " . $info['http_code'] . "\n";
echo "   Время подключения: " . $info['connect_time'] . " сек\n";
echo "   Общее время: " . $info['total_time'] . " сек\n";

curl_close($ch);

// Проверка 5: Тест без прокси (для сравнения)
echo "\n6. Тест БЕЗ прокси (напрямую)...\n";
$ch2 = curl_init();
curl_setopt_array($ch2, [
    CURLOPT_URL => 'https://api.ipify.org?format=json',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response2 = curl_exec($ch2);
$error2 = curl_error($ch2);

if ($error2) {
    echo "   ❌ ОШИБКА: $error2\n";
} else {
    echo "   ✅ УСПЕШНО!\n";
    echo "   Ваш IP (без прокси): $response2\n";
}

curl_close($ch2);

echo "</pre>";
?>

<?php
$BOT_TOKEN = '8989976718:AAFBWh90mRnw6VOYrjrMDhUl2TfTApELsHA';
$CHAT_ID   = '8763088511';

echo "=== TEST PHP ===<br>";
echo "PHP version: " . phpversion() . "<br>";
echo "curl: " . (function_exists('curl_init') ? 'ON' : 'OFF') . "<br>";
echo "allow_url_fopen: " . ini_get('allow_url_fopen') . "<br>";
echo "openssl: " . (extension_loaded('openssl') ? 'ON' : 'OFF') . "<br><br>";

$url = "https://api.telegram.org/bot$BOT_TOKEN/sendMessage";
$params = ['chat_id' => $CHAT_ID, 'text' => 'TEST dari server: ' . date('Y-m-d H:i:s')];

echo "=== KIRIM TEST ===<br>";
if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    echo "curl result: <pre>" . htmlspecialchars($res) . "</pre>";
    echo "curl error: " . htmlspecialchars($err) . "<br>";
} else {
    $res = @file_get_contents($url . '?' . http_build_query($params));
    echo "fgc result: <pre>" . htmlspecialchars($res) . "</pre>";
}
?>

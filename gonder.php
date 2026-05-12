<?php
$bot_token = "8840723831:AAFTK85plsprSI2FZ6ggIJy5f6WZfiu3RNE";
$chat_ids = [
    "7975374182",  // 1. hesap
    "6671499665"   // 2. hesap
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Sadece POST kabul edilir']);
    exit;
}

$öneri_text = isset($_POST['öneri']) ? trim($_POST['öneri']) : '';
if (empty($öneri_text)) {
    echo json_encode(['success' => false, 'error' => 'Öneri • İstek metni boş olamaz!']);
    exit;
}

$message = "🆕 YENİ İTİRAF\n\n📝 " . $öneri_text . "\n\n🕐 " . date('d.m.Y H:i');

// Tüm hesaplara metni gönder
foreach ($chat_ids as $chat_id) {
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $data = ['chat_id' => $chat_id, 'text' => $message];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_exec($ch);
    curl_close($ch);
}

// Medya varsa tüm hesaplara gönder
if (isset($_FILES['media']) && $_FILES['media']['error'] === 0) {
    
    $file_tmp = $_FILES['media']['tmp_name'];
    $file_name = $_FILES['media']['name'];
    $file_type = $_FILES['media']['type'];
    $file_size = $_FILES['media']['size'];
    
    if ($file_size > 100 * 1024 * 1024) {
        echo json_encode(['success' => true, 'warning' => 'Dosya 100MB üzeri']);
        exit;
    }
    
    if (strpos($file_type, 'video') !== false) {
        $media_url = "https://api.telegram.org/bot{$bot_token}/sendVideo";
        $field = 'video';
    } else {
        $media_url = "https://api.telegram.org/bot{$bot_token}/sendDocument";
        $field = 'document';
    }
    
    // Tüm hesaplara medyayı gönder
    foreach ($chat_ids as $chat_id) {
        $post_fields = [
            'chat_id' => $chat_id,
            $field => new CURLFile($file_tmp, $file_type, $file_name)
        ];
        
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $media_url);
        curl_setopt($ch2, CURLOPT_POST, 1);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch2, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_exec($ch2);
        curl_close($ch2);
    }
}

echo json_encode(['success' => true]);
?>

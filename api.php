<?php
// api.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$url = "https://api.allorigins.win/get?url=" . urlencode("https://lc79md5.vercel.app/lc79/md5");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    $outer_data = json_decode($response, true);
    // Giải mã chuỗi JSON nằm trong trường 'contents'
    if (isset($outer_data['contents'])) {
        echo $outer_data['contents']; 
    } else {
        echo json_encode(["error" => "Data format error"]);
    }
} else {
    echo json_encode(["error" => "Cannot connect to server"]);
}
?>

<?php
// api.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // Cho phép gọi từ mọi nơi (cấu hình lại nếu cần bảo mật)

$url = "https://lc79md5.vercel.app/lc79/md5";

// Sử dụng cURL để lấy dữ liệu (ổn định hơn file_get_contents)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bỏ qua lỗi SSL nếu có
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout sau 10s
$response = curl_exec($ch);
curl_close($ch);

// Trả về dữ liệu gốc
if ($response === false) {
    echo json_encode(["error" => "Không thể kết nối đến nguồn dữ liệu"]);
} else {
    echo $response;
}
?>

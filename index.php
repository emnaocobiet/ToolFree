<?php
// --- PHẦN BACKEND (Xử lý API) ---
if (isset($_GET['get_data'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    // Sử dụng proxy để tránh lỗi CORS và lấy dữ liệu
    $target = "https://lc79md5.vercel.app/lc79/md5";
    $url = "https://api.allorigins.win/get?url=" . urlencode($target);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $outer_data = json_decode($response, true);
        echo $outer_data['contents'] ?? json_encode(["error" => "No contents"]);
    } else {
        echo json_encode(["error" => "Backend error"]);
    }
    exit; // Dừng tại đây nếu là cuộc gọi API
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LC79 All-in-One</title>
    <style>
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; background: #000; font-family: sans-serif; }
        #bg-iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; }

        #floating-box {
            position: absolute; top: 80px; left: 20px;
            width: 140px; height: 90px;
            background: #ffffff; color: #000;
            border-radius: 12px; display: flex; flex-direction: column;
            justify-content: center; align-items: center;
            cursor: move; user-select: none; z-index: 9999;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            border: 2px solid #f1c40f;
        }

        .phien-label { font-size: 11px; color: #777; }
        .phien-so { font-size: 15px; font-weight: bold; margin-bottom: 2px; }
        .du-doan { font-size: 26px; font-weight: 900; color: #e67e22; text-transform: uppercase; }
        
        .status { font-size: 24px; font-weight: bold; }
        .win { color: #27ae60; }
        .lose { color: #c0392b; }
    </style>
</head>
<body>

    <iframe id="bg-iframe" src="https://play.lc79.bet/"></iframe>

    <div id="floating-box">
        <div id="content" style="text-align: center;">Đang kết nối...</div>
    </div>

    <script>
        const box = document.getElementById('floating-box');
        const content = document.getElementById('content');
        let currentPred = null;
        let isLock = false;

        // Xử lý kéo thả
        let isDrag = false, ox, oy;
        box.onmousedown = box.ontouchstart = (e) => { 
            isDrag = true; 
            const event = e.touches ? e.touches[0] : e;
            ox = event.clientX - box.offsetLeft; 
            oy = event.clientY - box.offsetTop; 
        };
        document.onmousemove = document.ontouchmove = (e) => { 
            if(!isDrag) return; 
            const event = e.touches ? e.touches[0] : e;
            box.style.left = (event.clientX - ox) + 'px'; 
            box.style.top = (event.clientY - oy) + 'px'; 
        };
        document.onmouseup = document.ontouchend = () => isDrag = false;

        async function update() {
            if (isLock) return;
            try {
                // Gọi ngược lại chính file này với tham số get_data
                const res = await fetch('?get_data=1&t=' + Date.now());
                const data = await res.json();

                // Kiểm tra kết quả phiên cũ
                if (currentPred && data.phien === currentPred.phien_so) {
                    isLock = true;
                    const win = data.ket_qua.trim() === currentPred.result.trim();
                    content.innerHTML = `<div class="status ${win?'win':'lose'}">${win?'THẮNG':'THUA'}</div>
                                         <div style="font-size:10px">${data.ket_qua}</div>`;
                    currentPred = null;
                    setTimeout(() => { isLock = false; update(); }, 4000);
                    return;
                }

                // Hiển thị dự đoán mới
                content.innerHTML = `
                    <div class="phien-label">Phiên hiện tại</div>
                    <div class="phien-so">#${data.phien_hien_tai}</div>
                    <div class="du-doan">${data.du_doan}</div>
                `;
                currentPred = { phien_so: data.phien_hien_tai, result: data.du_doan };

            } catch (e) {
                console.log("Đang đợi dữ liệu...");
            }
        }

        setInterval(update, 3000);
        update();
    </script>
</body>
</html>

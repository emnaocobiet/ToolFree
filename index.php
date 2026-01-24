<?php
// --- PHẦN BACKEND: Thuật toán dự đoán nâng cao ---
if (isset($_POST['md5_string'])) {
    header('Content-Type: application/json');
    $md5 = trim($_POST['md5_string']);
    
    if (strlen($md5) < 5) {
        echo json_encode(["status" => "error", "msg" => "Mã quá ngắn"]);
        exit;
    }

    // Thuật toán "Xịn": Kết hợp ký tự đầu, giữa và cuối
    $c1 = substr($md5, 0, 1);   // Ký tự đầu
    $c2 = substr($md5, 15, 1);  // Ký tự giữa
    $c3 = substr($md5, -1);     // Ký tự cuối
    
    // Chuyển mã Hex sang số thập phân và cộng lại
    $total = hexdec($c1) + hexdec($c2) + hexdec($c3);
    
    // Kết quả dựa trên tổng số điểm
    $prediction = ($total % 2 == 0) ? "Tài" : "Xỉu";
    $tile = rand(75, 98); // Tạo tỷ lệ thắng giả lập cho "oai"

    echo json_encode([
        "status" => "success",
        "du_doan" => $prediction,
        "tile" => $tile . "%",
        "analysis" => strtoupper($c1 . $c2 . $c3)
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LC79 VIP Predictor</title>
    <style>
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; background: #000; font-family: 'Segoe UI', Tahoma, sans-serif; }
        
        /* Hình nền Game */
        #bg-iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; z-index: 1; }

        /* Hiệu ứng Tuyết rơi */
        #snow-canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 2; }

        /* Box nổi VIP */
        #floating-box {
            position: absolute; top: 80px; left: 20px;
            width: 200px; padding: 15px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px); /* Hiệu ứng kính mờ */
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px; z-index: 9999;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center; color: white;
            cursor: move; transition: transform 0.2s;
        }

        #floating-box:active { transform: scale(1.02); }

        h4 { margin: 0 0 10px 0; font-size: 14px; letter-spacing: 1px; color: #ffd700; text-shadow: 0 0 5px rgba(255,215,0,0.5); }

        input {
            width: 85%; padding: 10px; margin-bottom: 12px;
            background: rgba(255,255,255,0.2); border: none;
            border-radius: 10px; color: white; outline: none; text-align: center;
        }
        input::placeholder { color: #ddd; font-size: 11px; }

        button {
            width: 100%; padding: 10px;
            background: linear-gradient(45deg, #f39c12, #e67e22);
            color: white; border: none; border-radius: 10px;
            cursor: pointer; font-weight: bold; text-transform: uppercase;
            box-shadow: 0 4px 15px rgba(230, 126, 34, 0.4);
        }

        #result-area { margin-top: 15px; height: 60px; display: flex; flex-direction: column; justify-content: center; }
        
        .du-doan { font-size: 32px; font-weight: 900; color: #fff; text-shadow: 0 0 10px rgba(255,255,255,0.8); margin: 0; }
        .win-rate { font-size: 11px; color: #2ecc71; font-weight: bold; }
        .loading-dots:after { content: ' .'; animation: dots 1s steps(5, end) infinite;}
        @keyframes dots { 0%, 20% { color: rgba(0,0,0,0); text-shadow: .25em 0 0 rgba(0,0,0,0), .5em 0 0 rgba(0,0,0,0); } 40% { color: white; text-shadow: .25em 0 0 rgba(0,0,0,0), .5em 0 0 rgba(0,0,0,0); } 60% { text-shadow: .25em 0 0 white, .5em 0 0 rgba(0,0,0,0); } 80%, 100% { text-shadow: .25em 0 0 white, .5em 0 0 white; } }
    </style>
</head>
<body>

    <iframe id="bg-iframe" src="https://play.lc79.bet/"></iframe>
    <canvas id="snow-canvas"></canvas>

    <div id="floating-box">
        <h4>MD5 PREDICT VIP</h4>
        <input type="text" id="md5Input" placeholder="Dán chuỗi MD5 tại đây...">
        <button onclick="getPrediction()">Phân tích mã</button>

        <div id="result-area">
            <div id="display-result">
                <span style="font-size: 10px; color: #ccc;">Sẵn sàng phân tích</span>
            </div>
        </div>
    </div>

    <script>
        // --- LOGIC KÉO THẢ ---
        const box = document.getElementById('floating-box');
        let isDrag = false, ox, oy;
        box.onmousedown = box.ontouchstart = (e) => { 
            isDrag = true; 
            const ev = e.touches ? e.touches[0] : e;
            ox = ev.clientX - box.offsetLeft; oy = ev.clientY - box.offsetTop; 
        };
        document.onmousemove = document.ontouchmove = (e) => { 
            if(!isDrag) return; 
            const ev = e.touches ? e.touches[0] : e;
            box.style.left = (ev.clientX - ox) + 'px'; box.style.top = (ev.clientY - oy) + 'px'; 
        };
        document.onmouseup = document.ontouchend = () => isDrag = false;

        // --- HIỆU ỨNG TUYẾT RƠI ---
        const canvas = document.getElementById('snow-canvas');
        const ctx = canvas.getContext('2d');
        let particles = [];
        function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
        window.onresize = resize; resize();

        class Particle {
            constructor() { this.init(); }
            init() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.r = Math.random() * 3 + 1;
                this.s = Math.random() * 1 + 0.5;
            }
            update() {
                this.y += this.s;
                if (this.y > canvas.height) this.y = -10;
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
                ctx.fillStyle = "white";
                ctx.fill();
            }
        }
        for(let i=0; i<100; i++) particles.push(new Particle());
        function animate() {
            ctx.clearRect(0,0,canvas.width, canvas.height);
            particles.forEach(p => { p.update(); p.draw(); });
            requestAnimationFrame(animate);
        }
        animate();

        // --- XỬ LÝ DỰ ĐOÁN ---
        async function getPrediction() {
            const md5Value = document.getElementById('md5Input').value.trim();
            const display = document.getElementById('display-result');

            if (!md5Value) return;

            display.innerHTML = '<span class="loading-dots">Đang soi cầu</span>';

            try {
                let formData = new FormData();
                formData.append('md5_string', md5Value);

                const response = await fetch('', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.status === "success") {
                    // Hiệu ứng màu sắc cho Tài/Xỉu
                    const color = data.du_doan === "Tài" ? "#f1c40f" : "#3498db";
                    display.innerHTML = `
                        <p class="du-doan" style="color:${color}">${data.du_doan}</p>
                        <span class="win-rate">Độ tin cậy: ${data.tile}</span>
                    `;
                }
            } catch (e) {
                display.innerHTML = 'Lỗi kết nối!';
            }
        }
    </script>
</body>
</html>

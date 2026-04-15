<?php
// في البداية، نبدأ الجلسة للتحقق إذا كان المستخدم مسجل دخوله بالفعل
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: user_home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مرحباً بك في GlyphX</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #00d2ff;
            --secondary-color: #3a7bd5;
            --accent-color: #f76b1c;
            --text-color: #ffffff;
            --font-heading: 'Cairo', sans-serif;
            --font-body: 'Tajawal', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-body);
            background: #000;
            color: var(--text-color);
            overflow: hidden;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            perspective: 1200px;
        }

        /* Animated Background */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            background: linear-gradient(to bottom, #0f0c29, #302b63, #24243e);
        }
/* 3D Card Container */
        .card-container {
            width: 400px;
            height: 500px;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1); /* إضافة تنعيم للحركة */
        }

        /* ده السطر اللي كان ناقصه تحديد الـ Y */
        .card-container.is-flipped {
            transform: rotateY(180deg) !important; 
        }

        .card-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
        }

        .card-face-back {
            transform: rotateY(180deg);
        }

        .card-face h2 {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 10px;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Glassmorphism Inputs */
        .input-group {
            margin: 20px 0;
            position: relative;
            width: 100%;
        }

        .input-group input {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: var(--text-color);
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 15px rgba(0, 210, 255, 0.3);
        }

        /* Cosmic Button */
        .cosmic-btn {
            width: 100%;
            padding: 15px;
            margin-top: 20px;
            border: none;
            border-radius: 50px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            font-family: var(--font-heading);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(0, 210, 255, 0.4);
        }

        .cosmic-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(0, 210, 255, 0.6);
        }

        .cosmic-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .cosmic-btn:active::before {
            width: 300px;
            height: 300px;
        }

        .switch-form {
            margin-top: 30px;
        }

        .switch-form a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s;
        }

        .switch-form a:hover {
            color: var(--accent-color);
        }

        #message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 30px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 50px;
            color: white;
            font-weight: 700;
            opacity: 0;
            transition: opacity 0.5s, transform 0.5s;
            z-index: 1000;
        }

       document.addEventListener('mousemove', (e) => {
            // لو الكارت لافف، ميعملش Tilt عشان ميبوظش المنظر
            if(card.classList.contains('is-flipped')) return; 
            
            const xAxis = (window.innerWidth / 2 - e.pageX) / 25;
            const yAxis = (window.innerHeight / 2 - e.pageY) / 25;
            card.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
        });
        .card-container.is-flipped {
    transform: rotateY(180deg) !important;
}

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-container { width: 90%; height: auto; min-height: 500px; }
            .card-face { padding: 30px 20px; }
        }
    </style>
</head>
<body>

    <div id="particles-js"></div>

    <div class="card-container" id="cardContainer">
        <!-- Login Form (Front of card) -->
        <div class="card-face card-face-front">
            <h2>أهلاً بعودتك</h2>
            <p>سجل دخولك لتبدأ رحلتك</p>
            <form id="loginFormElement">
                <div class="input-group">
                    <input type="email" id="loginEmail" placeholder="البريد الإلكتروني" required>
                </div>
                <div class="input-group">
                    <input type="password" id="loginPassword" placeholder="كلمة المرور" required>
                </div>
                <button type="submit" class="cosmic-btn">دخول</button>
            </form>
            <div class="switch-form">
                <p>ليس لديك حساب؟ <a href="#" onclick="flipCard()">إنشاء حساب جديد</a></p>
            </div>
        </div>

        <!-- Register Form (Back of card) -->
        <div class="card-face card-face-back">
            <h2>انضم إلينا</h2>
            <p>أنشئ حسابك الجديد وابدأ الاستكشاف</p>
            <form id="registerFormElement">
                <div class="input-group">
                    <input type="text" id="registerName" placeholder="الاسم الكامل" required>
                </div>
                <div class="input-group">
                    <input type="email" id="registerEmail" placeholder="البريد الإلكتروني" required>
                </div>
                <div class="input-group">
                    <input type="password" id="registerPassword" placeholder="كلمة المرور" required>
                </div>
                <button type="submit" class="cosmic-btn">إنشاء حساب</button>
            </form>
            <div class="switch-form">
                <p>لديك حساب بالفعل؟ <a href="#" onclick="flipCard()">تسجيل الدخول</a></p>
            </div>
        </div>
    </div>

    <p id="message"></p>

    <!-- Particles.js Library -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    
    <script>
        const card = document.getElementById('cardContainer');
        const messageEl = document.getElementById('message');

        // Initialize Particles.js
        particlesJS('particles-js', {
            "particles": {
                "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
                "color": { "value": "#ffffff" },
                "shape": { "type": "circle" },
                "opacity": { "value": 0.5, "random": true },
                "size": { "value": 3, "random": true },
                "line_linked": { "enable": true, "distance": 150, "color": "#ffffff", "opacity": 0.4, "width": 1 },
                "move": { "enable": true, "speed": 2, "direction": "none", "random": false, "straight": false, "out_mode": "out", "bounce": false }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": { "onhover": { "enable": true, "mode": "repulse" }, "onclick": { "enable": true, "mode": "push" }, "resize": true },
                "modes": { "repulse": { "distance": 100, "duration": 0.4 }, "push": { "particles_nb": 4 } }
            },
            "retina_detect": true
        });

        // 3D Tilt Effect on Mouse Move
        document.addEventListener('mousemove', (e) => {
            const xAxis = (window.innerWidth / 2 - e.pageX) / 25;
            const yAxis = (window.innerHeight / 2 - e.pageY) / 25;
            card.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
        });

        // Flip Card
        function flipCard() {
            card.classList.toggle('is-flipped');
            hideMessage();
        }

        // Message Functions
        function showMessage(msg, isError = false) {
            messageEl.textContent = msg;
            messageEl.style.background = isError ? 'rgba(231, 76, 60, 0.9)' : 'rgba(46, 204, 113, 0.9)';
            messageEl.classList.add('show');
        }

        function hideMessage() {
            messageEl.classList.remove('show');
        }
        
        function showLoading(button) {
            button.disabled = true;
            button.innerHTML = 'جاري التحميل... <span class="spinner"></span>';
        }

        function hideLoading(button, originalText) {
            button.disabled = false;
            button.innerHTML = originalText;
        }

        // Login Form Handler
        document.getElementById('loginFormElement').addEventListener('submit', async function(e) {
            e.preventDefault();
            const button = this.querySelector('.cosmic-btn');
            showLoading(button);

            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            try {
                const response = await fetch('signin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                const result = await response.json();

                if (response.ok && result.success) {
                    showMessage('تم تسجيل الدخول بنجاح! جاري التحويل...');
                    setTimeout(() => window.location.href = 'user_home.php', 2000);
                } else {
                    hideLoading(button, 'دخول');
                    showMessage(result.error || 'حدث خطأ ما.', true);
                }
            } catch (error) {
                hideLoading(button, 'دخول');
                showMessage('فشل الاتصال بالخادم.', true);
            }
        });

        // Register Form Handler
        document.getElementById('registerFormElement').addEventListener('submit', async function(e) {
            e.preventDefault();
            const button = this.querySelector('.cosmic-btn');
            showLoading(button);

            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;

            try {
                const response = await fetch('signin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, email, password })
                });
                const result = await response.json();

                hideLoading(button, 'إنشاء حساب');
                if (response.ok && result.success) {
                    showMessage('تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.');
                    setTimeout(flipCard, 2000);
                } else {
                    showMessage(result.error || 'حدث خطأ ما.', true);
                }
            } catch (error) {
                hideLoading(button, 'إنشاء حساب');
                showMessage('فشل الاتصال بالخادم.', true);
            }
        });
    </script>
</body>
</html>
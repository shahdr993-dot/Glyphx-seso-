<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $user_stmt = $pdo->prepare("SELECT full_name, profile_pic FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch();

    $visits_stmt = $pdo->prepare("SELECT v.*, p.name as place_name FROM visits v JOIN places p ON v.place_id = p.id WHERE v.user_id = ? ORDER BY v.visit_time DESC");
    $visits_stmt->execute([$user_id]);
    $all_visits = $visits_stmt->fetchAll();
} catch (PDOException $e) {
    die("خطأ تقني: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مركز التحكم | Eng. Nada</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --neon-purple: #9d50bb;
            --deep-purple: #6e48aa;
            --cyan: #00d2ff;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(157, 80, 187, 0.3);
        }

        body {
            background: #0f0c29;
            background: linear-gradient(-45deg, #0f0c29, #302b63, #24243e, #000000);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: white;
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            overflow-x: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .main-container {
            max-width: 1000px;
            margin: 40px auto;
            background: rgba(15, 12, 41, 0.7);
            border-radius: 40px;
            border: 1px solid var(--glass-border);
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            backdrop-filter: blur(20px);
            position: relative;
            animation: slideUp 1s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        /* --- تنسيق المساعد الذكي الجديد --- */
        #voice-assistant-section {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 30px;
            padding: 30px;
            margin-top: 40px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 0 20px rgba(157, 80, 187, 0.1);
        }

        .chat-box {
            max-height: 200px;
            overflow-y: auto;
            background: rgba(0,0,0,0.3);
            border-radius: 20px;
            margin-bottom: 20px;
            padding: 15px;
            text-align: right;
            border: 1px solid rgba(0, 210, 255, 0.1);
        }

        .listening-active {
            animation: listening-pulse 1.5s infinite;
            background: #ff0088 !important;
        }

        @keyframes listening-pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 0, 136, 0.7); }
            70% { box-shadow: 0 0 0 20px rgba(255, 0, 136, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 0, 136, 0); }
        }
        /* --- نهاية تنسيق المساعد --- */

        @keyframes slideUp { from { opacity: 0; transform: translateY(50px); } to { opacity: 1; transform: translateY(0); } }

        .profile-header { text-align: center; margin-bottom: 50px; }
        .avatar-wrapper { position: relative; width: 160px; margin: 0 auto 20px; }
        .avatar-img { width: 150px; height: 150px; border-radius: 50%; border: 4px solid var(--neon-purple); box-shadow: 0 0 30px var(--neon-purple); object-fit: cover; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: var(--glass); border-radius: 25px; padding: 30px; text-align: center; border: 1px solid var(--glass-border); transition: 0.4s; }
        .stat-card:hover { transform: scale(1.05); background: rgba(157, 80, 187, 0.15); border-color: var(--cyan); }

        .progress-bar { height: 12px; background: rgba(255,255,255,0.1); border-radius: 10px; margin: 15px 0; overflow: hidden; }
        .progress-fill { width: 85%; height: 100%; background: linear-gradient(90deg, var(--cyan), var(--neon-purple)); box-shadow: 0 0 15px var(--cyan); }

        .btn-action { background: linear-gradient(45deg, var(--neon-purple), var(--deep-purple)); color: white; border: none; padding: 18px 45px; border-radius: 50px; font-weight: 900; cursor: pointer; box-shadow: 0 0 20px var(--neon-purple); animation: pulse 2s infinite; }
        @keyframes pulse { 0% { transform: scale(1); } 70% { transform: scale(1.05); } 100% { transform: scale(1); } }

        .visit-item { background: rgba(255,255,255,0.03); margin-bottom: 12px; padding: 18px 25px; border-radius: 20px; border-right: 5px solid var(--neon-purple); display: flex; justify-content: space-between; }
        .edit-badge { position: absolute; bottom: 5px; right: 5px; background: var(--cyan); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 0 15px var(--cyan); }
    </style>
</head>
<body>

<div class="main-container">
    <div class="profile-header">
        <div class="avatar-wrapper">
            <img src="<?= $user['profile_pic'] ? 'uploads/'.$user['profile_pic'] : 'https://api.dicebear.com/7.x/bottts/svg?seed='.$user['full_name'] ?>" class="avatar-img">
            <label for="file-upload" class="edit-badge"><i class="fas fa-pen"></i></label>
            <form action="update_pic.php" method="POST" enctype="multipart/form-data" id="pic-form">
                <input type="file" id="file-upload" name="profile_pic" style="display:none;" onchange="document.getElementById('pic-form').submit()">
            </form>
        </div>
        <h1>Eng. <?= htmlspecialchars($user['full_name']) ?> <span style="color: var(--cyan); display: block; font-size: 0.5em;">TECHNICAL LEAD</span></h1>
        <p>أهلاً بكِ مجدداً يا ندى 🌌</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><i class="fas fa-map-marked-alt fa-2x"></i><h2><?= count($all_visits) ?></h2><p>الزيارات</p></div>
        <div class="stat-card">
            <i class="fas fa-code-branch fa-2x"></i><p>المهام</p>
            <div class="progress-bar"><div class="progress-fill"></div></div>
            <small>85% COMPLETED</small>
        </div>
    </div>

    <div id="voice-assistant-section">
        <h3 style="color: var(--cyan); text-align: center;"><i class="fas fa-robot"></i> مساعد GlyphX الذكي</h3>
        <div id="assistantMessages" class="chat-box">
            <p style="color: gray; font-size: 0.8rem;">جاهز لسماع أوامرك يا ندى...</p>
        </div>
        <div style="display: flex; gap: 10px; justify-content: center; align-items: center;">
            <input type="text" id="textInput" placeholder="اكتبي سؤالك هنا..." style="background: var(--glass); border: 1px solid var(--glass-border); color: white; padding: 12px; border-radius: 25px; width: 70%;">
            <button id="voiceInputBtn" class="edit-badge" style="position: static; width: 50px; height: 50px;">
                <i class="fas fa-microphone"></i>
            </button>
            <button id="sendTextBtn" style="background: var(--neon-purple); border: none; border-radius: 50%; width: 50px; height: 50px; color: white; cursor: pointer;">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <div style="margin-top: 40px;">
        <h3><i class="fas fa-history"></i> آخر الزيارات:</h3>
        <?php foreach(array_slice($all_visits, 0, 3) as $v): ?>
            <div class="visit-item">
                <span><i class="fas fa-location-dot" style="color: var(--cyan);"></i> <?= htmlspecialchars($v['place_name']) ?></span>
                <span style="opacity: 0.6; font-size: 0.8rem;"><?= date('Y/m/d', strtotime($v['visit_time'])) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<input type="hidden" id="current-user-id" value="<?= $user_id ?>">

<script src="voice.js"></script>

</body>
</html>
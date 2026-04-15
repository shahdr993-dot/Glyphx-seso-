<?php
require_once 'db_config.php';

class AIBridge {
    private $pythonScriptPath;
    
    public function __construct($pythonScriptPath = 'ai_core.py') {
        $this->pythonScriptPath = $pythonScriptPath;
    }
    
    public function generateNarration($placeId, $language = 'ar') {
        try {
            // جلب معلومات المكان من قاعدة البيانات
            $stmt = $pdo->prepare("SELECT name, description FROM places WHERE id = ?");
            $stmt->execute([$placeId]);
            $place = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$place) {
                return ['error' => 'المكان غير موجود'];
            }
            
            // استدعاء سكربت Python
            $command = "python {$this->pythonScriptPath} --action narration --place \"" . $place['name'] . "\" --info \"" . $place['description'] . "\" --lang {$language}";
            $output = shell_exec($command);
            
            // تحليل النتيجة
            $result = json_decode($output, true);
            
            if (!$result) {
                return ['error' => 'فشل في تحليل نتيجة الذكاء الاصطناعي'];
            }
            
            // حفظ النتيجة في قاعدة البيانات
            $stmt = $pdo->prepare("UPDATE places SET narration = ? WHERE id = ?");
            $stmt->execute([$result['text'], $placeId]);
            
            return ['success' => true, 'narration' => $result['text']];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function generateQuiz($placeId, $difficulty = 'medium') {
        try {
            // جلب معلومات المكان من قاعدة البيانات
            $stmt = $pdo->prepare("SELECT name FROM places WHERE id = ?");
            $stmt->execute([$placeId]);
            $place = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$place) {
                return ['error' => 'المكان غير موجود'];
            }
            
            // استدعاء سكربت Python
            $command = "python {$this->pythonScriptPath} --action quiz --place \"" . $place['name'] . "\" --difficulty {$difficulty}";
            $output = shell_exec($command);
            
            // تحليل النتيجة
            $result = json_decode($output, true);
            
            if (!$result) {
                return ['error' => 'فشل في تحليل نتيجة الذكاء الاصطناعي'];
            }
            
            // حفظ النتيجة في قاعدة البيانات
            $stmt = $pdo->prepare("UPDATE places SET quiz = ? WHERE id = ?");
            $stmt->execute([json_encode($result), $placeId]);
            
            return ['success' => true, 'quiz' => $result];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

// مثال على الاستخدام
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aiBridge = new AIBridge();
    
    $action = $_POST['action'] ?? '';
    $placeId = $_POST['place_id'] ?? 0;
    
    if ($action === 'narration') {
        $language = $_POST['language'] ?? 'ar';
        $result = $aiBridge->generateNarration($placeId, $language);
    } elseif ($action === 'quiz') {
        $difficulty = $_POST['difficulty'] ?? 'medium';
        $result = $aiBridge->generateQuiz($placeId, $difficulty);
    } else {
        $result = ['error' => 'إجراء غير صالح'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($result);
}
?>
<?php
require_once 'db_config.php';

// إعدادات الرأس للسماح بالطلبات من أي مصدر (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

class IoTController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // استقبال إحداثيات GPS من ESP32
    public function receiveGPS($robotId, $latitude, $longitude) {
        try {
            // تحديث موقع الروبوت
            $stmt = $pdo->prepare("UPDATE robots SET last_latitude = ?, last_longitude = ?, last_update = NOW() WHERE id = ?");
            $stmt->execute([$latitude, $longitude, $robotId]);
            
            // التحقق من وجود مكان قريب
            $nearbyPlace = $this->findNearbyPlace($latitude, $longitude);
            
            if ($nearbyPlace) {
                // تسجيل الزيارة
                $this->recordVisit($robotId, $nearbyPlace['id']);
                
                // إعداد الأوامر للروبوت
                $commands = [
                    'action' => 'narrate',
                    'place_id' => $nearbyPlace['id'],
                    'place_name' => $nearbyPlace['name'],
                    'narration' => $nearbyPlace['narration'] ?? 'جاري تحميل السرد...'
                ];
                
                return [
                    'success' => true,
                    'nearby_place' => $nearbyPlace['name'],
                    'commands' => $commands
                ];
            }
            
            return ['success' => true, 'message' => 'تم تحديث الموقع بنجاح'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // البحث عن مكان قريب بناءً على الإحداثيات
    private function findNearbyPlace($latitude, $longitude, $radius = 50) {
        // حساب المسافة باستخدام صيغة Haversine
        $stmt = $pdo->prepare("
            SELECT *, (
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS distance 
            FROM places 
            HAVING distance < ?
            ORDER BY distance
            LIMIT 1
        ");
        
        $stmt->execute([$latitude, $longitude, $latitude, $radius]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // تسجيل زيارة المكان
    private function recordVisit($robotId, $placeId) {
        // التحقق من وجود زيارة نشطة
        $stmt = $pdo->prepare("
            SELECT id FROM visits 
            WHERE robot_id = ? AND place_id = ? 
            AND visit_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$robotId, $placeId]);
        $existingVisit = $stmt->fetch();
        
        if (!$existingVisit) {
            // تسجيل زيارة جديدة
            $stmt = $pdo->prepare("INSERT INTO visits (robot_id, place_id) VALUES (?, ?)");
            $stmt->execute([$robotId, $placeId]);
        }
    }
    
    // نظام الاستطلاع (Polling) - للسماح للروبوت بطلب الأوامر
    public function pollCommands($robotId) {
        try {
            // التحقق من حالة الروبوت
            $stmt = $pdo->prepare("SELECT * FROM robots WHERE id = ?");
            $stmt->execute([$robotId]);
            $robot = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$robot) {
                return ['success' => false, 'error' => 'الروبوت غير موجود'];
            }
            
            // البحث عن مكان قريب
            if ($robot['last_latitude'] && $robot['last_longitude']) {
                $nearbyPlace = $this->findNearbyPlace($robot['last_latitude'], $robot['last_longitude']);
                
                if ($nearbyPlace) {
                    // تحديث حالة الروبوت
                    $stmt = $pdo->prepare("UPDATE robots SET last_update = NOW() WHERE id = ?");
                    $stmt->execute([$robotId]);
                    
                    return [
                        'success' => true,
                        'status' => 'active',
                        'commands' => [
                            'action' => 'narrate',
                            'place_id' => $nearbyPlace['id'],
                            'place_name' => $nearbyPlace['name'],
                            'narration' => $nearbyPlace['narration'] ?? 'جاري تحميل السرد...'
                        ]
                    ];
                }
            }
            
            // لا يوجد مكان قريب
            return [
                'success' => true,
                'status' => 'active',
                'commands' => [
                    'action' => 'continue',
                    'message' => 'استمر في الاستكشاف'
                ]
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

// معالجة الطلبات
 $iotController = new IoTController($pdo);

 $method = $_SERVER['REQUEST_METHOD'];
 $action = $_GET['action'] ?? '';

if ($method === 'POST' && $action === 'gps') {
    // استقبال بيانات GPS
    $data = json_decode(file_get_contents("php://input"), true);
    $robotId = $data['robot_id'] ?? 0;
    $latitude = $data['latitude'] ?? 0;
    $longitude = $data['longitude'] ?? 0;
    
    $result = $iotController->receiveGPS($robotId, $latitude, $longitude);
    echo json_encode($result);
} elseif ($method === 'GET' && $action === 'poll') {
    // نظام الاستطلاع
    $robotId = $_GET['robot_id'] ?? 0;
    $result = $iotController->pollCommands($robotId);
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'error' => 'طلب غير صالح']);
}
?>
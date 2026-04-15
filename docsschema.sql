-- جدول الأماكن السياحية
CREATE TABLE IF NOT EXISTS places (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    category VARCHAR(100) DEFAULT 'general',
    image_url VARCHAR(500),
    narration TEXT,
    quiz JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول الزيارات (لتتبع حركة الروبوت/السائح)
CREATE TABLE IF NOT EXISTS visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    place_id INT,
    robot_id INT,
    visit_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    duration_minutes INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (place_id) REFERENCES places(id),
    FOREIGN KEY (robot_id) REFERENCES robots(id)
);

-- جدول الروبوتات
CREATE TABLE IF NOT EXISTS robots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    model VARCHAR(100),
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    last_latitude DECIMAL(10, 8),
    last_longitude DECIMAL(11, 8),
    last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- إضافة بعض الأماكن للتجربة
INSERT INTO places (name, description, latitude, longitude, category) VALUES
('الأهرامات الجيزة', 'أهرامات الجيزة هي مجمع أثري يضم أهرامات خوفو، خفرع، منقرع', 29.9792, 31.1342, 'historical'),
('متحف المصري بالتحرير', 'أحد أكبر وأشهر المتاحف في العالم، يضم آثاراً مصرية قديمة', 30.0478, 31.2359, 'museum'),
('خان الخليلي', 'سوق قديم وشهير في القاهرة، يشتهر بالمنتجات الحرفية والهدايا التذكارية', 30.0456, 31.2623, 'market');


CREATE TABLE IF NOT EXISTS voice_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_message TEXT NOT NULL,
    assistant_response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
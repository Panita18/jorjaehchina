<?php
// ========================
// เชื่อมต่อฐานข้อมูล
// ========================
include '../config.php'; // include ไฟล์ config ที่มีการเชื่อมต่อ DB

// ========================
// ดึงเฉพาะคอลัมน์ rv_img จากตาราง reviews
// ========================
$sql = "SELECT rv_img FROM reviews ORDER BY rv_id DESC"; // เรียงรูปจากรีวิวล่าสุดมาก่อน
$res = $conn->query($sql);

// เก็บข้อมูลรูปทั้งหมดลงใน array $galleryData
$galleryData = [];
while ($row = $res->fetch_assoc()) {
    $galleryData[] = $row['rv_img']; // เก็บเฉพาะ path ของรูป (rv_img)
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gallery</title>

    <!-- CSS เมนูและสไตล์ของ Gallery -->
    <link rel="stylesheet" href="../css_front/menu.css">
    <link rel="stylesheet" href="../css_front/review.css">
</head>

<body>
    <?php include './menu.php'; ?> <!-- เมนูหลักของเว็บไซต์ -->

    <!-- ========================
         ส่วนแสดงรูป Gallery
    ========================= -->
    <h2>Travel Gallery</h2>

    <!-- ใช้ class .gallery (กำหนด layout ใน review.css) -->
    <div class="gallery" id="gallery-container">
        <?php if (empty($galleryData)): ?>
            <!-- กรณีไม่มีข้อมูลรูป -->
            <p style="grid-column:1/-1;text-align:center;">❌ ยังไม่มีรีวิวให้แสดง</p>
        <?php else: ?>
            <!-- วนลูปแสดงรูปภาพทั้งหมด -->
            <?php foreach ($galleryData as $img): ?>
                <div class="card">
                    <!-- ใช้ htmlspecialchars เพื่อป้องกัน XSS -->
                    <img src="<?= htmlspecialchars($img ?: '/img/default.jpg') ?>" alt="Travel Image">
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ========================
         ปุ่ม Back ย้อนกลับ
    ========================= -->
    <section class="menu-section">
        <ul>
            <li>
                <!-- javascript:history.back() ให้กลับไปหน้าก่อนหน้า -->
                <a href="javascript:history.back()">⭠ Back</a>
            </li>
        </ul>
    </section>

    <!-- JS เพิ่มเติม  -->
    <script src="app.js"></script>
</body>
</html>

<?php
    // ===== เชื่อมต่อฐานข้อมูล =====
    // ไฟล์ config.php จะเก็บข้อมูลการเชื่อมต่อ เช่น host, user, password, dbname
    include '../config.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ติดต่อเรา</title>

    <!-- ฟอนต์ Google (Nunito) สำหรับสไตล์ข้อความ -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- ไฟล์ CSS หลัก -->
    <link rel="stylesheet" href="../css_front/contact.css" />
    <link rel="stylesheet" href="../css_front/menu.css" />
</head>

<body>
<?php
    // ===== เรียกใช้งานเมนูนำทาง =====
    // menu.php คือไฟล์เมนูส่วนหัวที่ใช้ซ้ำในหลายหน้า
    include './menu.php';
?>

    <!-- ===== Main Contact Section ===== -->
    <main class="contact-section">

        <!-- ========== ข้อมูลการติดต่อ (ฝั่งซ้าย) ========== -->
        <aside class="contact-info" aria-labelledby="infoTitle">
            <h2 id="infoTitle" class="info-title">ข้อมูลการติดต่อ</h2>
            <p class="info-strong">JORJAEHCHINA</p>

            <!-- ที่อยู่สำนักงาน -->
            <p class="info-block">
                <span class="info-strong">ที่อยู่สำนักงาน</span><br>
                ตำบลประตูชัย อำเภอพระนครศรีอยุธยา จังหวัดพระนครศรีอยุธยา 13000
            </p>

            <!-- เบอร์โทรศัพท์ -->
            <h3 class="info-title">เบอร์โทรศัพท์</h3>
            <ul class="info-list">
                <li>084-353-2986</li>
                <li>061-626-2219</li>
            </ul>

            <!-- เวลาทำการ -->
            <h3 class="info-title">เวลาทำการ</h3>
            <p class="info-block">วันจันทร์ - อาทิตย์ เวลา 09.00 - 17.00 น.</p>

            <!-- อีเมล -->
            <h3 class="info-title">อีเมล</h3>
            <p class="info-block">
                <a href="mailto:jorjaehchina@gmail.com">jorjaehchina@gmail.com</a>
            </p>

            <!-- TikTok -->
            <h3 class="info-title">TikTok</h3>
            <p class="info-block">@jorjaehchina</p>
        </aside>

        <!-- ========== QR Code / LINE / ช่องทางอื่น (ฝั่งขวา) ========== -->
        <section class="contact-form" aria-labelledby="formTitle">
            <p class="form-sub">
                สแกน QR Code ด้านล่างเพื่อเพิ่มเราเป็นเพื่อนบน LINE / เข้าสู่เว็บไซต์ หรือช่องทางการติดต่ออื่น ๆ
            </p>

            <!-- รูป QR Code -->
            <div class="qr-container" style="text-align:center; margin-top:20px;">
                <img src="../frontend/img/qr-code.png"
                     alt="QR Code ติดต่อ JORJAEHCHINA"
                     style="width:400px; height:400px;">
            </div>
        </section>

    </main>
</body>

</html>

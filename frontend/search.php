<?php
// ========================
// เชื่อมต่อฐานข้อมูล
// ========================
include '../config.php';
$conn->set_charset("utf8"); // ตั้งค่า charset เพื่อรองรับภาษาไทย

// ========================
// รับค่าคำค้นหาจาก URL (?q=...)
// ========================
$q = trim($_GET['q'] ?? ''); // ถ้าไม่มี q ให้เป็นค่าว่าง

// เตรียมตัวแปรสำหรับเก็บผลลัพธ์ทั้งหมด (เป็น array 4 หมวดหมู่)
$results = [
    'programs'     => [], // ผลลัพธ์จากตาราง programs
    'seasons'      => [], // ผลลัพธ์จากตาราง seasons
    'reviews'      => [], // ผลลัพธ์จากตาราง reviews
    'destinations' => []  // ผลลัพธ์จาก array ปลายทาง (static)
];

// ========================
// ค้นหาข้อมูลเมื่อมีคำค้นหา
// ========================
if ($q !== '') {
    $like = "%$q%"; // เตรียมค่า LIKE สำหรับ SQL

    // --- 1️⃣ ค้นหา Programs ---
    $stmt = $conn->prepare("
        SELECT pg_id, pg_title, pg_img, pg_days, pg_nights
        FROM programs 
        WHERE pg_title LIKE ? OR pg_location LIKE ?
        ORDER BY pg_title ASC
    ");
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $results['programs'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // --- 2️⃣ ค้นหา Seasons ---
    $stmt = $conn->prepare("
        SELECT ss_id, ss_name 
        FROM seasons 
        WHERE ss_name LIKE ?
        ORDER BY ss_name ASC
    ");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $results['seasons'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // --- 3️⃣ ค้นหา Reviews (ตัวอย่างนี้ไม่ได้กรองด้วยคำค้นหา) ---
    // ดึงรีวิวทั้งหมดเรียงล่าสุด (สามารถเพิ่ม WHERE ถ้าต้องการกรอง)
    $stmt = $conn->prepare("
        SELECT rv_id, rv_img
        FROM reviews
        ORDER BY rv_id DESC
    ");
    $stmt->execute();
    $results['reviews'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // --- 4️⃣ ค้นหา Destinations (Static Array) ---
    $destinations = [
        ["name" => "Chengdu",          "link" => "chengdu.php",      "image" => "/img/IMG_0808.WEBP"],
        ["name" => "Jiuzhaigou",       "link" => "jiuzhaigou.php",   "image" => "/img/IMG_0808.WEBP"],
        ["name" => "Huanglong",        "link" => "huanglong.php",    "image" => "/img/IMG_0808.WEBP"],
        ["name" => "Four Sisters Mountain", "link" => "foursisters.php","image" => "/img/IMG_0808.WEBP"],
        ["name" => "Dagu Glacier",     "link" => "daguglacier.php",  "image" => "/img/IMG_0808.WEBP"],
        ["name" => "Bipenggou",        "link" => "bipenggou.php",    "image" => "/img/IMG_0808.WEBP"],
    ];
    // วนเช็คว่า q มีอยู่ในชื่อปลายทางหรือไม่ (ไม่สนตัวพิมพ์เล็ก/ใหญ่)
    foreach($destinations as $d){
        if (stripos($d['name'], $q) !== false){
            $results['destinations'][] = $d;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ผลการค้นหา: <?= htmlspecialchars($q) ?></title>

    <!-- CSS หลักของหน้า -->
    <link rel="stylesheet" href="../css_front/style.css">
    <link rel="stylesheet" href="../css_front/program.css">
    <link rel="stylesheet" href="../css_front/menu.css">

    <!-- Font Awesome (สำหรับ icon) -->
    <script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include './menu.php'; ?> <!-- เมนูหลัก -->

<section id="search-results">
    <h1>ผลการค้นหา: "<?= htmlspecialchars($q) ?>"</h1>

    <!-- ========================
         แสดงผลลัพธ์ Programs
    ========================= -->
    <h2>Programs</h2>
    <div class="cards">
        <?php if(empty($results['programs'])): ?>
            <p style="width:100%;text-align:center;">❌ ไม่พบผลลัพธ์</p>
        <?php else: ?>
            <?php foreach($results['programs'] as $p): ?>
                <div class="card">
                    <!-- รูปภาพโปรแกรม -->
                    <img src="<?= htmlspecialchars($p['pg_img'] ?: '/img/default.jpg') ?>" 
                         class="card-img" alt="">

                    <!-- เนื้อหาโปรแกรม -->
                    <div class="card-content">
                        <h3><?= htmlspecialchars($p['pg_title']) ?></h3>
                        <p><i class="fa-solid fa-clock"></i> <?= $p['pg_days'] ?>D<?= $p['pg_nights'] ?>N</p>
                        <a href="detail.php?id=<?= $p['pg_id'] ?>" class="btn-detail">ดูรายละเอียด</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ========================
         แสดงผลลัพธ์ Seasons
    ========================= -->
    <h2>Seasons</h2>
    <div class="cards">
        <?php if(empty($results['seasons'])): ?>
            <p style="width:100%;text-align:center;">❌ ไม่พบผลลัพธ์</p>
        <?php else: ?>
            <?php foreach($results['seasons'] as $s): ?>
                <div class="card">
                    <div class="card-content">
                        <h3><?= htmlspecialchars($s['ss_name']) ?></h3>
                        <a href="index.php?ss_id=<?= $s['ss_id'] ?>" class="btn-detail">ดูแพ็กเกจ</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ========================
         แสดงผลลัพธ์ Reviews
    ========================= -->
    <h2>Reviews</h2>
    <div class="cards">
        <?php if(empty($results['reviews'])): ?>
            <p style="width:100%;text-align:center;">❌ ไม่พบผลลัพธ์</p>
        <?php else: ?>
            <?php foreach($results['reviews'] as $r): ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($r['rv_img'] ?: '/img/default.jpg') ?>" class="card-img" alt="">
                    <div class="card-content">
                        <p>⭐ รีวิว ID <?= $r['rv_id'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ========================
         แสดงผลลัพธ์ Destinations
    ========================= -->
    <h2>Destinations</h2>
    <div class="cards">
        <?php if(empty($results['destinations'])): ?>
            <p style="width:100%;text-align:center;">❌ ไม่พบผลลัพธ์</p>
        <?php else: ?>
            <?php foreach($results['destinations'] as $d): ?>
                <div class="card" style="background-image:url('<?= $d['image'] ?>');background-size:cover;color:white;">
                    <div class="card-content" style="background:rgba(0,0,0,0.5);padding:10px;">
                        <h3><?= htmlspecialchars($d['name']) ?></h3>
                        <a href="<?= $d['link'] ?>" target="_blank" class="btn-detail">เยี่ยมชม</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- ปุ่ม Back -->
<section class="menu-section">
    <ul>
        <li>
            <a href="<?= $_SERVER['HTTP_REFERER'] ?? '/frontend/index.html'; ?>"> Back</a>
        </li>
    </ul>
</section>

<?php include './footer.php'; ?> <!-- ส่วนท้าย -->
</body>
</html>

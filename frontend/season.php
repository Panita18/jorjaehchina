<?php
include '../config.php'; // ===== เชื่อมต่อฐานข้อมูล =====

// --- ดึงเฉพาะฤดูกาลที่มีแพ็กเกจจริง ---
// เลือก season (ฤดูกาล) ที่มี program อยู่จริง โดยใช้ DISTINCT + INNER JOIN
$sql_seasons = "
    SELECT DISTINCT s.ss_id, s.ss_name
    FROM seasons s
    INNER JOIN programs p ON s.ss_id = p.ss_id
    ORDER BY s.ss_name ASC
";
$res_seasons = $conn->query($sql_seasons);

// เก็บฤดูกาลใน array (key = ss_id, value = ss_name)
$seasons = [];
while($row = $res_seasons->fetch_assoc()){
    $seasons[$row['ss_id']] = $row['ss_name'];
}

// --- ตรวจสอบว่าผู้ใช้เลือก ss_id ผ่าน GET หรือไม่ ---
$selected_ss_id = intval($_GET['ss_id'] ?? 0);

// --- ดึงแพ็กเกจตาม ss_id ที่เลือก ---
$programs = [];
if($selected_ss_id > 0){
    // ใช้ prepared statement ป้องกัน SQL Injection
    $stmt = $conn->prepare("
        SELECT p.pg_id, p.pg_title, p.pg_img, p.pg_start, p.pg_end,
               p.pg_days, p.pg_nights, p.pg_price,
               s.ss_name
        FROM programs p
        INNER JOIN seasons s ON p.ss_id = s.ss_id
        WHERE p.ss_id = ?
        ORDER BY p.pg_title ASC
    ");
    $stmt->bind_param("i", $selected_ss_id);
    $stmt->execute();
    $res = $stmt->get_result();

    // เก็บข้อมูลแพ็กเกจใน array
    while($row = $res->fetch_assoc()){
        $programs[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>แพ็กเกจตามฤดูกาล | JORJAEHCHINA</title>

<!-- ===== ไฟล์ CSS ===== -->
<link rel="stylesheet" href="../css_front/program.css">
<link rel="stylesheet" href="../css_front/menu.css">
<link rel="stylesheet" href="../css_front/season.css">

<!-- Font Awesome สำหรับไอคอน -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php
// ===== เมนูนำทาง (Navbar) =====
// menu.php คือเมนูหลักที่ใช้ซ้ำในทุกหน้า
include './menu.php';
?>

<!-- ===== ส่วนเลือกฤดูกาล ===== -->
<section id="seasons">
    <h2>เลือกฤดูกาลที่คุณสนใจ</h2>

    <!-- ปุ่มฤดูกาล (วน loop แสดงทุก season) -->
    <div class="seasons">
        <?php foreach($seasons as $id => $name): ?>
            <a href="?ss_id=<?= $id ?>" 
               class="season-btn <?= ($selected_ss_id==$id ? 'active' : '') ?>">
               <?= htmlspecialchars($name) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ===== รายการแพ็กเกจ ===== -->
    <div id="package-list" class="cards">
        <?php if($selected_ss_id == 0): ?>
            <!-- ยังไม่ได้เลือกฤดูกาล -->
            <p style="width:100%;text-align:center;">กรุณาเลือกฤดูกาลเพื่อดูแพ็กเกจ</p>

        <?php elseif(empty($programs)): ?>
            <!-- เลือกฤดูกาลแล้ว แต่ไม่มีแพ็กเกจ -->
            <p style="width:100%;text-align:center;">ไม่พบแพ็กเกจในฤดูกาลนี้</p>

        <?php else: ?>
            <!-- แสดงผลแพ็กเกจแต่ละรายการ -->
            <?php foreach($programs as $program): ?>
                <div class="card" data-id="<?= $program['pg_id'] ?>">
                    <!-- รูปภาพแพ็กเกจ (ถ้าไม่มีรูปใช้ default.jpg) -->
                    <img src="<?= htmlspecialchars($program['pg_img'] ?: '/img/default.jpg') ?>" 
                         alt="<?= htmlspecialchars($program['pg_title']) ?>" 
                         class="card-img">

                    <div class="card-content">
                        <h3><?= htmlspecialchars($program['pg_title']) ?></h3>

                        <!-- รหัสแพ็กเกจ -->
                        <p style="color:gray;font-size:0.9em;">
                            รหัสแพ็กเกจ: <?= $program['pg_id'] ?>
                        </p>

                        <!-- ฤดูกาล -->
                        <p style="color:blue;font-size:0.9em;">
                            ฤดูกาล: <?= htmlspecialchars($program['ss_name']) ?>
                        </p>

                        <!-- วันที่เดินทาง -->
                        <?php if($program['pg_start'] && $program['pg_end']): ?>
                            <p><i class="fa-solid fa-calendar-days"></i> 
                               <?= htmlspecialchars($program['pg_start']) ?> - <?= htmlspecialchars($program['pg_end']) ?>
                            </p>
                        <?php endif; ?>

                        <!-- จำนวนวัน/คืน -->
                        <?php if($program['pg_days'] && $program['pg_nights']): ?>
                            <p><i class="fa-solid fa-clock"></i> 
                               <?= htmlspecialchars($program['pg_days']) ?> Days <?= htmlspecialchars($program['pg_nights']) ?> Nights
                            </p>
                        <?php endif; ?>

                        <!-- ราคาเริ่มต้น -->
                        <?php if($program['pg_price']): ?>
                            <p class="price">From <span><?= number_format($program['pg_price'],0) ?></span></p>
                        <?php endif; ?>

                        <!-- ปุ่มรายละเอียด -->
                        <button class="btn" onclick="window.location.href='detail.php?id=<?= $program['pg_id'] ?>'">
                            Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- ===== ปุ่มย้อนกลับ ===== -->
<section class="menu-section">
    <ul>
        <li><a href="javascript:history.back()">⭠ Back</a></li>
    </ul>
</section>

<!-- ===== ปุ่มกลับไปบนสุด ===== -->
<button id="backToTop">↑</button>

<!-- ===== ปุ่มลอยไปยัง LINE ===== -->
<a href="https://lin.ee/XVpxnImL" target="_blank" class="floating-btn line">
    <img src="/img/LINE_logo.svg.png" alt="LINE" />
</a>

<!-- ===== Script Back to Top ===== -->
<script>
const backToTop = document.getElementById("backToTop");
window.addEventListener("scroll", () => {
    // แสดงปุ่มเมื่อ scroll ลงมาเกิน 300px
    backToTop.style.display = window.scrollY > 300 ? "block" : "none";
});
backToTop.addEventListener("click", () => {
    window.scrollTo({top:0, behavior:"smooth"});
});
</script>

</body>
</html>

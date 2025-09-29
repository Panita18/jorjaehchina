<?php
// =======================================
// เชื่อมต่อฐานข้อมูล
// =======================================
include '../config.php'; // ไฟล์เชื่อมต่อ DB
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// =======================================
// 1) ดึงข้อมูลโปรแกรมแนะนำ (Popular Programs)
// =======================================
$sqlPrograms = "SELECT * FROM programs ORDER BY pg_id DESC LIMIT 3";
$resultPrograms = $conn->query($sqlPrograms);

// =======================================
// 2) ดึงเฉพาะฤดูกาล (seasons) ที่มีแพ็กเกจจริง
//    เพื่อแสดงปุ่มฤดูกาลให้ผู้ใช้เลือก
// =======================================
$sql_seasons = "
    SELECT DISTINCT s.ss_id, s.ss_name
    FROM seasons s
    INNER JOIN programs p ON s.ss_id = p.ss_id
    ORDER BY s.ss_name ASC
";
$res_seasons = $conn->query($sql_seasons);
$seasons = [];
while($row = $res_seasons->fetch_assoc()){
    $seasons[$row['ss_id']] = $row['ss_name'];
}

// =======================================
// 3) ตรวจสอบว่าผู้ใช้คลิกเลือกฤดูกาล (ss_id)
//    จาก query string (GET)
// =======================================
$selected_ss_id = intval($_GET['ss_id'] ?? 0);

// =======================================
// 4) ถ้าเลือกฤดูกาลแล้ว ให้ดึงแพ็กเกจของฤดูกาลนั้น
// =======================================
$programs = [];
if($selected_ss_id > 0){
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
    while($row = $res->fetch_assoc()){
        $programs[] = $row;
    }
    $stmt->close();
}

// =======================================
// 5) ดึงรูปรีวิวทั้งหมดจากตาราง reviews
// =======================================
$sqlGallery = "SELECT rv_img FROM reviews ORDER BY rv_id DESC";
$resGallery = $conn->query($sqlGallery);
$galleryData = [];
while ($row = $resGallery->fetch_assoc()) {
    $galleryData[] = $row['rv_img'];
}

// =======================================
// 6) จุดหมายปลายทาง (Destinations) แบบกำหนดเอง
//    สามารถเพิ่ม/แก้ไขลิงก์และรูปภาพได้ตรงนี้
// =======================================
$destinations = [
    ["name" => "Jiuzhaigou",          "image" => "/img/IMG_0808.WEBP", "link" => "jiuzhaigou.php"],
    ["name" => "Huanglong",           "image" => "/img/IMG_0808.WEBP", "link" => "huanglong.php"],
    ["name" => "Four Sisters Mountain","image" => "/img/IMG_0808.WEBP", "link" => "foursisters.php"],
    ["name" => "Dagu Glacier",        "image" => "/img/IMG_0808.WEBP", "link" => "daguglacier.php"],
    ["name" => "Bipenggou",           "image" => "/img/IMG_0808.WEBP", "link" => "bipenggou.php"],
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JORJAEHCHINA</title>

    <!-- =================== CSS =================== -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css_front/style.css">
    <link rel="stylesheet" href="../css_front/menu.css">
    <link rel="stylesheet" href="../css_front/program.css">
    <link rel="stylesheet" href="../css_front/season.css">
</head>
<body>

<?php include './menu.php'; ?> <!-- เมนูนำทาง -->

<!-- =================== HERO =================== -->
<section id="hero">
    <div class="hero-content">
        <h1>Explore China with Jorjaehchina</h1>
        <p>Tailor-made tours, unforgettable experiences.</p>
        <!-- ช่องค้นหาแพ็กเกจ -->
        <input type="text" id="search" placeholder="🔍 Search packages...">
        <button id="searchBtn">Search</button>
        <script>
            // ฟังก์ชันค้นหาแพ็กเกจ
            function goToSearch() {
                let keyword = document.getElementById("search").value;
                window.location.href = "search.php?q=" + encodeURIComponent(keyword);
            }
            document.getElementById("search").addEventListener("keypress", function (e) {
                if (e.key === "Enter") goToSearch();
            });
            document.getElementById("searchBtn").addEventListener("click", goToSearch);
        </script>
    </div>
</section>

<!-- =================== โปรแกรมแนะนำ =================== -->
<section id="programs">
    <h2>โปรแกรมแนะนำ</h2>
    <div class="cards">
        <?php if($resultPrograms->num_rows > 0): ?>
            <?php while($row = $resultPrograms->fetch_assoc()): ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($row['pg_img']) ?>" alt="" class="card-img">
                    <div class="card-content">
                        <h3><?= htmlspecialchars($row['pg_title']) ?></h3>
                        <p>
                            <i class="fa-solid fa-plane-departure"></i>
                            <?= date("d F Y", strtotime($row['pg_startdate'])) ?>
                            -
                            <?= date("d F Y", strtotime($row['pg_enddate'])) ?>
                        </p>
                        <p>
                            <i class="fa-solid fa-clock"></i>
                            <?= $row['pg_days'] ?>D<?= $row['pg_nights'] ?>N
                        </p>
                        <a href="detail.php?id=<?= $row['pg_id'] ?>" class="btn-detail">ดูรายละเอียด</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>❌ ยังไม่มีโปรแกรมให้แสดง</p>
        <?php endif; ?>
    </div>
</section>

<!-- ลิงก์ไปหน้ารวมโปรแกรมทั้งหมด -->
<section class="menu-section">
    <ul>
        <li><a href="program.php">โปรแกรมทั้งหมด</a></li>
    </ul>
</section>

<!-- =================== เลือกฤดูกาล =================== -->
<section id="seasons">
    <h2>เลือกฤดูกาลที่สนใจ</h2>
    <div class="seasons">
        <?php foreach($seasons as $id => $name): ?>
            <a href="?ss_id=<?= $id ?>" 
               class="season-btn <?= ($selected_ss_id==$id ? 'active' : '') ?>">
               <?= htmlspecialchars($name) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- แสดงแพ็กเกจตามฤดูกาล -->
    <div id="package-list" class="cards">
        <?php if($selected_ss_id == 0): ?>
            <p style="width:100%;text-align:center;">กรุณาเลือกฤดูกาลเพื่อดูแพ็กเกจ</p>
        <?php elseif(empty($programs)): ?>
            <p style="width:100%;text-align:center;">ไม่พบแพ็กเกจในฤดูกาลนี้</p>
        <?php else: ?>
            <?php foreach($programs as $program): ?>
                <div class="card" data-id="<?= $program['pg_id'] ?>">
                    <img src="<?= htmlspecialchars($program['pg_img'] ?: '/img/default.jpg') ?>" 
                         alt="<?= htmlspecialchars($program['pg_title']) ?>" 
                         class="card-img">
                    <div class="card-content">
                        <h3><?= htmlspecialchars($program['pg_title']) ?></h3>
                        <p style="color:gray;font-size:0.9em;">
                            รหัสแพ็กเกจ: <?= $program['pg_id'] ?>
                        </p>
                        <p style="color:blue;font-size:0.9em;">
                            ฤดูกาล: <?= htmlspecialchars($program['ss_name']) ?>
                        </p>
                        <?php if($program['pg_start'] && $program['pg_end']): ?>
                            <p><i class="fa-solid fa-calendar-days"></i> 
                               <?= htmlspecialchars($program['pg_start']) ?> - <?= htmlspecialchars($program['pg_end']) ?>
                            </p>
                        <?php endif; ?>
                        <?php if($program['pg_days'] && $program['pg_nights']): ?>
                            <p><i class="fa-solid fa-clock"></i> 
                               <?= htmlspecialchars($program['pg_days']) ?> Days <?= htmlspecialchars($program['pg_nights']) ?> Nights
                            </p>
                        <?php endif; ?>
                        <?php if($program['pg_price']): ?>
                            <p class="price">From <span><?= number_format($program['pg_price'],0) ?></span></p>
                        <?php endif; ?>
                        <button class="btn" onclick="window.location.href='detail.php?id=<?= $program['pg_id'] ?>'">
                            Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- =================== แกลเลอรี่รีวิว =================== -->
<section id="gallery">
    <h2>รีวิวรูปภาพในทริป</h2>
    <div class="gallery-grid">
        <?php if(empty($galleryData)): ?>
            <p style="grid-column:1/-1;text-align:center;">❌ ยังไม่มีรีวิวให้แสดง</p>
        <?php else: ?>
            <?php foreach($galleryData as $img): ?>
                <div class="gallery-card">
                    <img src="<?= htmlspecialchars($img ?: '/img/default.jpg') ?>" alt="Travel Image">
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- =================== จุดหมายปลายทาง =================== -->
<section id="destinations">
    <div class="section-header">
        <h2>จุดหมายปลายทาง</h2>
    </div>
    <div class="destinations-grid">
        <?php foreach ($destinations as $dest): ?>
            <a href="<?= $dest['link'] ?>" target="_blank" class="destination-card" 
               style="background-image: url('<?= $dest['image'] ?>');">
                <span><?= htmlspecialchars($dest['name']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ปุ่ม Back to Top -->
<button id="backToTop" title="Back to Top">↑</button>

<!-- ปุ่มลอยไป LINE -->
<a href="https://lin.ee/XVpxnImL" target="_blank" class="floating-btn line">
    <img src="../frontend/img/LINE_logo.svg.png" alt="LINE" />
</a>

<style>
/* ===== ปุ่ม Back to Top ===== */
#backToTop {
    position: fixed;
    bottom: 90px; /* ให้สูงกว่า LINE ปุ่ม */
    right: 40px;
    z-index: 100;
    background-color: #007BFF;
    color: white;
    border: none;
    padding: 12px 16px;
    border-radius: 15%;
    cursor: pointer;
    font-size: 20px;
    display: none; /* ซ่อนไว้ก่อน */
    box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    transition: background-color 0.3s;
}
#backToTop:hover { background-color: #0056b3; }

/* ===== ปุ่ม LINE ===== */
.floating-btn.line {
    position: fixed;
    bottom: 40px;
    right: 40px;
    z-index: 101;
    display: block;
    width: 40px;
    height: 40px;
}
.floating-btn.line img {
    width: 100%;
    height: 100%;
}

/* Responsive ปรับตำแหน่งปุ่มเมื่อจอเล็ก */
@media screen and (max-width: 600px) {
    #backToTop, .floating-btn.line {
        bottom: 20px;
        right: 20px;
    }
}
</style>

<script>
// ===== แสดง/ซ่อนปุ่ม Back to Top =====
window.onscroll = function() {scrollFunction()};
function scrollFunction() {
    const btn = document.getElementById("backToTop");
    if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
        btn.style.display = "block";
    } else {
        btn.style.display = "none";
    }
}
// ===== คลิกเพื่อเลื่อนขึ้นบน =====
document.getElementById("backToTop").addEventListener("click", function() {
    window.scrollTo({top: 0, behavior: 'smooth'});
});
</script>

<?php include 'footer.php'; ?> <!-- ส่วนท้ายเว็บ -->

</body>
</html>

<?php
include '../config.php'; // เชื่อม DB

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// --- ดึงข้อมูล Popular Programs ---
$sqlPrograms = "SELECT * FROM programs ORDER BY pg_id DESC LIMIT 3";
$resultPrograms = $conn->query($sqlPrograms);

// --- ดึงเฉพาะฤดูกาลที่มีแพ็กเกจจริง ---
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

// --- ตรวจสอบว่าผู้ใช้เลือก ss_id ผ่าน GET ---
$selected_ss_id = intval($_GET['ss_id'] ?? 0);

// --- ดึงแพ็กเกจตาม ss_id ---
$programs = [];
if($selected_ss_id > 0){
    $stmt = $conn->prepare("
        SELECT p.pg_id, p.pg_title, p.pg_start, p.pg_end,
               p.pg_days, p.pg_nights,
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

// --- ดึง gallery reviews ---
$sqlGallery = "SELECT rv_img FROM reviews ORDER BY rv_id DESC";
$resGallery = $conn->query($sqlGallery);
$galleryData = [];
while ($row = $resGallery->fetch_assoc()) {
    $galleryData[] = $row['rv_img'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JORJAEHCHINA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css_front/style.css">
    <link rel="stylesheet" href="../css_front/menu.css">
    <link rel="stylesheet" href="../css_front/program.css">
    <link rel="stylesheet" href="../css_front/season.css">
</head>
<body>
<?php include './menu.php'; ?>

<!-- Hero -->
<section id="hero">
    <div class="hero-content">
        <h1>Explore China with Jorjaehchina</h1>
        <p>Tailor-made tours, unforgettable experiences.</p>
        <input type="text" id="search" placeholder="🔍 Search packages...">
        <button id="searchBtn">Search</button>
        <script>
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

<!-- Popular Programs -->
<section id="programs">
    <h2>โปรแกรมแนะนำ</h2>
    <div class="cards">
        <?php if($resultPrograms->num_rows > 0): ?>
            <?php while($row = $resultPrograms->fetch_assoc()): ?>
                <div class="card">
                    <!-- FIXED IMAGE -->
                    <img src="../frontend/img/view-01.jpg" alt="Fixed Image" class="card-img">
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
<section class="menu-section">
    <ul>
        <li><a href="program.php">โปรแกรมทั้งหมด</a></li>
    </ul>
</section>

<!-- Seasons -->
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

    <div id="package-list" class="cards">
        <?php if($selected_ss_id == 0): ?>
            <p style="width:100%;text-align:center;">กรุณาเลือกฤดูกาลเพื่อดูแพ็กเกจ</p>
        <?php elseif(empty($programs)): ?>
            <p style="width:100%;text-align:center;">ไม่พบแพ็กเกจในฤดูกาลนี้</p>
        <?php else: ?>
            <?php foreach($programs as $program): ?>
                <div class="card" data-id="<?= $program['pg_id'] ?>">
                    <!-- FIXED IMAGE -->
                    <img src="/img/default.jpg" alt="Fixed Image" class="card-img">
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
                        <button class="btn" onclick="window.location.href='detail.php?id=<?= $program['pg_id'] ?>'">
                            Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Travel Gallery -->
<section id="gallery">
    <h2>รีวิวรูปภาพในทริป</h2>
    <div class="gallery-grid">
        <?php if(empty($galleryData)): ?>
            <p style="grid-column:1/-1;text-align:center;">❌ ยังไม่มีรีวิวให้แสดง</p>
        <?php else: ?>
            <?php foreach($galleryData as $img): ?>
                <div class="gallery-card">
                    <img src="<?= htmlspecialchars($img ?: '../frontend/img/view-01.jpg') ?>" alt="Travel Image">
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Destinations -->
<section id="destinations">
    <div class="section-header">
        <h2>จุดหมายปลายทาง</h2>
    </div>

    <div class="destinations-grid">
        <a href="chengdu.php" target="_blank" class="destination-card" style="background-image: url('/img/IMG_0808.WEBP');">
            <span>Chengdu</span>
        </a>
        <a href="jiuzhaigou.php" target="_blank" class="destination-card" style="background-image: url('/img/IMG_0808.WEBP');">
            <span>Jiuzhaigou</span>
        </a>
        <a href="huanglong.php" target="_blank" class="destination-card" style="background-image: url('/img/IMG_0808.WEBP');">
            <span>Huanglong</span>
        </a>
        <a href="foursisters.php" target="_blank" class="destination-card" style="background-image: url('/img/IMG_0808.WEBP');">
            <span>Four Sisters Mountain</span>
        </a>
        <a href="daguglacier.php" target="_blank" class="destination-card" style="background-image: url('/img/IMG_0808.WEBP');">
            <span>Dagu Glacier</span>
        </a>
        <a href="bipenggou.php" target="_blank" class="destination-card" style="background-image: url('/img/IMG_0808.WEBP');">
            <span>Bipenggou</span>
        </a>
    </div>
</section>

<!-- Back to Top -->
<button id="backToTop" title="Back to Top">↑</button>

<!-- Floating LINE Button -->
<a href="https://lin.ee/XVpxnImL" target="_blank" class="floating-btn line">
    <img src="../frontend/img/LINE_logo.svg.png" alt="LINE" />
</a>

<style>
/* Back to Top Button */
#backToTop {
    position: fixed;
    bottom: 90px;
    right: 40px;
    z-index: 100;
    background-color: #007BFF;
    color: white;
    border: none;
    padding: 12px 16px;
    border-radius: 15%;
    cursor: pointer;
    font-size: 20px;
    display: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    transition: background-color 0.3s;
}
#backToTop:hover { background-color: #0056b3; }

/* Floating LINE Button */
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

@media screen and (max-width: 600px) {
    #backToTop, .floating-btn.line {
        bottom: 20px;
        right: 20px;
    }
}
</style>

<script>
window.onscroll = function() {scrollFunction()};
function scrollFunction() {
    const btn = document.getElementById("backToTop");
    if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
        btn.style.display = "block";
    } else {
        btn.style.display = "none";
    }
}
document.getElementById("backToTop").addEventListener("click", function() {
    window.scrollTo({top: 0, behavior: 'smooth'});
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>

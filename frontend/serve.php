<?php
session_start();
include '../config.php'; // เชื่อมต่อฐานข้อมูล

// ดึงข้อมูลรูปจากตาราง serve
$sql = "SELECT sv_img FROM serves";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>China Travel</title>
    <link rel="stylesheet" href="../css_front/serve.css">
    <link rel="stylesheet" href="../css_front/menu.css">
    <link rel="stylesheet" href="../css_front/footer.css">
    <script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
</head>
<body>
    <!-- <?php include './menu.php'; ?> -->
    

<header>
    <h1>China Travel</h1>
    <p>แนะนำโรงแรม รถ และการเดินทาง</p>
</header>

<section class="gallery">
    <h2>ตัวอย่างโรงแรมและห้องพัก</h2>
    <div class="image-grid">
        <?php 
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) { ?>
                <div class="card">
                    <img src="<?= $row['sv_img'] ?>">
                </div>
        <?php } } else {
            echo "<p>ยังไม่มีข้อมูลรูปภาพ</p>";
        } ?>
    </div>
</section>

<section class="menu-section">
    <ul>
        <li>
            <!-- ถ้ามีหน้าเดิม ให้กลับไป, ถ้าไม่มีให้กลับไป index.html -->
            <a href="<?= $_SERVER['HTTP_REFERER'] ?? '/frontend/index.html'; ?>">⭠ Back</a>
        </li>
    </ul>
</section>

<?php include 'footer.php'; ?> <!-- ส่วนท้ายเว็บ -->
</body>
</html>

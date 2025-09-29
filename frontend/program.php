<?php
// ===========================================================
// 1) เชื่อมต่อฐานข้อมูล
// ===========================================================
include '../config.php'; // ไฟล์เชื่อมต่อ DB (มีตัวแปร $conn)

// ===========================================================
// 2) ดึงข้อมูลโปรแกรมทั้งหมดจากตาราง programs
//     เรียงตามวันที่เริ่มเดินทาง (pg_startdate) จากน้อยไปมาก
// ===========================================================
$sql = "SELECT * FROM programs ORDER BY pg_startdate ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packages</title>

    <!-- CSS หลักของหน้านี้ -->
    <link rel="stylesheet" href="../css_front/program.css">
    <link rel="stylesheet" href="../css_front/menu.css">

    <!-- Font Awesome สำหรับ Icon -->
    <script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php
    // =======================================================
    // 3) เมนูนำทาง (Menu)
    //     menu.php คือไฟล์ที่มี Navigation Bar
    // =======================================================
    include './menu.php';
    ?>

    <!-- =======================================================
         SECTION: แสดงโปรแกรมทัวร์ทั้งหมด
    ======================================================== -->
    <section id="programs"><br>
        <h2>Program</h2>
        <div class="cards">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <!-- =======================
                         รูปภาพปกของโปรแกรม
                    ======================== -->
                    <img src="<?= htmlspecialchars($row['pg_img']) ?>" alt="" class="card-img" id="pg_img">

                    <div class="card-content">
                        <!-- ชื่อแพ็กเกจ -->
                        <h3 id="pg_title"><?= htmlspecialchars($row['pg_title']) ?></h3>

                        <!-- วันที่เดินทาง -->
                        <p id="pg_startdate">
                            <i class="fa-solid fa-plane-departure"></i>
                            <!-- แปลงวันที่เป็นรูปแบบ วัน เดือน ปี -->
                            <?= date("d F Y", strtotime($row['pg_startdate'])) ?>
                            -
                            <?= date("d F Y", strtotime($row['pg_enddate'])) ?>
                        </p>

                        <!-- จำนวนวัน/คืน -->
                        <p id="pg_nights">
                            <i class="fa-solid fa-clock"></i>
                            <?= $row['pg_days'] ?>D<?= $row['pg_nights'] ?>N
                        </p>

                        <!-- ปุ่มลิงก์ไปหน้ารายละเอียด -->
                        <a href="detail.php?id=<?= $row['pg_id'] ?>" class="btn-detail">ดูรายละเอียด</a>

                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- =======================================================
         SECTION: ปุ่ม Back กลับไปหน้าก่อนหน้า
    ======================================================== -->
    <section class="menu-section">
        <ul>
            <li>
                <!-- ถ้ามีหน้าเดิม ให้กลับไป, ถ้าไม่มีให้กลับไป index.html -->
                <a href="<?= $_SERVER['HTTP_REFERER'] ?? '/frontend/index.html'; ?>">⭠ Back</a>
            </li>
        </ul>
    </section>

    <!-- =======================================================
         ปุ่มลอย (Floating Buttons)
         - ปุ่ม Back to Top
         - ปุ่ม LINE
    ======================================================== -->

    <!-- ปุ่ม Back to Top -->
    <button id="backToTop" title="Back to Top">↑</button>

    <!-- ปุ่มติดต่อ LINE -->
    <a href="https://lin.ee/XVpxnImL" target="_blank" class="floating-btn line">
        <img src="../frontend/img/LINE_logo.svg.png" alt="LINE" />
    </a>

    <style>
    /* ==========================================================
       CSS ปุ่มลอย
    ========================================================== */

    /* ปุ่ม Back to Top */
    #backToTop {
        position: fixed;
        bottom: 90px; /* ให้สูงกว่า LINE */
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
    #backToTop:hover {
        background-color: #0056b3;
    }

    /* ปุ่ม LINE */
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

    /* Responsive สำหรับมือถือ */
    @media screen and (max-width: 600px) {
        #backToTop, .floating-btn.line {
            bottom: 20px;
            right: 20px;
        }
    }
    </style>

    <script>
    // ==========================================================
    // JavaScript แสดง/ซ่อนปุ่ม Back to Top เมื่อ scroll ลงมา
    // ==========================================================
    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
        const btn = document.getElementById("backToTop");
        if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
            btn.style.display = "block";
        } else {
            btn.style.display = "none";
        }
    }

    // เมื่อคลิกปุ่ม → เลื่อนขึ้นบนแบบ smooth
    document.getElementById("backToTop").addEventListener("click", function() {
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
    </script>

    <!-- ไฟล์ JS อื่น ๆ ของโปรเจ็กต์ (เช่นเมนู) -->
    <script src="app.js"></script>
</body>
</html>

<?php
// =======================================================
//  ปิดการ Cache ของเบราว์เซอร์ เพื่อให้ข้อมูลอัปเดตทุกครั้ง
// =======================================================
header("Cache-Control: no-cache, must-revalidate");
header("Expires: 0");
?>

<?php
// =======================================================
// 1) เชื่อมต่อฐานข้อมูล
// =======================================================
include '../config.php'; // ไฟล์นี้ต้องมี $conn สำหรับเชื่อมต่อ MySQL

// =======================================================
// 2) รับค่า id (รหัสโปรแกรมทัวร์) จาก URL
//    เช่น detail.php?id=3
// =======================================================
$pg_id = $_GET['id'] ?? ''; // ถ้าไม่มีค่า จะเป็น ''
if ($pg_id == 0) {
    // ถ้าไม่พบ id ให้หยุดการทำงานและแจ้งเตือน
    die("<p style='color:red;'>❌ ไม่พบ Program</p>");
}

// =======================================================
// 3) ดึงข้อมูลโปรแกรมทัวร์จากตาราง programs
// =======================================================
$sql = "SELECT * FROM programs WHERE pg_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pg_id);  // s = string (ป้องกัน SQL Injection)
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // ถ้าไม่มีข้อมูลโปรแกรม ให้หยุดและแจ้งเตือน
    die("<p style='color:red;'>❌ ไม่พบข้อมูล</p>");
}

$programs = $result->fetch_assoc(); // เก็บข้อมูลเป็น Array (เช่น pg_title, pg_img)

// =======================================================
// 4) ดึงข้อมูลราคา Join Tour (ราคาต่อคนตามจำนวนคน)
// =======================================================
$join_price = [];
$sql_join = "SELECT * FROM joins WHERE pg_id = ? ORDER BY num_joins ASC";
$stmt_join = $conn->prepare($sql_join);
$stmt_join->bind_param("s", $pg_id);
$stmt_join->execute();
$result_join = $stmt_join->get_result();
while($row = $result_join->fetch_assoc()) {
    // เก็บในรูปแบบ [จำนวนคน] => ราคา
    $join_price[$row['num_joins']] = $row['join_price'];
}

// =======================================================
// 5) ดึงข้อมูลราคา Private Tour (ราคาส่วนตัวตามจำนวนคน)
// =======================================================
$pv_price = [];
$sql_private = "SELECT * FROM privates WHERE pg_id = ? ORDER BY num_privates ASC";
$stmt_private = $conn->prepare($sql_private);
$stmt_private->bind_param("s", $pg_id);
$stmt_private->execute();
$result_private = $stmt_private->get_result();
while($row = $result_private->fetch_assoc()) {
    // เก็บในรูปแบบ [จำนวนคน] => ราคา
    $pv_price[$row['num_privates']] = $row['pv_price'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($programs['pg_title']); ?> - Detail</title>

    <!-- ฟอนต์ภาษาไทย -->
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai&display=swap" rel="stylesheet">

    <!-- CSS หลักของหน้านี้ -->
    <link rel="stylesheet" href="../css_front/detail.css">

    <style>
    /* ตัวอย่างสไตล์เสริม */
    h2, p {
        text-align: left;
    }
    </style>
</head>

<body>

    <!-- ===================================================
         Hero Section : รูปภาพปกของโปรแกรม
    =================================================== -->
    <section class="hero">
        <div style="background: url('<?= $programs['pg_img']; ?>') center/cover no-repeat;
            width: 100%; 
            height: 400px;">
        </div>
    </section>

    <!-- ===================================================
         Programs Section : ข้อมูลโปรแกรม
    =================================================== -->
    <section class="programs">
        <h2 id="pk_title"><?= htmlspecialchars($programs['pg_title']); ?></h2>
        <p>
            <span id="pg_days"><?= $programs['pg_days']; ?></span> Days 
            <span id="pg_nights"><?= $programs['pg_nights']; ?></span> Nights
        </p>
        <p id="pg_location"><?= htmlspecialchars($programs['pg_location']); ?></p>

        <h2>Itinerary</h2>
        <!-- nl2br() แปลง \n เป็น <br> -->
        <p id="pg_description"><?= nl2br(htmlspecialchars($programs['pg_description'])); ?></p>

        <!-- ตารางราคา -->
        <div class="programs-grid">

            <!-- Join Tour Table -->
            <div class="programs-card">
                <h3>Join Tour</h3>
                <table>
                    <tbody>
                        <?php if ($join_price): ?>
                            <?php $i = 1; foreach ($join_price as $num => $price): ?>
                                <tr>
                                    <td id="num_joins_<?= $i ?>"><?= $num ?> คน</td>
                                    <td id="join_joins_<?= $i ?>"><?= number_format($price, 0) ?></td>
                                </tr>
                            <?php $i++; endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Private Tour Table -->
            <div class="programs-card">
                <h3>Private Tour</h3>
                <table>
                    <tbody>
                        <?php if ($pv_price): ?>
                            <?php $i = 1; foreach ($pv_price as $num => $price): ?>
                                <tr>
                                    <td id="num_privates_<?= $i ?>"><?= $num ?> คน</td>
                                    <td id="pv_price_<?= $i ?>"><?= number_format($price, 0) ?></td>
                                </tr>
                            <?php $i++; endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </section>

    <!-- ปุ่มจองทัวร์ -->
    <section class="price">
        <a href="contact.php" target="_blank" class="btn">📩 Book Now</a>
    </section>

    <!-- ปุ่ม Back -->
    <section class="menu-section" style="text-align:center;">
        <ul style="list-style:none; padding:0;">
            <li><a href="javascript:history.back()"> Back</a></li>
        </ul>
    </section>

    <!-- ปุ่มกลับขึ้นบน -->
    <button id="backToTop">↑</button>

    <!-- ปุ่ม LINE (ลอยมุมขวาล่าง) -->
    <a href="https://lin.ee/XVpxnImL" target="_blank" class="floating-btn line">
        <img src="/img/LINE_logo.svg.png" alt="LINE" />
    </a>

    <script src="app.js"></script>

    <!-- ปุ่มกลับขึ้นบนและ LINE (สำรอง) -->
    <button id="backToTop" title="Back to Top">↑</button>
    <a href="https://lin.ee/XVpxnImL" target="_blank" class="floating-btn line">
        <img src="../frontend/img/LINE_logo.svg.png" alt="LINE" />
    </a>

    <style>
    /* ===========================
       CSS ปุ่มลอย
    ============================ */

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

    /* Responsive สำหรับจอเล็ก */
    @media screen and (max-width: 600px) {
        #backToTop, .floating-btn.line {
            bottom: 20px;
            right: 20px;
        }
    }
    </style>

    <script>
    // =====================================================
    //  แสดงปุ่ม Back to Top เมื่อ scroll ลงมาเกิน 200px
    // =====================================================
    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
        const btn = document.getElementById("backToTop");
        if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
            btn.style.display = "block";
        } else {
            btn.style.display = "none";
        }
    }

    // เมื่อคลิก → เลื่อนขึ้นบนแบบ smooth
    document.getElementById("backToTop").addEventListener("click", function() {
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
    </script>
</body>
</html>

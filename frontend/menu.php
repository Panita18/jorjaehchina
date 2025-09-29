<header class="menu">
    <div class="logo">JORJAEHCHINA</div>
    <nav>
        <ul>
            <li><a href="index.php">หน้าแรก</a></li>
            <li><a href="#programs">โปรแกรม</a></li> 
            <li><a href="#seasons">ฤดูกาล</a></li>  
            <li><a href="#gallery">รีวิว</a></li>
            <li><a href="#destinations">จุดหมาย</a></li>
            <li><a href="../frontend/serve.php">บริการเสริม</a></li>
            <li><a href="contact.php">ติดต่อ</a></li>
            <li><a href="../dashboard.php">เข้าสู่ระบบ</a></li>
        </ul>
    </nav>
    <div class="menu-toggle">☰</div>
</header>

<script>
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('header.menu nav ul');

    menuToggle.addEventListener('click', () => {
        nav.classList.toggle('active');
    });
</script>

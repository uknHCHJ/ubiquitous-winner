<?php
session_start();
/** 資料庫連線 */
$link = mysqli_connect("127.0.0.1", "HCHJ", "xx435kKHq", "HCHJ");
if ($link) {
  mysqli_query($link, 'SET NAMES UTF8');

} else {
  echo "資料庫連接失敗: " . mysqli_connect_error();
}

if (!isset($_SESSION['user'])) {
    echo("<script>
          alert('請先登入！！');
          window.location.href = '/~HCHJ/index.html'; 
          </script>");
    exit();
}

$userData = $_SESSION['user'];
// 確保你在 SESSION 中儲存了唯一識別符（例如 user_id 或 username）
$username= $userData['name']; // 例如從 SESSION 中獲取 user_id
$userId= $userData['user'];
?>

<!DOCTYPE html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>升學競賽全方位資源網</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

		<link rel="shortcut icon" type="image/x-icon" href="schoolimages/ukn.png">
        <!-- Place favicon.ico in the root directory -->

		<!-- ========================= CSS here ========================= -->
		<link rel="stylesheet" href="assets/css/bootstrap-5.0.0-alpha.min.css">
        <link rel="stylesheet" href="assets/css/LineIcons.2.0.css">
		<link rel="stylesheet" href="assets/css/animate.css">
		<link rel="stylesheet" href="assets/css/tiny-slider.css">
		<link rel="stylesheet" href="assets/css/glightbox.min.css">
		<link rel="stylesheet" href="assets/css/main.css">
    </head>
    <body>
        <!--[if lte IE 9]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
        <![endif]-->

        <!-- ========================= preloader start ========================= -->
            <div class="preloader">
                <div class="loader">
                    <div class="ytp-spinner">
                        <div class="ytp-spinner-container">
                            <div class="ytp-spinner-rotator">
                                <div class="ytp-spinner-left">
                                    <div class="ytp-spinner-circle"></div>
                                </div>
                                <div class="ytp-spinner-right">
                                    <div class="ytp-spinner-circle"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <!-- preloader end -->

        <!-- ========================= header start ========================= -->
        <header class="header navbar-area">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-12">
                        <nav class="navbar navbar-expand-lg">
                            <a class="navbar-brand" href="index-04.php">
                                <img src="schoolimages/uknlogo.png" alt="Logo">
                            </a>
                            <button class="navbar-toggler" type="button" data-toggle="collapse"
                                data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="Toggle navigation">
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                            </button>
                            <div class="collapse navbar-collapse sub-menu-bar" id="navbarSupportedContent">
                                <ul id="nav" class="navbar-nav ml-auto">
                                <li class="nav-item">
                                        <a href="index-04.php">首頁</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="page-scroll dd-menu" href="javascript:void(0)">個人資料</a>
                                        <ul class="sub-menu">
                                            <li class="nav-item"><a href="contact-04.php">查看個人資料</a></li>
                                            <li class="nav-item"><a href="../changepassword-01.html">修改密碼</a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item">
                                        <a class="page-scroll dd-menu" href="javascript:void(0)">班級管理</a>
                                        <ul class="sub-menu">
                                            <li class="nav-item"><a href="Contest-history1.php">查看學生備審資料</a></li>
                                            <li class="nav-item"><a href="order1.php">查看志願序</a></li>
                                            <li class="nav-item"><a href="Contest-history1.php">查看競賽歷程</a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-item dd-menu">二技校園網</a>           
                                        <ul class="sub-menu">
                                        <li class="nav-item"><a href="Schoolnetwork1-04.php">首頁</a></li>
                                        <li class="nav-item"><a href="AddSchool1-04.php">新增校園</a></li>
                                        <li class="nav-item"><a href="portfolio delete-04(編輯).php">編輯資訊</a></li>                                        
                                        </ul>
                                    </li>
                                    <li class="nav-item">
                                        <a class="page-scroll dd-menu" href="javascript:void(0)">比賽資訊</a>
                                        <ul class="sub-menu">
                                        <li class="nav-item"><a href="blog-03(競賽).php">查看</a></li>
                                            <li class="nav-item"><a href="create-03.php">新增</a></li>
                                            <li class="nav-item"><a href="delete-03.php">編輯</a></li>
                                        </ul>                                      
                                    </li>
                                    <li class="nav-item">
                                        <a class="page-scroll" >目前登入使用者：<?php echo $userId; ?></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="page-scroll" href="/~HCHJ/Permission.php" >切換使用者</a>
                                    </li> 
                                    <li class="nav-item">
                                        <a class="page-scroll" href="../logout.php" >登出</a>
                                    </li>                           
                                </ul>
                            </div> <!-- navbar collapse -->
                        </nav> <!-- navbar -->
                    </div>
                </div> <!-- row -->
            </div> <!-- container -->
        
        </header>
        <!-- ========================= header end ========================= -->

        <!-- ========================= hero-section start ========================= -->
        <section id="home" class="hero-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-5 col-lg-6">
                        <div class="hero-content-wrapper">

                            <h2 class="mb-25 wow fadeInDown" data-wow-delay=".2s">您好，<?php echo $username; ?></h2>
                            <h1 class="mb-25 wow fadeInDown" data-wow-delay=".2s">歡迎使用本系統</h1>

                            <script>
                                // JavaScript 函数触发表单提交
                                function submitLogout() {
                                    document.getElementById('logoutForm').submit();  // 提交隐藏的表单
                                }
                            </script>
                                <a href="javascript:void(0)" type="button" class="theme-btn" onclick="submitLogout()">登出</a>
                                <form id="logoutForm" action="../logout.php" method="POST" style="display:none;">
                        </div>
                    </div>  
                    <div class="col-xl-7 col-lg-6">
                        <!--<div class="hero-img">
                            <div class="d-inline-block hero-img-right">-->
                                <img src="schoolimages/imlogo.png" alt="" class="wow fadeInRight" text-align="text-center" data-wow-delay=".5s">                                                          
                           <!-- </div>
                        </div>-->
                    </div>
                </div>
            </div>
        </section>
        <!-- ========================= hero-section end ========================= -->

        <!-- ========================= client-logo-section start ========================= -->
        <section class="client-logo-section pt-100">
            <div class="container">
                <div class="client-logo-wrapper">
                    <div class="client-logo-carousel d-flex align-items-center justify-content-between">
                        <div class="client-logo">
                            <img src="schoolimages/uknim.jpg" alt="">
                        </div>
                        <div class="client-logo">
                            <img src="schoolimages/uknbm.jpg" alt="">
                        </div> 
                        <div class="client-logo">
                            <img src="schoolimages/uknanime.jpg" alt="">
                        </div>
                        <div class="client-logo">
                            <img src="schoolimages/uknbaby.jpg" alt="">
                        </div>
                        <div class="client-logo">
                            <img src="schoolimages/uknenglish.jpg" alt="">
                        </div>
                        <div class="client-logo">
                            <img src="schoolimages/ukneyes.jpg" alt="">
                        </div>
                        <div class="client-logo">
                            <img src="schoolimages/uknnurse.jpg" alt="">
                        </div>

                        
                    </div>
                </div>
            </div>
        </section>
        <!-- ========================= client-logo-section end ========================= -->



        <!-- ========================= footer start ========================= -->
        <footer class="footer pt-100">
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="footer-widget mb-60 wow fadeInLeft" data-wow-delay=".2s">
                            <a href="index-04.php" class="logo mb-30"><img src="schoolimages/uknlogo.png" alt="logo"></a>
                            <p class="mb-30 footer-desc">©康寧大學資訊管理科製作</p>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="footer-widget mb-1 wow fadeInLeft" data-wow-delay=".8s">
                            
                            <ul class="footer-contact"> 
                                <h3>關於我們</h3>                                                        
                                <p>(02)2632-1181/0986-212-566</p>                                
                                    <p>台北校區：114 臺北市內湖區康寧路三段75巷137號</p>                             
                            </ul>
                            <style>
                                .footer .row {
                                display: flex;
                                align-items: center; /* 垂直居中 */
                                justify-content: space-between; /* 讓兩個區塊分居左右 */
                                }
                                .footer-widget {                                   
                                text-align: right; /* 讓「關於學校」內容靠右對齊 */
                                }
                            </style>
                        </div>
                    </div>
                </div>

                <div class="copyright-area">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="footer-social-links">
                                <ul class="d-flex">
                                    <li><a href="https://www.facebook.com/UKNunversity"><i class="lni lni-facebook-filled"></i></a></li>
                                    <li><a href="https://www.instagram.com/ukn_taipei/"><i class="lni lni-instagram-filled"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- ========================= footer end ========================= -->

        <!-- ========================= scroll-top ========================= -->
        <a href="#" class="scroll-top">
            <i class="lni lni-arrow-up"></i>
        </a>
        
		<!-- ========================= JS here ========================= -->
		<script src="assets/js/bootstrap.bundle-5.0.0.alpha-min.js"></script>
		<script src="assets/js/contact-form.js"></script>
        <script src="assets/js/count-up.min.js"></script>
        <script src="assets/js/tiny-slider.js"></script>
        <script src="assets/js/isotope.min.js"></script>
        <script src="assets/js/glightbox.min.js"></script>
        <script src="assets/js/wow.min.js"></script>
        <script src="assets/js/imagesloaded.min.js"></script>
		<script src="assets/js/main.js"></script>
        
        <script>
            //========= glightbox
            GLightbox({
                'href': '#',
                'type': 'video',
                'source': 'youtube', //vimeo, youtube or local
                'width': 900,
                'autoplayVideos': true,
            });

            //========= testimonial 
            tns({
                container: '.testimonial-active',
                items: 1,
                slideBy: 'page',
                autoplay: false,
                mouseDrag: true,
                gutter: 0,
                nav: false,
                controlsText: ['<i class="lni lni-arrow-left"></i>', '<i class="lni lni-arrow-right"></i>'],
            });

            //============== isotope masonry js with imagesloaded
            imagesLoaded( '#container', function() {
                var elem = document.querySelector('.grid');
                var iso = new Isotope(elem, {
                    // options
                    itemSelector: '.grid-item',
                    masonry: {
                    // use outer width of grid-sizer for columnWidth
                    columnWidth: '.grid-item'
                    }
                });

                let filterButtons = document.querySelectorAll('.portfolio-btn-wrapper button');
                filterButtons.forEach(e =>
                    e.addEventListener('click', () => {

                        let filterValue = event.target.getAttribute('data-filter');
                        iso.arrange({
                            filter: filterValue
                        });
                    })
                );
            });
        </script>
    </body>
</html>

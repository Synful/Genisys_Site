<?php
require_once("../backend/includes.php");

if(isset($_POST['btnLogOut'])) {
  unset($_SESSION['user']);
  user::$instance = null;
  session_destroy();
  header("Location: login");
}

if(!isset($_SESSION['user'])) {
  header("Location: login");
} else {
  user::$instance = unserialize($_SESSION['user']);
  user::$instance->db = $db;
  user::$instance->initUserByName(user::$instance->username);
}

$query = $site->db->prepare("SELECT * FROM products ORDER BY id ASC");
$query->execute();
$products = $query->fetchall(PDO::FETCH_CLASS, 'ArrayObject');

$code = "";
if(isset($_POST['btnWeek']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
  if(!FREE_MODE) {
    if(!user::$instance->hasTime()) {
      switch($_POST['btnWeek']) {
        case 0:
          if(!DISABLE_PAYPAL || user::$instance->username == "Synful") {
            $_SESSION['user'] = serialize(user::$instance);
            header("Location: ".$paypal->create(1));
            exit();
          }
          break;
        case 1:
          if(!DISABLE_COINBASE || user::$instance->username == "Synful") {
            $_SESSION['user'] = serialize(user::$instance);
            header("Location: ".$coinbase->create(1, user::$instance->license));
            exit();
          }
          break;
      }
    } else {
      $code = "You already have time.";
    }
  }
}
if(isset($_POST['btnMonth']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
  if(!FREE_MODE) {
    if(!user::$instance->hasTime()) {
      switch($_POST['btnMonth']) {
        case 0:
          if(!DISABLE_PAYPAL || user::$instance->username == "Synful") {
            $_SESSION['user'] = serialize(user::$instance);
            header("Location: ".$paypal->create(2));
            exit();
          }
          break;
        case 1:
          if(!DISABLE_COINBASE || user::$instance->username == "Synful") {
            $_SESSION['user'] = serialize(user::$instance);
            header("Location: ".$coinbase->create(2, user::$instance->license));
            exit();
          }
          break;
      }
    } else {
      $code = "You already have time.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="assets/img/favicon.png">
  <title>Genisys</title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />
  <link href="https://use.fontawesome.com/releases/v5.15.1/css/all.css" rel="stylesheet">
  <link href="assets/font-awesome/css/font-awesome.min.css" rel="stylesheet">
  <!-- Nucleo Icons -->
  <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
  <!-- CSS Files -->
  <link href="assets/css/black-dashboard.css" rel="stylesheet" />
  <link href="assets/css/style.css" rel="stylesheet" />
</head>

<body class="">
  <div class="wrapper">
    <div class="sidebar">
      <div class="sidebar-wrapper">
        <div class="logo">
          <div class="reg_logo"></div>
        </div>
        <ul class="nav">
          <li class="syn-li">
            <a href="index">
              <i class="fa fa-fw fa-home"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="syn-li active">
            <a href="products">
              <i class="fa fa-fw fa-shopping-cart"></i>
              <p>Products</p>
            </a>
          </li>
          <?php if(user::$instance->hasTime() || FREE_MODE) :?>
          <li class="syn-li">
            <a href="downloads">
              <i class="fa fa-fw fa-download"></i>
              <p>Downloads</p>
            </a>
          </li>
          <?php endif; ?>
          <li class="syn-li">
            <a href="profile">
              <i class="fa fa-fw fa-user"></i>
              <p>User Profile</p>
            </a>
          </li>
          <?php if(user::$instance->level >= 1) :?>
          <li class="syn-li">
            <a href="admin">
              <i class="fa fa-fw fa-cogs"></i>
              <p>Panel</p>
            </a>
          </li>
          <?php endif;?>
        </ul>
      </div>
    </div>
    <div class="main-panel">
      <!-- Navbar -->
      <nav class="navbar navbar-expand-lg navbar-absolute navbar-transparent">
        <div class="container-fluid">
          <div class="navbar-wrapper">
            <div class="navbar-toggle d-inline">
              <button type="button" class="navbar-toggler">
                <span class="navbar-toggler-bar bar1"></span>
                <span class="navbar-toggler-bar bar2"></span>
                <span class="navbar-toggler-bar bar3"></span>
              </button>
            </div>
            <a class="navbar-brand" href="javascript:void(0)">Downloads</a>
          </div>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-bar navbar-kebab"></span>
            <span class="navbar-toggler-bar navbar-kebab"></span>
            <span class="navbar-toggler-bar navbar-kebab"></span>
          </button>
          <div class="collapse navbar-collapse" id="navigation">
            <ul class="navbar-nav ml-auto">
              <li class="dropdown nav-item">
                <form action="" method="POST">
                  <button class="btn btn-primary" name="btnLogOut" type="submit">Log Out</button>
                </form>
              </li>
              <li class="separator d-lg-none"></li>
            </ul>
          </div>
        </div>
      </nav>
      <!-- End Navbar -->
      <div class="content">
        <?php
          if($code != "") {
            echo "<div class=\"alert alert-danger\">
                    <button type=\"button\" aria-hidden=\"true\" class=\"close\" data-dismiss=\alert\" aria-label=\"Close\">
                      <i class=\"tim-icons icon-simple-remove\"></i>
                    </button>
                    <span><b> Error - </b> ".$code."</span>
                  </div>";
          }
        ?>
        <div class="row">
          <?php
            foreach($products as $product) {
              echo "<div class=\"col-md-3\">
                <div class=\"card card-chart\">
                  <div class=\"card-header\">
                    <h2 class=\"card-title\"><b><center>1 ".$product->name."</center></b></h2>
                  </div>
                  <div class=\"card-body\">
                    <center><h5>$".$product->price." USD</h5></center>
                    <form action=\"\" method=\"POST\">
                      <button class=\"paypal-button\" name=\"btn".$product->name."\" value=\"0\" id=\"btn".$product->name."\" type=\"submit\"></button>
                      <button class=\"coinbase-button\" name=\"btn".$product->name."\" value=\"1\" id=\"btn".$product->name."\" type=\"submit\"></button>
                    </form>
                  </div>
                </div>
              </div>";
            }
          ?>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="card card-chart">
              <div class="card-body">
                <div class="w3-content w3-display-container" style="max-width:900px">
                  <img class="mySlides" src="assets/img/menu/g_1.png" style="width:100%">
                  <img class="mySlides" src="assets/img/menu/g_2.png" style="width:100%">
                  <img class="mySlides" src="assets/img/menu/g_3.png" style="width:100%">
                  <img class="mySlides" src="assets/img/menu/g_4.png" style="width:100%">
                  <img class="mySlides" src="assets/img/menu/g_5.png" style="width:100%">
                  <img class="mySlides" src="assets/img/menu/g_6.png" style="width:100%">
                  <div class="w3-center w3-container w3-section w3-large w3-text-white w3-display-bottommiddle" style="width:100%">
                    <div class="w3-left w3-hover-text-khaki" onclick="plusDivs(-1)">&#10094;</div>
                    <div class="w3-right w3-hover-text-khaki" onclick="plusDivs(1)">&#10095;</div>
                    <span class="w3-badge demo w3-border w3-transparent w3-hover-white" onclick="currentDiv(1)"></span>
                    <span class="w3-badge demo w3-border w3-transparent w3-hover-white" onclick="currentDiv(2)"></span>
                    <span class="w3-badge demo w3-border w3-transparent w3-hover-white" onclick="currentDiv(3)"></span>
                    <span class="w3-badge demo w3-border w3-transparent w3-hover-white" onclick="currentDiv(4)"></span>
                    <span class="w3-badge demo w3-border w3-transparent w3-hover-white" onclick="currentDiv(5)"></span>
                    <span class="w3-badge demo w3-border w3-transparent w3-hover-white" onclick="currentDiv(6)"></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <footer class="footer">
        <div class="container-fluid">
          <div class="copyright">
            © 2020-<script> document.write(new Date().getFullYear()) </script> made by <a href="">Synful</a>
          </div>
        </div>
      </footer>
    </div>
  </div>
  <!--   Core JS Files   -->
  <script src="assets/js/core/jquery.min.js"></script>
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <script src="assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
  <!-- Chart JS -->
  <script src="assets/js/plugins/Chart.js"></script>
  <!--  Notifications Plugin    -->
  <script src="assets/js/plugins/bootstrap-notify.js"></script>
  <script src="assets/js/black-dashboard.js"></script>
  <script>
  var slideIndex = 1;
  showDivs(slideIndex);

  function plusDivs(n) {
    showDivs(slideIndex += n);
  }

  function currentDiv(n) {
    showDivs(slideIndex = n);
  }

  function showDivs(n) {
    var i;
    var x = document.getElementsByClassName("mySlides");
    var dots = document.getElementsByClassName("demo");
    if (n > x.length) {slideIndex = 1}
    if (n < 1) {slideIndex = x.length}
    for (i = 0; i < x.length; i++) {
      x[i].style.display = "none";  
    }
    for (i = 0; i < dots.length; i++) {
      dots[i].className = dots[i].className.replace(" w3-white", "");
    }
    x[slideIndex-1].style.display = "block";  
    dots[slideIndex-1].className += " w3-white";
  }
  </script>
</body>
</html>
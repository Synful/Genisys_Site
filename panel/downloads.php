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
}

if(!user::$instance->hasTime() && !FREE_MODE) {
  header("Location: index");
}

$file = 'assets/downloads/Genisys 3.3.rar';
if(isset($_POST['btnDownload']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
  if (file_exists($file)) {
    header("Content-type: application/rar"); 
    header("Content-Disposition: attachment; filename=".basename($file));
    header("Content-length: " . filesize($file));
    header("Pragma: no-cache"); 
    header("Expires: 0"); 
    readfile($file);
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
          <li class="syn-li">
            <a href="products">
              <i class="fa fa-fw fa-shopping-cart"></i>
              <p>Products</p>
            </a>
          </li>
          <?php if(user::$instance->hasTime() || FREE_MODE) :?>
          <li class="syn-li active">
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
        <div class="row">
          <div class="col-md-8">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <div class="panel-title">Change Log</div>
              </div>
              <div class="panel-body">
                <div class="ov-widget">
                  <div class="ov-widget__list">
                    <div class="ov-widget__item ov-widget__item_inc">
                      <div id="newsbox" class="sp-widget__wrap scrollable scrollbar-macosx">
                        <div class="sp-widget__cont">
                          <div class="sp-widget__list">
                          <?php
                            $fh = fopen('assets/downloads/changelog.txt','r');
                            while ($line = fgets($fh)) {
                              echo $line . "<br>";
                            }
                            fclose($fh);
                          ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <div class="panel-title">Downloads</div>
              </div>
              <div class="panel-body">
                <div class="ov-widget">
                  <div class="ov-widget__list">
                    <div class="ov-widget__item ov-widget__item_inc">
                      <div class="ov-widget__value"><center>Version: 3.3</center></div>
                      <div class="ov-widget__info">
                        <div class="s-container">
                          <div class="center">
                            <form action="" method="POST">
                              <button class="btn btn-primary" name="btnDownload" type="submit">Download</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
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
</body>
</html>
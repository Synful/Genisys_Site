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

if(isset($_GET['token']) || isset($_GET['PayerID'])) {
    user::$instance = unserialize($_SESSION['user']);
    $paypal->response(user::$instance, $_GET['token'], $_GET['PayerID'], $_SESSION['price']);
}


$ps3Logins = utils::getPS3Logins($db); 
$siteLogins = utils::getSiteLogins($db);

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
          <li class="syn-li active">
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
            <a class="navbar-brand" href="javascript:void(0)">Dashboard</a>
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
          <div class="col-md-3">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <div class="panel-title">Discord Server</div>
              </div>
              <div class="panel-body">
                <div class="ov-widget">
                  <iframe src="https://discordapp.com/widget?id=388524177684037632&theme=dark" width="100%" height="310px" allowtransparency="true" frameborder="0"></iframe>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <div class="panel-title">Latest Site Logins</div>
              </div>
              <div class="panel-body">
                <div class="ov-widget">
                  <table class="table">
                    <thead class="text-primary">
                      <tr>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        if(isset($siteLogins)) {
                          foreach($siteLogins as $row) {
                            $strSF = "";
                            if($row->info == "Login Successful!" || $row->info == "AuthSuccess") {
                              $strSF = "Success";
                            } else {
                              $strSF = "Failed";
                            }
                            echo "<tr>
                                    <td>$row->username</td>
                                    <td>".date('m/d/Y g:i:s a', $row->date)."</td>
                                    <td>".$strSF."</td>
                                  </tr>";
                          }
                        }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <div class="panel-title">Latest PS3 Logins</div>
              </div>
              <div class="panel-body">
                <div class="ov-widget">
                  <table class="table tablesorter">
                    <thead class="text-primary">
                      <tr>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        if(isset($ps3Logins)) {
                          foreach($ps3Logins as $row) {
                            $strSF = "";
                            if($row->info == "Login Successful!" || $row->info == "AuthSuccess") {
                              $strSF = "Success";
                            } else {
                              $strSF = "Failed";
                            }
                            echo "<tr>
                                    <td>$row->username</td>
                                    <td>".date('m/d/Y g:i:s a', $row->date)."</td>
                                    <td>".$strSF."</td>
                                  </tr>";
                          }
                        }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <div class="panel-title">Statistics</div>
              </div>
              <div class="panel-body">
                <div class="ov-widget">
                  <div class="ov-widget__list">
                    <div class="ov-widget__item ov-widget__item_inc">
                      <div class="ov-widget__value"><?php echo utils::usersOnline($db); ?></div>
                      <div class="ov-widget__info">
                        <div class="ov-widget__title">Users Online</div>
                      </div>
                    </div>
                    <div class="ov-widget__item ov-widget__item_dec">
                      <div class="ov-widget__value"><?php echo utils::usersCount($db); ?></div>
                      <div class="ov-widget__info">
                        <div class="ov-widget__title">Total Users</div>
                      </div>
                    </div>
                    <div class="ov-widget__item ov-widget__item_warn">
                      <div class="ov-widget__value"><?php echo utils::loginsCount($db); ?></div>
                      <div class="ov-widget__info">
                        <div class="ov-widget__title">Total Logins</div>
                      </div>
                    </div>
                    <div class="ov-widget__item ov-widget__item_tack">
                      <div class="ov-widget__value"><?php echo utils::salesCount($db); ?></div>
                      <div class="ov-widget__info">
                        <div class="ov-widget__title">Total Sales</div>
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
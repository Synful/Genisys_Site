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

$code = "";
if(isset($_POST['requestBtn'])) {
  if(isset($_POST['reasonTxt']) && !empty($_POST['reasonTxt'])) {
    $code = user::$instance->requestReset($_POST['reasonTxt']);
  } else {
    $code = "Please provide a reset reason.";
  }
}

if(isset($_POST['saveBtn'])) {
  if(isset($_POST['oldpassTxt']) && !empty($_POST['oldpassTxt']) &&
     isset($_POST['newpassTxt']) && !empty($_POST['newpassTxt']) &&
     isset($_POST['rnewpassTxt']) && !empty($_POST['rnewpassTxt'])) {
       $code = user::$instance->updatePassword($_POST['oldpassTxt'], $_POST['newpassTxt'], $_POST['rnewpassTxt']);
  } else {
    $code = "Please make sure all fields are filled.";
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
          <li class="syn-li">
            <a href="downloads">
              <i class="fa fa-fw fa-download"></i>
              <p>Downloads</p>
            </a>
          </li>
          <?php endif; ?>
          <li class="syn-li active">
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
            <a class="navbar-brand" href="javascript:void(0)">Profile</a>
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
          <div class="col-md-8">
            <div class="card">
              <div class="card-header">
                <h5 class="title">Edit Profile</h5>
              </div>
              <form action="" method="POST">
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-4 pr-md-1">
                      <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="oldpassTxt" class="form-control">
                      </div>
                    </div>
                    <div class="col-md-4 px-md-1">
                      <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="newpassTxt" class="form-control">
                      </div>
                    </div>
                    <div class="col-md-4 pl-md-1">
                      <div class="form-group">
                        <label>Re-type New Password</label>
                        <input type="password" name="rnewpassTxt" class="form-control">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4 pr-md-1">
                      <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" value="<?php echo user::$instance->email; ?>">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-footer">
                  <button type="submit" name="saveBtn" class="btn btn-fill btn-primary">Save</button>
                </div>
              </form>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card card-user">
              <div class="card-body">
                  <div class="author">
                    <a href="">
                      <h5 class="title"><?php echo user::$instance->username; ?></h5>
                    </a>
                    <p class="description">
                      <strong>Time:</strong> <?php echo (user::$instance->time == 0 ? "None" : date('m/d/Y g:i:s a', user::$instance->time));?>
                      <br>
                      <strong>Key:</strong> <?php echo user::$instance->license; ?>
                    </p>
                  </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-2">
            <div class="card">
              <div class="card-header">
                <h5 class="title">Request Reset</h5>
              </div>
              <form action="" method="POST">
                <div class="card-body">
                  <div class="form-group">
                    <label>Request Reason</label>
                    <input name="reasonTxt" type="text" class="form-control">
                  </div>
                </div>
                <div class="card-footer">
                  <button type="submit" name="requestBtn" class="btn btn-fill btn-primary">Request</button>
                </div>
              </form>
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
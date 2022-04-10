<?php
require_once("../../backend/includes.php");

$admin = new admin($db);

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

if(user::$instance->level < 1) {
  $_SESSION['user'] = serialize(user::$instance);
  header("Location: ../");
}

$loaded_user_license = null;
if(isset($_POST['txtUserLicense'])) {
  $_SESSION['loaded_user'] = $_POST['txtUserLicense'];
  $loaded_user_license = $_POST['txtUserLicense'];
} else if(isset($_SESSION['loaded_user'])) {
  $loaded_user_license = $_SESSION['loaded_user'];
}


$current_user = null;
if(isset($_POST['btnLoadUser'])) {
  $current_user = $admin->get_user($_POST['txtUserLicense']);
}

if($loaded_user_license != null) {
  $current_user = $admin->get_user($loaded_user_license);
}

$user_ps3_logins = null;
$user_site_logins = null;
$user_payments = null;
$user_request = null;
if($current_user != null) {
  $user_ps3_logins = $admin->get_user_ps3_logins($current_user['license']);
  $user_site_logins = $admin->get_user_site_logins($current_user['license']);
  $user_payments = $admin->get_user_payments($current_user['username']);
  $user_request = $admin->get_user_latest_request($current_user['license']);
}

if(isset($_POST['btnDeny'])) {
  if(isset($_POST['txtDenyReason'])) {
    $admin->set_user_request($user_request->id, $loaded_user_license, 1, $_POST['txtDenyReason'], user::$instance->username);
    $user_request = $admin->get_user_latest_request($current_user['license']);
  } else {
    //do error for no deny reason here.
  }
}
if(isset($_POST['btnGrant'])) {
  $admin->set_user_request($user_request->id, $loaded_user_license, 2, "", user::$instance->username);
  $user_request = $admin->get_user_latest_request($current_user['license']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>Genisys</title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />
  <link href="https://use.fontawesome.com/releases/v5.15.1/css/all.css" rel="stylesheet">
  <link href="../assets/font-awesome/css/font-awesome.min.css" rel="stylesheet">
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <!-- CSS Files -->
  <link href="../assets/css/datatables/dataTables.bootstrap.css" rel="stylesheet">
  <link href="../assets/css/black-dashboard.css" rel="stylesheet" />
  <link href="../assets/css/style.css" rel="stylesheet" />
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
            <a href="user">
              <i class="fa fa-fw fa-user"></i>
              <p>User</p>
            </a>
          </li>
          <li class="syn-li">
            <a href="users">
              <i class="fa fa-fw fa-user"></i>
              <p>Users</p>
            </a>
          </li>
          <li class="syn-li">
            <a href="../index">
              <i class="fa fa-fw fa-cogs"></i>
              <p>User Panel</p>
            </a>
          </li>
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
            <a class="navbar-brand" href="javascript:void(0)">User Management</a>
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
          <div class="col-md-2">
            <div class="card">
              <div class="card-header">
                <h5 class="title">User Action</h5>
              </div>
              <form action="" method="POST">
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-11 pr-md-1">
                      <div class="form-group">
                        <label>User License</label>
                        <input name="txtUserLicense" type="text" class="form-control" value="<?php if($loaded_user_license != null) echo $loaded_user_license;?>">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-footer">
                  <button name="btnLoadUser" type="submit" class="btn btn-fill btn-primary">Load User</button>
                </div>
              </form>
            </div>
          </div>
          <div class="col-md-10">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <div class="panel-title">User Information</div>
              </div>
              <div class="panel-body">
                <table id="loginlogs" class="table display table-hover" cellspacing="0" width="100%">
							    <thead>
								    <tr>
                      <th>Email</th>
								      <th>Username</th>
								      <th>License</th>
								      <th>Mac</th>
								      <th>PSID</th>
								      <th>Expiration Date</th>
                      <th>Banned</th>
                      <th>Set Lock</th>
								      <th>Rank</th>
								    </tr>
							    </thead>
                  <tbody>
                    <?php if(isset($current_user)): ?>
                    <tr>
                      <td><?php echo $current_user['email']; ?></td>
                      <td><?php echo $current_user['username']; ?></td>
                      <td><?php echo $current_user['license']; ?></td>
                      <td><?php echo $current_user['mac']; ?></td>
                      <td><?php echo $current_user['psid']; ?></td>
                      <td><?php echo date('m/d/Y g:i:s a', $current_user['enddate']) ?></td>
                      <td><?php echo ($current_user['banned'] == "1" ? "True" : "False"); ?></td>
                      <td><?php echo ($current_user['setlock'] == "1" ? "True" : "False"); ?></td>
                      <td><?php 
                      switch((int)$current_user['level']) {
                        case 0:
                          echo "User";
                          break;
                        case 1:
                          echo "Support";
                          break;
                        case 2:
                          echo "Admin";
                          break;
                      }
                      ?></td>
                    </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <div class="panel-title">User PS3 Logins</div>
              </div>
              <div class="panel-body">
                <table id="loginlogs" class="table datatable display table-hover" cellspacing="0" width="100%">
                  <thead>
								    <tr>
								      <th>Username</th>
								      <th>License</th>
								      <th>IP</th>
								      <th>Mac</th>
                      <th>PSID</th>
                      <th>Checksum</th>
                      <th>Version</th>
									    <th>Date</th>
									    <th>Info</th>
								    </tr>
							    </thead>
                  <tbody>
                    <?php
                    if(isset($user_ps3_logins)) {
                      foreach($user_ps3_logins as $row) {
                        echo "<tr>
                                <td>$row->username</td>
                                <td>$row->license</td>
                                <td>$row->ip</td>
                                <td>".($row->mac == "" ? "N/A" : $row->mac)."</td>
                                <td>".($row->psid == "" ? "N/A" : $row->psid)."</td>
                                <td>".($row->checksum == "" ? "N/A" : $row->checksum)."</td>
                                <td>".($row->version == "" ? "N/A" : $row->version)."</td>
                                <td>".date('m/d/Y g:i:s a', $row->date)."</td>
                                <td>$row->info</td>
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
        <div class="row">
          <div class="col-md-12">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <div class="panel-title">User Site Logins</div>
              </div>
              <div class="panel-body">
                <table id="loginlogs" class="table datatable display table-hover" cellspacing="0" width="100%">
                  <thead>
								    <tr>
								      <th>Username</th>
								      <th>License</th>
								      <th>IP</th>
								      <th>Mac</th>
                      <th>PSID</th>
                      <th>Checksum</th>
                      <th>Version</th>
									    <th>Date</th>
									    <th>Info</th>
								    </tr>
							    </thead>
                  <tbody>
                    <?php
                    if(isset($user_site_logins)) {
                      foreach($user_site_logins as $row) {
                        echo "<tr>
                                <td>$row->username</td>
                                <td>$row->license</td>
                                <td>$row->ip</td>
                                <td>".($row->mac == "" ? "N/A" : $row->mac)."</td>
                                <td>".($row->psid == "" ? "N/A" : $row->psid)."</td>
                                <td>".($row->checksum == "" ? "N/A" : $row->checksum)."</td>
                                <td>".($row->version == "" ? "N/A" : $row->version)."</td>
                                <td>".date('m/d/Y g:i:s a', $row->date)."</td>
                                <td>$row->info</td>
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
        <div class="row">
          <div class="col-md-12">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <div class="panel-title">User Payments</div>
              </div>
              <div class="panel-body">
                <table id="paymentlogs" class="table datatable display table-hover" cellspacing="0" width="100%">
                  <thead>
								    <tr>
                      <th>Type</th>
								      <th>Username</th>
								      <th>License</th>
								      <th>Price Paid</th>
                      <th>Date</th>
                      <?php if(user::$instance->level == 2) :?>
								      <th>Info</th>
                      <?php endif; ?>
								    </tr>
							    </thead>
                  <tbody>
                  <?php
                  if(isset($user_payments)) {
                      foreach($user_payments as $row) {
                        echo "<tr>
                                <td>".($row->type ? "Coinbase" : "Paypal")."
                                <td>$row->username</td>
                                <td>$row->license</td>
                                <td>$row->price_paid</td>
                                <td>".date('m/d/Y g:i:s a', $row->date)."</td>
                                ".(user::$instance->level == 2 ? "<td>$row->info</td>" : "")."
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
        <?php if($user_request != null) :?>
        <div class="row">
          <div class="col-md-12">
            <div class="card">
              <div class="card-header">
                <h5 class="title">Active Request</h5>
              </div>
              <form action="" method="POST">
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-3 pr-md-1">
                      <div class="form-group">
                        <label>Request Date</label>
                        <input type="text" class="form-control" disabled value="<?php echo date('m/d/Y g:i:s a', $user_request->requestdate); ?>">
                      </div>
                    </div>
                    <div class="col-md-3 px-md-1">
                      <div class="form-group">
                        <label>Filled Date</label>
                        <input type="text" class="form-control" disabled value="<?php echo ($user_request->filleddate == 0 ? "N/A" : date('m/d/Y g:i:s a', $user_request->filleddate)); ?>">
                      </div>
                    </div>
                    <div class="col-md-3 pl-md-1">
                      <div class="form-group">
                        <label>Has Time</label>
                        <input type="" class="form-control", disabled value="<?php echo ($user_request->time > strtotime("now") ? "Yes" : "No");?>">
                      </div>
                    </div>
                    <div class="col-md-3 pl-md-1">
                      <div class="form-group">
                        <label>Request Reason</label>
                        <input type="" class="form-control", disabled value="<?php echo $user_request->requestreason;?>">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-4 pr-md-1">
                      <div class="form-group">
                        <label>Deny Reason</label>
                        <input name="txtDenyReason" type="text" class="form-control">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-footer">
                  <button name="btnDeny" type="submit" class="btn btn-fill btn-primary">Deny</button>
                  <button name="btnGrant" type="submit" class="btn btn-fill btn-primary">Grant</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php endif; ?>
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
  <script src="../assets/js/core/jquery.min.js"></script>
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
  <!-- Chart JS -->
  <script src="../assets/js/plugins/Chart.js"></script>
  
  <script src="../assets/js/plugins/tablesorter/jquery.tablesorter.min.js"></script>

  <script src="../assets/js/plugins/datatables/jquery.dataTables.js"></script>
  <script src="../assets/js/plugins/datatables/dataTables.bootstrap.js"></script>
  <!--  Notifications Plugin    -->
  <script src="../assets/js/plugins/bootstrap-notify.js"></script>
  <script src="../assets/js/black-dashboard.js"></script>
</body>

</html>
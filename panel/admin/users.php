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

if(user::$instance->level == 2) {
  if(isset($_POST['btnBanUser']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $info = explode('|', $_POST['btnBanUser']);
    $query = $auth->db->prepare("UPDATE `users` SET `banned` = :banned WHERE `license` = :license");
    
    $query->execute(array(
      'banned' => !(bool)$info[1],
      'license' => $info[0]
    ));
  }
}

$users = $admin->get_all_users();
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
          <li class="syn-li">
            <a href="user">
              <i class="fa fa-fw fa-user"></i>
              <p>User</p>
            </a>
          </li>
          <li class="syn-li active">
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
            <a class="navbar-brand" href="javascript:void(0)">Users Management</a>
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
          <div class="col-md-12">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <div class="panel-title">All Users</div>
              </div>
              <div class="panel-body">
                <table id="allusers" class="table datatable2 display table-hover" cellspacing="0" width="100%">
                  <thead>
								    <tr>
                      <th>ID</th>
									    <th>Username</th>
									    <th>Email</th>
									    <th>License</th>
									    <th>Mac</th>
									    <th>PSID</th>
									    <th>Expiration Date</th>
									    <th>Rank</th>
                      <th>Banned</th>
                      <th>Set Lock</th>
								    </tr>
							    </thead>
                  <tbody>
                  <?php
                    if(isset($users)) {
                      foreach($users as $row) {
                        $btnString = "<td><button class=\"btn btn-primary\" value=\"$row->license|$row->banned\" name=\"btnBanUser\" type=\"submit\" style=\"padding: revert;\">".($row->banned ? "Unban" : "Ban")."</button></td>";
                        $slev = "N/A";
                        switch((int)$row->level) {
                          case 0:
                            $slev = "User";
                            break;
                          case 1:
                            $slev = "Support";
                            break;
                          case 2:
                            $slev = "Admin";
                            break;
                        }
                        echo "<tr>
                                <form action=\"\" method=\"POST\">
                                  <td>$row->id</td>
                                  <td>$row->username</td>
                                  <td>$row->email</td>
                                  <td>$row->license</td>
                                  <td>".($row->mac == NULL ? "N/A" : $row->mac)."</td>
                                  <td>".($row->psid == NULL ? "N/A" : $row->psid)."</td>
                                  <td>".($row->enddate == 0 ? "N/A" : date('m-d-Y g:i:s a', $row->enddate))."</td>
                                  <td>$slev</td>".
                                  (user::$instance->level == 2 ? $btnString : "<td>".($row->banned ? "Yes" : "No")."</td>")
                                  ."<td>".($row->setlock ? "Yes" : "No")."</td>
                                </form>
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
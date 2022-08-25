<!doctype html>
<?php
  require_once("kong/kong_admin_api.php");
  require_once("secrets.php");
  require("kong/AclGroups.php");
  require("kong/Consumers.php");
  require("kong/AclPluginInstances.php");
  require("kong/Services.php");
  require("kong/Routes.php");
?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Developer overview and notifier</title>

    <link rel="icon" type="image/x-icon" href="/favicon.png">
    <!-- Bootstrap -->
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!--[if lt IE 9]>
      <script src="https://cdn.jsdelivr.net/npm/html5shiv@3.7.3/dist/html5shiv.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/respond.js@1.4.2/dest/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>

  <nav class="navbar navbar-expand-lg navbar-light bg-light ">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Kong Developer Notifier</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarScroll">
      <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="/">ACL Groups</a>
        </li>
    </div>
  </div>
</nav>

    <div class="mt-4 p-5 bg-secondary text-white rounded d-flex justify-content-center align-items-center">

      <div class="starter-template">
        <h1>ACL Groups</h1>
        <p class="lead">The power of having an API</p>
        <p>Choose your workspace</p>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
          <div class="form-row row vertical-center-row" style="max-width:300px">
            <div class="form-group col-md-8">
              <select name="workspace" class="form-select">
                <?php
                  $workspaces = kong_admin_api_call("/workspaces");
                  if(isset($_GET["workspace"])) {
                    // when this select has been sent
                    $selected_workspace = $_GET["workspace"];
                  } else if(isset($_POST["workspace"])) {
                    // when we get the workspace from the send_mail functionality
                    $selected_workspace = $_POST["workspace"];
                  } else { 
                    $selected_workspace = "default";
                  }
                  foreach ($workspaces["json"]->data as $workspace) {
                      if($selected_workspace == $workspace->name) {
                        echo "<option value=\"" . $workspace->name . "\" selected>" . $workspace->name . "</option>";
                      } else {
                        echo "<option value=\"" . $workspace->name . "\">" . $workspace->name . "</option>";
                      }
                  }
                ?>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group col-md-4">
                <button type="submit" class="btn btn-primary">Select</button>
              </div>
            </div>
          </div>
        </form>
      </div>
      


    </div><!-- /.container -->
  
    <div class="container">


    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">

      
      <?php

          $consumers = fetch_all_consumers($selected_workspace);
          $groups = fetch_acl_groups($selected_workspace, $consumers);
          $pluginInstances = fetch_all_acl_plugin_instances($selected_workspace);         
          $services = fetch_all_services_names($selected_workspace);
          $routes = fetch_all_routes_names($selected_workspace);


          foreach ($groups as $group) {
            echo "<div class=\"input-group\">";
            echo "<h2 ><input type=\"radio\" id=\"$group->name\" name=\"selected_group\" value=\"$group->name\" />&nbsp;".$group->name."</h2>";
            echo "</div>";

            // Consumers
            echo "<div class=\"row align-items-start\"  style=\"border-bottom:1px solid #777\">";
              echo "<div class=\"col\">";
                echo "<table class=\"table table-striped table-light table-bordered\"><thead><tr><th>Consumers</th><tbody>";
                  foreach ($group->consumers as $consumer) {
                    echo "<tr><td><a href=\"" . $config->manager_url . "/" . $selected_workspace . "/consumers/" .$consumer->username . "/#credentials\" target=\”_blank\”>" . $consumer->username;
                    if(isset($consumer->custom_id)) {
                      echo " (" . $consumer->custom_id . ")";
                    }
                    echo "</a></td></tr></tr";
                  }
                echo "</tbody></table>";

              // Allow
              echo "</div><div class=\"col\">";
              echo "<table class=\"table table-striped table-success table-bordered\"><thead><tr><th>Allow</th><tbody>";
              foreach ($pluginInstances as $instance) {
                if(isset($instance->allow)) {
                  if(in_array($group->name,$instance->allow)) {
                    if(isset($instance->service)) {
                      echo "<tr><td><span class=\"badge bg-secondary\">Service</span> <a href=\"" . $config->manager_url . "/" . $selected_workspace . "/plugins/acl/" . $instance->id . "\" target=\"_blank\">". $services[$instance->service->id] ."</a></td><tr>";
                    } else if(isset($instance->route)) {
                      echo "<tr><td><span class=\"badge bg-primary\">Route</span> <a href=\"" . $config->manager_url . "/" . $selected_workspace . "/plugins/acl/" . $instance->id . "\" target=\"_blank\">". $routes[$instance->route->id] ."</a></td><tr>";
                    } else {
                      echo "<tr><td><span class=\"badge bg-dark\">Global</span> <a href=\"" . $config->manager_url . "/" . $selected_workspace . "/plugins/acl/" . $instance->id . "\" target=\"_blank\">". $selected_workspace ."</a></td><tr>";
                    }
                  }
                }
              }
              echo "</tbody></table>";
    
              // Deny
              echo "</div><div class=\"col\">";
              echo "<table class=\"table table-striped table-danger table-bordered\"><thead><tr><th>Deny</th><tbody>";
              foreach ($pluginInstances as $instance) {
                if(isset($instance->deny)) {
                  if(in_array($group->name,$instance->deny)) {
                    if(isset($instance->service)) {
                      echo "<tr><td><span class=\"badge bg-secondary\">Service</span> <a href=\"" . $config->manager_url . "/" . $selected_workspace . "/plugins/acl/" . $instance->id . "\" target=\"_blank\">". $services[$instance->service->id] ."</a></td><tr>";
                    } else if(isset($instance->route)) {
                      echo "<tr><td><span class=\"badge bg-primary\">Route</span> <a href=\"" . $config->manager_url . "/" . $selected_workspace . "/plugins/acl/" . $instance->id . "\" target=\"_blank\">". $routes[$instance->route->id] ."</a></td><tr>";
                    } else {
                      echo "<tr><td><span class=\"badge bg-dark\">Global</span> <a href=\"" . $config->manager_url . "/" . $selected_workspace . "/plugins/acl/" . $instance->id . "\" target=\"_blank\">". $selected_workspace ."</a></td><tr>";
                    }
                  }
                }
              }
              echo "</tbody></table>";
            echo "</div></div>";
          }

          echo "<hr />";

          if(isset($_POST["submit_email"])) {
            if(!isset($_POST["selected_group"])) {
              echo "<div class=\"alert alert-danger\" role=\"alert\">Please select a group</div>";
            } else if(empty($_POST["subject"]) || empty($_POST["title"]) || empty($_POST["text"])) {
              echo "<div class=\"alert alert-danger\" role=\"alert\">Please fill all fields</div>";
            }
            else
            {
              echo "<h1>Sending emails to group " . $_POST["selected_group"] . "</h1>";
              $emailRecipients;
              foreach ($groups[$_POST["selected_group"]]->consumers as $consumer) {
                if (filter_var($consumer->username, FILTER_VALIDATE_EMAIL)) {
                  $emailRecipients[] = $consumer->username;
                  continue;
                } else if (filter_var($consumer->custom_id, FILTER_VALIDATE_EMAIL)) {
                  $emailRecipients[] = $consumer->custom_id;
                  continue;
                }
                echo "<div class=\"alert alert-warning\" role=\"alert\">
                No valid email address found for consumer ".$consumer->username."
                </div>";
              }
              echo "</ul>";
              if(empty($emailRecipients)) {
                echo "<div class=\"alert alert-danger\" role=\"alert\">
                No valid email address found for any consumer in group ".$_POST["selected_group"]."
                </div>";
              } else {
                echo "<div class=\"alert alert-success\" role=\"alert\">
                Sending email to ".count($emailRecipients)." recipient(s)
                </div>";
                foreach ($emailRecipients as $emailRecipient) {
                  require("send_email.php");
                  $emailResponse = send_email($emailRecipient, $emailRecipient, $_POST["subject"] , "<h1>". $_POST["text"] . "</h1><p>" .$_POST["text"] ."</p>", $config->email_smtp_username, $config->email_smtp_password);
                  
                  if(!empty($emailResponse)) {
                    echo "<div class=\"alert alert-danger\" role=\"alert\">
                    Error sending email to ".$emailRecipient.": ". $emailResponse ."
                    </div>";
                  } else {
                    echo "<div class=\"alert alert-success\" role=\"alert\">
                    Email sent to ".$emailRecipient."
                    </div>";
                  }
                }
              }
            }
          }
        ?>


        <h4>Send email</h4>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="subject">Subject</label>
	          <input name="subject" value="<?php echo @$_POST['subject'] ?>" type="text" class="form-control" id="subject" aria-describedby="subjectHelp" placeholder="News about your API subscription">
            <small id="subjectHelp" class="form-text text-muted">The subject of the email</small>
          </div>
          <div class="form-group col-md-6">
            <label for="title">Title</label>
	          <input name="title" value="<?php echo @$_POST['title'] ?>" type="text" class="form-control" id="subject" aria-describedby="titleHelp" placeholder="Existing update for your API">
            <small id="titleHelp" class="form-text text-muted">The title in the text body of the email</small>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-12">
            <label for="text">Text</label>
	          <textarea name="text" class="form-control" id="text" aria-describedby="textHelp" placeholder="The text itself"><?php echo @$_POST['text'] ?></textarea>
            <small id="textHelp" class="form-text text-muted">The whole text yu want to write. You an add HTML in here</small>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-12">
            <button type="submit" class="btn btn-primary" name="submit_email">Send email</button>
          </div>
        </div>
        </div>
      </form>


    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  </body>
</html>

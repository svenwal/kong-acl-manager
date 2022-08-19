<!doctype html>
<?php
  require("kong_admin_api.php");
?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Developer overview and notifier</title>

    <link rel="icon" type="image/x-icon" href="/favicon.png">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">
   <link rel="stylesheet" href="./css/custom.css" >

    <!--[if lt IE 9]>
      <script src="https://cdn.jsdelivr.net/npm/html5shiv@3.7.3/dist/html5shiv.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/respond.js@1.4.2/dest/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Kong Notifier</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="#">View groups</a></li>
            <li><a href="mailto:sven@konghq.com">Contact</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container jumbotron">

      <div class="starter-template">
        <h1>ACL Groups</h1>
        <p class="lead">The power of having an API</p>
        <p>Choose your workspace</p>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
          <div class="form-row row vertical-center-row" style="max-width:300px">
            <div class="form-group col-md-8">
              <select name="workspace" class="form-control">
                <?php
                  $workspaces = kong_admin_api_call("/workspaces");
                  if(isset($_GET["workspace"])) {
                    $selected_workspace = $_GET["workspace"];
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
          class AclGroup
          {
            public $name;
            public $consumersIds;
            public $consumers;
          }

          class Consumer 
          {
            public $id;
            public $username;
            public $custom_id;
          }




          $consumers = kong_admin_api_call("/consumers", $selected_workspace);
          //print_r($consumers["json"]->data);
          //$consumers = array();
          foreach ($consumers["json"]->data as $consumer) {
            $consumerObject = new Consumer();
            $consumerObject->id = $consumer->id;
            $consumerObject->username = $consumer->username;
            $consumerObject->custom_id = $consumer->custom_id;
            $consumers[$consumerObject->id] = $consumerObject;
          }


          $acls = kong_admin_api_call("/acls", $selected_workspace);
          $groups = array();

          foreach ($acls["json"]->data as $group) {
            $groupObject = new AclGroup();
            $groupObject->name = $group->group;
            $groupObject->consumersIds[] = $group->consumer->id;
            if (isset($groups[$groupObject->name])) {
              $groups[$groupObject->name]->consumersIds[] = $group->consumer->id;
              $groups[$groupObject->name]->consumers[] = $consumers[$group->consumer->id];
            } else {
              $groups[$groupObject->name] = $groupObject;
              $groups[$groupObject->name]->consumers[] = $consumers[$group->consumer->id];
            }
            

          }


          foreach ($groups as $group) {
            echo "<h2><input type=\"radio\" id=\"$group->name\" name=\"selected_group\" value=\"$group->name\" />&nbsp;".$group->name."</h2>";
            echo "<ul>";
            foreach ($group->consumers as $consumer) {
              echo "<li>".$consumer->username;
              if(isset($consumer->custom_id)) {
                echo " (" . $consumer->custom_id . ")";
              }
              echo "</li>";
            }
            echo "</ul>";

          }

          echo "<hr />";
        ?>

<h4>Send email</h4>
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
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
	          <textarea name="text" value="<?php echo @$_POST['text'] ?>" class="form-control" id="text" aria-describedby="textHelp" placeholder="The text itself"></textarea>
            <small id="textHelp" class="form-text text-muted">The whole text yu want to write. You an add HTML in here</small>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-12">
            <button disabled type="submit" class="btn btn-primary">Send email (will be added later)</button>
          </div>
        </div>
        </div>
      </form>


    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha384-nvAa0+6Qg9clwYCGGPpDQLVpLNn0fRaROjHqs13t4Ggj3Ez50XnGQqc/r8MhnRDZ" crossorigin="anonymous"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
  </body>
</html>

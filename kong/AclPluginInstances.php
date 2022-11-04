<?php
    /* This file hosts all instances of the ACL plugin */
    require_once("load_config.php");
    require_once("kong/kong_admin_api.php");

    class AclPluginInstance
    {
      public $id;
      public $allow;
      public $deny;
      public $global;
      public $service;
      public $route;
    }

    function fetch_all_acl_plugin_instances($workspace) {
        $pluginInstances = array();
        $pluginInstancesResponse = kong_admin_api_call("/plugins?name=acl", $workspace);
        foreach ($pluginInstancesResponse["json"]->data as $instance) {
          $instanceObject = new AclPluginInstance();
          $instanceObject->id = $instance->id;
          $instanceObject->allow = $instance->config->allow;
          $instanceObject->deny = $instance->config->deny;
          $instanceObject->service = $instance->service;
          $instanceObject->route = $instance->route;
          $pluginInstances[$instanceObject->id] = $instanceObject;
        }
        return $pluginInstances;
    }

?>
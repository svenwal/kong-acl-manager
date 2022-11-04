<?php
    /* This file fetches all services */
    require_once("load_config.php");
    require_once("kong/kong_admin_api.php");

    function fetch_all_services_names($workspace, $all_services = null) {
        $services = array();
        if(!isset($all_services)) {
            $servicesResponse = kong_admin_api_call("/services", $workspace);
        }
        else
        {
            $servicesResponse = $all_services;
        }
        $services = array_column($servicesResponse["json"]->data, 'name', 'id');
        return $services;
    }
?>
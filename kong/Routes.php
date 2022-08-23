<?php
    /* This file fetches all Routes */
    require_once("secrets.php");
    require_once("kong/kong_admin_api.php");

    function fetch_all_routes_names($workspace, $all_Routes = null) {
        $routes = array();
        if(!isset($all_Routes)) {
            $routesResponse = kong_admin_api_call("/routes", $workspace);
        }
        else
        {
            $routesResponse = $all_Routes;
        }
        $routes = array_column($routesResponse["json"]->data, 'name', 'id');
        return $routes;
    }
?>
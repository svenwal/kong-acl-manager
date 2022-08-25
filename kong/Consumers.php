<?php
    require_once("secrets.php");
    require_once("kong/kong_admin_api.php");

    class Consumer 
    {
        public $id;
        public $username;
        public $custom_id;
    }

    function fetch_all_consumers($workspace) {
        $consumers = array();
        $consumersResponse = kong_admin_api_call("/consumers", $workspace);
        foreach ($consumersResponse["json"]->data as $consumer) {
            $consumerObject = new Consumer();
            $consumerObject->id = $consumer->id;
            $consumerObject->username = $consumer->username;
            $consumerObject->custom_id = $consumer->custom_id;
            $consumers[$consumerObject->id] = $consumerObject;
        }
        return $consumers;
    }

?>

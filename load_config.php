<?php
    /*
    Loads the configuration.
    
    First it will try to load the configuration from the file secrets.php.
    Second it will try to load the configuration from the environment variables (env vars will override the secrets.php file).
    
    Checks mandatory configuration parameters and throws an exception if any of them is missing.

    */
    if (file_exists("secrets.php")) {
        require_once("secrets.php");
        error_log("secrets.php loaded", 3, "/dev/stdout");
    }
    else
    {
        $config = $object = new stdClass();
        error_log("secrets.php not found", 3, "/dev/stdout");
    }

    $all_headers = getallheaders();

    load_config_from_header("admin_api_url", true, $config);
    load_config_from_header("admin_api_token", false, $config);
    load_config_from_header("manager_url", true, $config);
    load_config_from_header("email_smtp_host", true, $config);
    load_config_from_header("email_smtp_port", true, $config);
    load_config_from_header("email_smtp_username", true, $config);
    load_config_from_header("email_smtp_password", true, $config);
    load_config_from_header("email_from_address", true, $config);


    function load_config_from_header(string $option_name, bool $is_mandatory, &$config) {
        $header_value=@$all_headers["CONFIG_" + strtoupper($option_name)];
        if (!empty($header_value)) {
            $config->$option_name=$header_value;
        }
        if (empty($config->$option_name) && is_mandatory) {
            error_log($option_name + " not set in secrets.php or header");
            die($option_name + " not set in secrets.php or header");
        }
    }

?>
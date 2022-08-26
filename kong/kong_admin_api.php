<?php
function kong_admin_api_call($path, $workspace = "default", $method = "get", $addtitonal_headers = array(), $body = "") {
    require("secrets.php");
    $url = $config->admin_api_url . "/" . $workspace . $path;
    $headers = array(
        "Content-Type: application/json",
        "Accept: application/json",
        "Kong-Admin-Token: " . $config->admin_api_token
    );
    $headers = array_merge($headers, $addtitonal_headers);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($method == "post") {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return array("http_code" => $http_code, "raw_response" => $response, "json" => json_decode($response, false));
}
?>

<?php
$fb_page_id ="24705XXX";
$access_token_dummy=file("./token.txt");
$access_token=$access_token_dummy[0];
$fields="id,name,description,location,venue,timezone,start_time,cover";
$api_key="16XXXXX"; 
$app_secret="2d0bXXXXXXXXXX";
$token_url="https://graph.facebook.com/oauth/access_token?client_id=".$api_key."&client_secret=".$app_secret."&grant_type=client_credentials";
$response = file_get_contents($token_url);
$params = null;
parse_str($response, $params);
$access_token = $params['access_token'];   
if(trim( $access_token)!=""){
    $datei = fopen("./token.txt","w");
    fwrite($datei,  $access_token);    
    fclose($datei);
}
?>

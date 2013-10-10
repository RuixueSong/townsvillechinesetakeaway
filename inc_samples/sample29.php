<?php

//###This sample will show how to use Filepicker.io to upload document and get it's URL
//### Set variables and get POST data

if (isset($_POST) AND !empty($_POST)) {
    //Get json object from raw data with request parameters
    //Get parameters from json object
    $url = $_POST['url'];
    //Get base path
    $basePath = $_POST["server_type"];
    //Get user id
    $clientId = $_POST["clientId"];
    if ($basePath == "") {
        //If base base is empty seting base path to prod server
        $basePath = 'https://api.groupdocs.com/v2.0';
    }
    //###Generate iframe url for chosen server
    if (isset($url) && !empty($url)) {
        if ($basePath == "https://api.groupdocs.com/v2.0") {
            $iframe = 'https://apps.groupdocs.com/document-viewer/embed?url=' . $url . '&user_id=' . $clientId;
            //iframe to dev server
        } elseif ($basePath == "https://dev-api.groupdocs.com/v2.0") {
            $iframe = 'https://dev-apps.groupdocs.com/document-viewer/embed?url=' . $url . '&user_id=' . $clientId;
            
            //iframe to test server
        } elseif ($basePath == "https://stage-apps-groupdocs.dynabic.com/v2.0") {
            $iframe = 'https://stage-apps-groupdocs.dynabic.com/document-viewer/embed?url=' . $url . '&user_id=' . $clientId;
            
        } elseif ($basePath == "http://realtime-api.groupdocs.com") {
            $iframe = 'http://realtime-apps.groupdocs.com/document-viewer/embed?url=' . $url . '&user_id=' . $clientId;
            
        }
        //Create json string with result data
        $result = json_encode(array("iframe" => $iframe, "error" => $error));
    }
}
//Check is result genarated or not
if (isset($result) && !empty($result)) {
    //If generated show send ifarme url
    header('Content-type: application/json');
    echo $result;
} else {
    //If not process template
    echo Template::serve('sample29.htm');
}
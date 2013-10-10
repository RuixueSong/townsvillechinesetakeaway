<?php

//###<i>This sample will show how to use <b>Signer object</b> to be authorized at GroupDocs and how to get GroupDocs user information using PHP SDK</i>
//Set variables and get POST data
F3::set('email', '');
F3::set('password', '');
$login = F3::get('POST["login"]');
$password = F3::get('POST["password"]');

function Login($login, $password) {
    //Check is all data entered
    if (!isset($login) || !isset($password)) {
        throw new Exception("Please enter login and password");
    }
    $basePath = f3::get('POST["server_type"]');
    //Check if user entered base path
    if ($basePath == "") {
        //If base base is empty seting base path to prod server
        $basePath = 'https://api.groupdocs.com/v2.0';
    }
    //Create signer object
    $signer = new GroupDocsRequestSigner("123");
    //Create apiClient object
    $apiClient = new APIClient($signer);
    //Creaet Shared object
    $shared = new SharedApi($apiClient);
    //Set base path
    $shared->setBasePath($basePath);
    //Set empty variable for result
    $result = "";
    //Login and get user data
    $userData = $shared->LoginUser($login, $password);
    //Check status
    if ($userData->status == "Ok") {
        //If status Ok get all user data
        $result = $userData->result->user;
    } else {
        throw new Exception($userData->error_message);
    }
    //Return user data for template
    return f3::set("userInfo", $result);
}

try {
    Login($login, $password);
} catch (Exception $e) {
    $error = 'ERROR: ' . $e->getMessage() . "\n";
    f3::set('error', $error);
}
//Process template
echo Template::serve('sample26.htm');
<?php

//###<i>This sample will show how to use <b>ListEntities</b> method from Storage  API  to list files within GroupDocs Storage</i>
//Set variables and get POST data

$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');

//###Check clientId and privateKey
if (empty($clientId) || empty($privateKey)) {
    $error = 'Please enter all required parameters';
    f3::set('error', $error);
} else {
    //Get base path
    $basePath = f3::get('POST["server_type"]');
    //###Create Signer, ApiClient and Storage Api objects
    //Create signer object
    $signer = new GroupDocsRequestSigner($privateKey);
    //Create apiClient object
    $apiClient = new APIClient($signer); // PHP SDK V1.1
    //Create Storage Api object
    $storageApi = new StorageApi($apiClient);
    //Check if user entered base path
    if ($basePath == "") {
        //If base base is empty seting base path to prod server
        $basePath = 'https://api.groupdocs.com/v2.0';
    }
    //Set base path
    $storageApi->setBasePath($basePath);
    //###Make a request to Storage API using clientId
    //Obtaining all Entities from current user
    try {
        $files = $storageApi->ListEntities($clientId, '', 0);
        if ($files->status == "Ok") {
            //Obtaining file names
            $name = '';
            foreach ($files->result->files as $item) {
                $name .= $item->name . '<br>';
            }

            //If request was successfull - set filelist variable for template
            F3::set('filelist', $name);
        } else {
            throw new Exception($files->error_message);
        }
    } catch (Exception $e) {
        $error = 'ERROR: ' . $e->getMessage() . "\n";
        f3::set('error', $error);
    }
}
//Process template
F3::set('userId', $clientId);
F3::set('privateKey', $privateKey);
echo Template::serve('sample02.htm');
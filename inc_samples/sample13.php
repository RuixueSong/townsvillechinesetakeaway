<?php

//### This sample will show how to add collaborator to doc with annotations
// Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
F3::set('fileId', '');
F3::set('collaborations', '');
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
$collaborations = array(F3::get('POST["email"]'));
// Remove NULL value
$collaborations = (is_array($collaborations)) ? array_filter($collaborations, 'strlen') : array();
if (empty($clientId) || empty($privateKey) || (is_array($collaborations) && !count($collaborations))) {
    $error = 'Please enter all required parameters';
    f3::set('error', $error);
} else {
    //Get base path
    $basePath = f3::get('POST["server_type"]');
    //Get entered by user data
    $fileGuId = F3::get('POST["fileId"]');
    $url = F3::get('POST["url"]');
    $file = $_FILES['file'];
    F3::set('userId', $clientId);
    F3::set('privateKey', $privateKey);
    F3::set('collaborations', $collaborations);
    //### Create Signer, ApiClient and Annotation Api objects
    // Create signer object
    $signer = new GroupDocsRequestSigner($privateKey);
    // Create apiClient object
    $apiClient = new ApiClient($signer);
    // Create Annotation object
    $antApi = new AntApi($apiClient);
    //Create Storage Api object
    $storageApi = new StorageApi($apiClient);
    if ($basePath == "") {
        //If base base is empty seting base path to prod server
        $basePath = 'https://api.groupdocs.com/v2.0';
    }
    //Set base path
    $antApi->setBasePath($basePath);
    $storageApi->setBasePath($basePath);
    //Check if user choose upload file from URL
    if ($url != "") {
        $fileGuId = "";
        //Upload file from URL
        $uploadResult = $storageApi->UploadWeb($clientId, $url);
        //Check is file uploaded
        try {
            if ($uploadResult->status == "Ok") {
                //Get file GUID
                $fileId = $uploadResult->result->guid;
                //If it isn't uploaded throw exception to template
            } else {
                throw new Exception($uploadResult->error_message);
            }
        } catch (Exception $e) {
            $error = 'ERROR: ' . $e->getMessage() . "\n";
            f3::set('error', $error);
        }
    }
    //Check is user choose upload local file
    if ($_FILES['file']["name"] != "") {
        $fileGuId = "";
        //Temp name of the file
        $tmpName = $file['tmp_name'];
        //Original name of the file
        $name = $file['name'];
        //Creat file stream
        $fs = FileStream::fromFile($tmpName);
        //###Make a request to Storage API using clientId
        //Upload file to current user storage
        try {
            $uploadResult = $storageApi->Upload($clientId, $name, 'uploaded', "", $fs);

            //###Check if file uploaded successfully
            if ($uploadResult->status == "Ok") {
                //Get file GUID
                $fileId = $uploadResult->result->guid;
                //If it isn't uploaded throw exception to template
            } else {
                throw new Exception($uploadResult->error_message);
            }
        } catch (Exception $e) {
            $error = 'ERROR: ' . $e->getMessage() . "\n";
            f3::set('error', $error);
        }
    }
    if ($fileGuId != "") {
        $fileId = $fileGuId;
        F3::set('fileId', $fileGuId);
    }
    // Make a request to Annotation API using clientId and fileId
    try {
        $response = $antApi->SetAnnotationCollaborators($clientId, $fileId, "v2.0", $collaborations);
        if ($response->status == "Ok") {
            // Check the result of the request
            if (isset($response->result)) {
                // If request was successfull - set annotations variable for template
                F3::set('result', $response->result);
            }
        } else {
            throw new Exception($response->error_message);
        }
    } catch (Exception $e) {
        $error = 'ERROR: ' . $e->getMessage() . "\n";
        f3::set('error', $error);
    }
}
// Process template
echo Template::serve('sample13.htm');
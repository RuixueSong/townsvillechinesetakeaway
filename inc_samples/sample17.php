<?php

//### This sample will how to upload a file into the storage and compress it into zip archive using PHP SDK
// Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
F3::set('fileId', '');
F3::set('message', '');
F3::set('iframe', '');
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
//###Check clientId and privateKey
if (empty($clientId) || empty($privateKey)) {
    $error = 'Please enter all required parameters';
    f3::set('error', $error);
} else {
    //Get base path
    $basePath = f3::get('POST["server_type"]');
    // Deleting of tags, slashes and  space from clientId and privateKey
    $clientID = strip_tags(stripslashes(trim($clientId))); //ClientId==UserId
    $apiKey = strip_tags(stripslashes(trim($privateKey))); //ApiKey==PrivateKey
    //### Create Signer, ApiClient and Storage Api objects
    // Create signer object
    $signer = new GroupDocsRequestSigner($apiKey);
    // Create apiClient object
    $apiClient = new APIClient($signer);
    // Create Storage Api object
    $storageApi = new StorageApi($apiClient);
    $id = "";
    if ($basePath == "") {
        //If base base is empty seting base path to prod server
        $basePath = 'https://api.groupdocs.com/v2.0';
    }
    //Set base path
    $storageApi->setBasePath($basePath);
    $url = F3::get('POST["url"]');
    //Check is URL entered
    if ($url != "") {
        //Upload file from URL
        try {
            $uploadResult = $storageApi->UploadWeb($clientID, $url);
            //CHeck upload status
            if ($uploadResult->status == "Ok") {
                //Get uploaded file GUID
                $id = $uploadResult->result->id;
            } else {
                throw new Exception($uploadResult->error_message);
            }
        } catch (Exception $e) {
            $error = 'ERROR: ' . $e->getMessage() . "\n";
            f3::set('error', $error);
        }
    } else {
        // Get uploaded file
        $uploadedFile = $_FILES['file'];
        //###Check uploaded file
        if (null === $uploadedFile) {
            return new RedirectResponse("/sample17");
        }
        // Temp name of the file
        $tmpName = $uploadedFile['tmp_name'];
        // Original name of the file
        $name = $uploadedFile['name'];
        // Creat file stream
        $fs = FileStream::fromFile($tmpName);
        //### Make a request to Storage API using clientId
        // Upload file to current user storage
        try {
            $uploadResult = $storageApi->Upload($clientID, $name, 'uploaded', "", $fs);
            if ($uploadResult->status == "Ok") {
                $id = $uploadResult->result->id;
            } else {
                throw new Exception($uploadResult->error_message);
            }
        } catch (Exception $e) {
            $error = 'ERROR: ' . $e->getMessage() . "\n";
            f3::set('error', $error);
        }
    }
    $result = array();
    //### Check if file uploaded successfully
    // compress uploaded file into "zip" archive
    try {
        $compress = $storageApi->Compress($clientID, $id, "zip");
        if ($compress->status == "Ok") {
            // Generation of Embeded Viewer URL with uploaded file GuId
            $result = preg_replace("/\.[a-z]{3}/", ".zip", $name);
            $message = '<p>Archive created and saved successfully as ' . $result;
            F3::set('message', $message);
        } else {
            throw new Exception($compress->error_message);
        }
    } catch (Exception $e) {
        $error = 'ERROR: ' . $e->getMessage() . "\n";
        f3::set('error', $error);
    }
}

// Process template
F3::set('userId', $clientId);
F3::set('privateKey', $privateKey);
echo Template::serve('sample17.htm');
<?php

//###<i>This sample will show how to use <b>ShareDocument</b> method from Doc Api to share a document to other users</i>
//Set variables and get POST data

F3::set('userId', '');
F3::set('privateKey', '');
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
$email = f3::get('POST["email"]');
$sharer = array($email);
//### Check file id, user, private key and body
if ($clientId == "" || $privateKey == "" || $sharer == "") {
    $error = 'Please enter all required parameters';
    f3::set('error', $error);
} else {
    //Get base path
    $basePath = f3::get('POST["server_type"]');
    //Get entered by user data
    $url = F3::get('POST["url"]');
    $file = $_FILES['file'];
    $fileId = f3::get('POST["fileId"]');
    $file_id = "";
    //###Create Signer, ApiClient and Storage Api objects
    //Create signer object
    $signer = new GroupDocsRequestSigner($privateKey);
    //Create apiClient object
    $apiClient = new APIClient($signer);
    //Create Storage Api object
    $storageApi = new StorageApi($apiClient);
    if ($basePath == "") {
        //If base base is empty seting base path to prod server
        $basePath = 'https://api.groupdocs.com/v2.0';
    }
    //Set base path
    $storageApi->setBasePath($basePath);
    //Check if user choose upload file from URL
    if ($url != "") {
        //Upload file from URL
        try {
            $uploadResult = $storageApi->UploadWeb($clientId, $url);
            //Check is file uploaded
            if ($uploadResult->status == "Ok") {
                //Get file GUID
                $file_id = $uploadResult->result->id;
                $fileId = "";
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
                $file_id = $uploadResult->result->id;
                $fileId = "";

                //If it isn't uploaded throw exception to template
            } else {
                throw new Exception($uploadResult->error_message);
            }
        } catch (Exception $e) {
            $error = 'ERROR: ' . $e->getMessage() . "\n";
            f3::set('error', $error);
        }
    }
    if ($fileId != "") {
        //###Make request to Storage
        //Geting all Entities from current user
        try {
            $files = $storageApi->ListEntities($clientId, '', 0);
            if ($files->status == "Ok") {
                //Selecting file names
                $name = '';
                foreach ($files->result->files as $item) {
                    if ($item->guid == $fileId) {
                        $name = $item->name;
                        $file_id = $item->id;
                    }
                }
               
            } else {
                throw new Exception($files->error_message);
            }
        } catch (Exception $e) {
            $error = 'ERROR: ' . $e->getMessage() . "\n";
            f3::set('error', $error);
        }
    }
    //###Create DocApi object
    $docApi = new DocApi($apiClient);
    $docApi->setBasePath($basePath);
    //Make request to user storage for sharing document
    try {
        $share = $docApi->ShareDocument($clientId, $file_id, $sharer);
        if ($share->status == "Ok") {
            //If request was successfull - set shared variable for template
            f3::set('shared', $sharer['0']);
            f3::set('fileId', $file_id);
        } else {
            throw new Exception($share->error_message);
        }
    } catch (Exception $e) {
        $error = 'ERROR: ' . $e->getMessage() . "\n";
        f3::set('error', $error);
    }
}
//Process template
F3::set('userId', $clientId);
F3::set('privateKey', $privateKey);
f3::set('email', $email);
echo Template::serve('sample10.htm');
<?php

//###<i>This sample will show how to use <b>MoveFile</b> method from Storage Api to copy/move a file in GroupDocs Storage </i>
//Set variables and get POST data
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');

$copy = F3::get('POST["copy"]');
$move = F3::get('POST["move"]');
$folder = F3::get('POST["destPath"]');

//###Check clientId, privateKey and file Id
if (!isset($clientId) || !isset($privateKey) || $folder == "") {
    $error = 'Please enter all required parameters';
    f3::set('error', $error);
} else {
    //Get base path
    $basePath = f3::get('POST["server_type"]');
    //###Create Signer, ApiClient and Storage Api objects
    //Create signer object
    $signer = new GroupDocsRequestSigner($privateKey);
    //Create apiClient object
    $apiClient = new APIClient($signer);
    //Create Storage Api object
    $storageApi = new StorageApi($apiClient);
    //Check if user entered base path
    if ($basePath == "") {
        //If base base is empty seting base path to prod server
        $basePath = 'https://api.groupdocs.com/v2.0';
    }
    //Set base path
    $storageApi->setBasePath($basePath);
    //Set empty file id
    $fileId = '';
    //Get entered URL
    $url = F3::get('POST["url"]');
    $fileName = F3::get('POST["srcPath"]');
    if ($fileName != "") {
        $fileId = $fileName;
    }
    //Check is URL entered
    if ($url != "") {
        //Upload file from URL
        try {
            $uploadResult = $storageApi->UploadWeb($clientId, $url);
            //Check upload status
            if ($uploadResult->status == "Ok") {
                //Get file GUID
                $fileId = $uploadResult->result->guid;
            } else {
                throw new Exception($uploadResult->error_message);
            }
        } catch (Exception $e) {
            $error = 'ERROR: ' . $e->getMessage() . "\n";
            $message = $error;
        }
    }
    //Check is local file chosen
    if ($_FILES['file']["name"] != "") {
        //Get uploaded file
        $uploadedFile = $_FILES['file'];
        //###Check uploaded file
        //Temp name of the file
        $tmpName = $uploadedFile['tmp_name'];
        //Original name of the file
        $name = $uploadedFile['name'];
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
            }
        } catch (Exception $e) {
            $error = 'ERROR: ' . $e->getMessage() . "\n";
            $message = $error;
        }
    }
    //###Make a request to Storage API using clientId
    //Obtaining all Entities from current user
    try {
        $files = $storageApi->ListEntities($clientId, '', 0);
        //Obtaining file name and id by fileGuID
        $name = '';
        foreach ($files->result->files as $item) {
            if ($item->guid == $fileName) {
                $name = $item->name;
                $fileId = $item->id;
            }
        }
        //###Make request for file copying/movement
        //Where to copy/move file
        $path = $folder . '/' . $name;
        //If user choose copy
        if (isset($copy)) {
            //Request to Storage for copying
            try {
                $file = $storageApi->MoveFile($clientId, $path, NULL, $fileId, NULL); //download file
                //Returning to Viewer what button was pressed
                F3::set('button', $copy);
            } catch (Exception $e) {
                $error = 'ERROR: ' . $e->getMessage() . "\n";
                $message = $error;
            }
        }
        //If user choose move
        if (isset($move)) {
            //Request to Storage for copying
            try {
                $file = $storageApi->MoveFile($clientId, $path, NULL, NULL, $fileId); //download file
                //If request was successfull - set button variable for template
                F3::set('file_Name', $fileId);
                F3::set('button', $move);
            } catch (Exception $e) {
                $error = 'ERROR: ' . $e->getMessage() . "\n";
                $message = $error;
            }
        }
        $message = 'File was {{@button}}\'ed to the <font color="blue">{{@folder}}</font> folder';
        //Process template
        F3::set('userId', $clientId);
        F3::set('privateKey', $privateKey);
        F3::set('folder', $folder);
        f3::set('message', $message);
    } catch (Exception $e) {
        $error = 'ERROR: ' . $e->getMessage() . "\n";
        $message = $error;
    }
}

echo Template::serve('sample05.htm');
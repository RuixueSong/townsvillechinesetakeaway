<?php

//###This sample will show how to delete file from GroupDocs account
//### Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
F3::set('fileName', '');

$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
$fileName = F3::get('POST["fileName"]');
$fileGuid = "";
$message = "";
if (empty($clientId) || empty($privateKey) || empty($fileName)) {
    $error = 'Please enter all required parameters';
    f3::set('error', $error);
} else {
    //Get base path
    $basePath = f3::get('POST["server_type"]');
    if ($basePath == "") {
         $basePath = 'https://api.groupdocs.com/v2.0';
    }
    F3::set('userId', $clientId);
    F3::set('privateKey', $privateKey);
    F3::set('fileName', $fileName);

    #### Create Signer, ApiClient and Annotation Api objects
    # Create signer object
    $signer = new GroupDocsRequestSigner($privateKey);
    # Create apiClient object
    $apiClient = new ApiClient($signer);
    # Create Storage object
    $storageApi = new StorageApi($apiClient);
    $storageApi->setBasePath($basePath);
    #Get file GUID by it's name
    try {
        $allFiles = $storageApi->ListEntities($clientId, "", null, null, null, null, null, null, false);
        if ($allFiles->status == "Ok") {
            for ($i = 0; $i < count($allFiles->result->files); $i++) {
                if ($allFiles->result->files[$i]->name == $fileName){
                    $fileGuid = $allFiles->result->files[$i]->guid;
                }
            }
           
            # Delete file from Api Storage
            try {
                 if ($fileGuid == "") {
                    $message = '<span style="color: red">This file is no longer available</span>';
                } else {
                    $delFile = $storageApi->Delete($clientId, $fileGuid);
                    // Check the result of the request
                    if ($delFile->status == "Ok") {
                        // If status "ok" - show Mesege
                        $message = '<span style="color: green">Done, file deleted from your GroupDocs Storage</span>';
                    } else {
                        $message = '<span style="color: red">' . $delFile->error_message . '</span>';
                    }
                }
               
            } catch (Exception $e) {
                $error = 'ERROR: ' . $e->getMessage() . "\n";
                f3::set('error', $error);
            }
        } else {
            $message = '<span style="color: red">' . $allFiles->error_message . '</span>';
        }

    } catch (Exception $e) {
        $error = 'ERROR: ' . $e->getMessage() . "\n";
        f3::set('error', $error);
    }
    F3::set('message', $message);
}
// Process template
echo Template::serve('sample30.htm');
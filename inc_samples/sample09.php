<?php

//###<i>This sample will show how to use <b>GuId</b> of file to generate an embedded Viewer/Annotation URL for a Document</i>
//Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
$width = f3::get('POST["width"]');
$height = f3::get('POST["height"]');
//###Check fileGuId
if (empty($clientId) || empty($privateKey)) {
    $error = 'Please enter all required parameters';
    f3::set('error', $error);
} else {
    F3::set('userId', $clientId);
    F3::set('privateKey', $privateKey);
    $iframeType = f3::get('POST["iframeType"]');
    f3::set("iframeType", $iframeType);
    //Get base path
    $basePath = f3::get('POST["server_type"]');
    //Get entered by user data
    $fileGuId = "";
    $url = F3::get('POST["url"]');
    $file = $_FILES['file'];
    $fileId = f3::get('POST["fileId"]');
    //###Create Signer, ApiClient and Storage Api objects
    //Create signer object
    $signer = new GroupDocsRequestSigner($privateKey);
    //Create apiClient object
    $apiClient = new APIClient($signer);
    //Create Storage Api object
    $storageApi = new StorageApi($apiClient);
    //Check is user entered base path
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
                $fileGuId = $uploadResult->result->guid;
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
                $fileGuId = $uploadResult->result->guid;
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
    //Check is user choose file GUID
    if ($fileId != "") {
        //Get entered by user file GUID
        $fileGuId = $fileId;
    }
    if ($iframeType == "viewer") {
        //Generation of iframe URL using fileGuId
        if ($basePath == "https://api.groupdocs.com/v2.0") {
            $iframe = 'http://apps.groupdocs.com/document-viewer/embed/' . $fileGuId;
            //iframe to dev server
        } elseif ($basePath == "https://dev-api.groupdocs.com/v2.0") {
            $iframe = 'http://dev-apps.groupdocs.com/document-viewer/embed/' . $fileGuId;
            //iframe to test server
        } elseif ($basePath == "https://stage-api-groupdocs.dynabic.com/v2.0") {
            $iframe = 'https://stage-apps-groupdocs.dynabic.com/document-viewer/Embed/' . $fileGuId;
            //Iframe to realtime server
        } elseif ($basePath == "http://realtime-api.groupdocs.com") {
            $iframe = 'http://realtime-apps.groupdocs.com/document-viewer/embed/' . $fileGuId;
        }
    }
    if ($iframeType == "annotation") {
        if ($basePath == "https://api.groupdocs.com/v2.0") {
            $iframe = 'http://apps.groupdocs.com/document-annotation2/embed/' . $fileGuId;
            //iframe to dev server
        } elseif ($basePath == "https://dev-api.groupdocs.com/v2.0") {
            $iframe = 'http://dev-apps.groupdocs.com/document-annotation2/embed/' . $fileGuId;
            //iframe to test server
        } elseif ($basePath == "https://stage-api-groupdocs.dynabic.com/v2.0") {
            $iframe = 'https://stage-apps-groupdocs.dynabic.com/document-annotation2/Embed/' . $fileGuId;
            //Iframe to realtime server
        } elseif ($basePath == "http://realtime-api.groupdocs.com") {
            $iframe = 'http://realtime-apps.groupdocs.com/document-annotation2/embed/' . $fileGuId;
        }
    }
    //If request was successfull - set url variable for template
    f3::set('fileId', $fileGuId);
    f3::set('url', $iframe);
}
//Process template
F3::set('userId', $clientId);
F3::set('privateKey', $privateKey);
F3::set('width', $width);
F3::set('height', $height);

echo Template::serve('sample09.htm');
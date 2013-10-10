<?php

//###<i>This sample will show how to use <b>Upload</b> method from Storage Api to upload file to GroupDocs Storage </i>
//Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
F3::set('fileId', '');
F3::set('message', '');
F3::set('iframe', '');
F3::set('basePath', '');
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
$basePath = f3::get('POST["server_type"]');
$url = F3::get('POST["url"]');

//###Check clientId and privateKey
if (empty($clientId) || empty($privateKey)) {
    $error = 'Please enter all required parameters';
    f3::set('error', $error);
} else {
    //Deleting of tags, slashes and  space from clientId and privateKey
    $clientID = strip_tags(stripslashes(trim($clientId))); //ClientId==UserId
    $apiKey = strip_tags(stripslashes(trim($privateKey))); //ApiKey==PrivateKey
    //###Create Signer, ApiClient and Storage Api objects
    //Create signer object
    $signer = new GroupDocsRequestSigner($apiKey);
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
    $iframe = null;
    //Check URL entered
    if ($url != "") {
        //Upload file from URL
        try {
            $uploadResult = $storageApi->UploadWeb($clientID, $url);
            //Check upload status
            if ($uploadResult->status == "Ok") {
                //Get file GUID
                $guid = $uploadResult->result->guid;
                if ($basePath == "https://api.groupdocs.com/v2.0") {
                    $iframe = 'http://apps.groupdocs.com/document-viewer/embed/' . $guid;
                    //iframe to dev server
                } elseif ($basePath == "https://dev-api.groupdocs.com/v2.0") {
                    $iframe = 'http://dev-apps.groupdocs.com/document-viewer/embed/' . $guid;
                    //iframe to test server
                } elseif ($basePath == "https://stage-api-groupdocs.dynabic.com/v2.0") {
                    $iframe = 'https://stage-apps-groupdocs.dynabic.com/document-viewer/Embed/' . $guid;
                    //Iframe to realtime server
                } elseif ($basePath == "http://realtime-api.groupdocs.com") {
                    $iframe = 'http://realtime-apps.groupdocs.com/document-viewer/embed/' . $guid;
                }

                //Generation of Embeded Viewer URL with uploaded file GuId
                $result = '<iframe src="' . $iframe . '" frameborder="0" width="800" height="650"></iframe>';
                //If request was successfull - set result variable for template
                $message = '<p>File was uploaded to GroupDocs. Here you can see your file in the GroupDocs Embedded Viewer.</p>';
                F3::set('message', $message);
                F3::set('iframe', $result);
                F3::set('userId', $clientId);
                F3::set('privateKey', $privateKey);
                F3::set('basePath', $basePath);
            } else {
                throw new Exception($uploadResult->error_message);
            }
        } catch (Exception $e) {
            $error = 'ERROR: ' . $e->getMessage() . "\n";
            f3::set('error', $error);
        }
    } else {
        //Get uploaded file
        $uploadedFile = $_FILES['file'];
        //###Check uploaded file
        if (null === $uploadedFile) {
            return new RedirectResponse("/sample03");
        }
        //Temp name of the file
        $tmpName = $uploadedFile['tmp_name'];
        //Original name of the file
        $name = $uploadedFile['name'];
        //Creat file stream
        $fs = FileStream::fromFile($tmpName);
        //###Make a request to Storage API using clientId
        $callbackUrl = f3::get('POST["callbackUrl"]');
        F3::set("callbackUrl", $callbackUrl);
        //Upload file to current user storage
        try {
            $uploadResult = $storageApi->Upload($clientID, $name, 'uploaded', $callbackUrl, $fs);
            var_dump($uploadResult);
            //###Check if file uploaded successfully
            if ($uploadResult->status == "Ok") {
                //Get file GUID
                $guid = $uploadResult->result->guid;
            } else {
                throw new Exception($uploadResult->error_message);
            }
            if ($basePath == "https://api.groupdocs.com/v2.0") {
                $iframe = 'http://apps.groupdocs.com/document-viewer/embed/' . $guid;
                //iframe to dev server
            } elseif ($basePath == "https://dev-api.groupdocs.com/v2.0") {
                $iframe = 'http://dev-apps.groupdocs.com/document-viewer/embed/' . $guid;
                //iframe to test server
            } elseif ($basePath == "https://stage-api-groupdocs.dynabic.com/v2.0") {
                $iframe = 'https://stage-apps-groupdocs.dynabic.com/document-viewer/Embed/' . $guid;
                //Iframe to realtime server
            } elseif ($basePath == "http://realtime-api.groupdocs.com") {
                $iframe = 'http://realtime-apps.groupdocs.com/document-viewer/embed/' . $guid;
            }

            //Generation of Embeded Viewer URL with uploaded file GuId
            $result = '<iframe src="' . $iframe . '" frameborder="0" width="800" height="650"></iframe>';
            //If request was successfull - set result variable for template
            $message = '<p>File was uploaded to GroupDocs. Here you can see your file in the GroupDocs Embedded Viewer.</p>';
            F3::set('message', $message);
            F3::set('iframe', $result);
            F3::set('userId', $clientId);
            F3::set('privateKey', $privateKey);
            F3::set('basePath', $basePath);
        } catch (Exception $e) {
            $error = 'ERROR: ' . $e->getMessage() . "\n";
            f3::set('error', $error);
        }
    }
}
//Process template
echo Template::serve('sample03.htm');

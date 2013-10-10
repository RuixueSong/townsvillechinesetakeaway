<?php

//###<i>This sample will show how to use <b>UploadWeb</b> method from Storage Api to upload file to GroupDocs Storage </i>
//Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
F3::set('fileId', '');
F3::set('message', '');
F3::set('iframe', '');
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
$url = F3::get('POST["url"]');

function Upload($clientId, $privateKey, $url) {
    //###Check clientId and privateKey
    if (empty($clientId) || empty($privateKey) || empty($url)) {
        throw new Exception('Please enter all required parameters');
    } else {
        //Get base path
        $basePath = f3::get('POST["server_type"]');
        $clientID = strip_tags(stripslashes(trim($clientId))); //ClientId==UserId
        $apiKey = strip_tags(stripslashes(trim($privateKey))); //ApiKey==PrivateKey
        //###Create Signer, ApiClient and Storage Api objects
        //Create signer object
        $signer = new GroupDocsRequestSigner($apiKey);
        //Create apiClient object
        $apiClient = new APIClient($signer);
        //Create Storage Api object
        $storageApi = new StorageApi($apiClient);
        //Set url to choose whot server to use
        if ($basePath == "") {
            //If base base is empty seting base path to prod server
            $basePath = 'https://api.groupdocs.com/v2.0';
        }
        //Set base path
        $storageApi->setBasePath($basePath);
        //###Make a request to Storage API using clientId
        //Upload file to current user storage using entere URl to the file
        $uploadResult = $storageApi->UploadWeb($clientID, $url);

        //###Check if file uploaded successfully
        if ($uploadResult->status == "Ok") {
            //Generation of iframe URL using $pageImage->result->guid
            //iframe to prodaction server
            if ($basePath == "https://api.groupdocs.com/v2.0") {
                $result = '<iframe src="https://apps.groupdocs.com/document-viewer/Embed/' . 
                        $uploadResult->result->guid . '" frameborder="0" width="720" height="600"></iframe>';
                //iframe to dev server
            } elseif ($basePath == "https://dev-api.groupdocs.com/v2.0") {
                $result = '<iframe src="https://dev-apps.groupdocs.com/document-viewer/Embed/' . 
                        $uploadResult->result->guid . '" frameborder="0" width="720" height="600"></iframe>';
                //iframe to test server
            } elseif ($basePath == "https://stage-apps-groupdocs.dynabic.com/v2.0") {
                $result = '<iframe src="https://stage-apps-groupdocs.dynabic.com/document-viewer/Embed/' . 
                        $uploadResult->result->guid . '" frameborder="0" width="720" height="600"></iframe>';
            } elseif ($basePath == "http://realtime-api.groupdocs.com") {
                $result = '<iframe src="https://realtime-apps.groupdocs.com/document-viewer/Embed/' . 
                        $uploadResult->result->guid . '" frameborder="0" width="720" height="600"></iframe>';
            }
            //If request was successfull - set result variable for template
            return $result;
        }
    }
}

try {
    $upload = Upload($clientId, $privateKey, $url);
    $message = '<p>File was uploaded to GroupDocs. Here you can see your <strong> file in the GroupDocs Embedded Viewer.</p>';
    F3::set('message', $message);
    F3::set('iframe', $upload);
} catch (Exception $e) {
    $error = 'ERROR: ' . $e->getMessage() . "\n";
    f3::set('error', $error);
}
//Process template
F3::set('userId', $clientId);
F3::set('privateKey', $privateKey);
echo Template::serve('sample24.htm');
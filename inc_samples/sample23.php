<?php

//###<i>This sample will show how to use <b>GuId</b> of file to view document pages as images</i>
//Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
$basePath = f3::get('POST["server_type"]');

function Iframe($clientId, $privateKey, $basePath) {
    //###Check if user entered all parameters
    if (empty($clientId) || empty($privateKey)) {
        throw new Exception('Please enter FILE ID');
    } else {
        F3::set('userId', $clientId);
        F3::set('privateKey', $privateKey);
        //###Create Signer, ApiClient and Storage Api objects
        //Create signer object
        $signer = new GroupDocsRequestSigner($privateKey);
        //Create apiClient object
        $apiClient = new APIClient($signer);
        //Create Doc Api object
        $api = new DocApi($apiClient);
        //Create Storage Api object
        $storageApi = new StorageApi($apiClient);
        //Set url to choose whot server to use
        if ($basePath == "") {
            //If base base is empty seting base path to prod server
            $basePath = 'https://api.groupdocs.com/v2.0';
        }
        //Set base path
        $api->setBasePath($basePath);
        $storageApi->setBasePath($basePath);
        //Get entered by user data
        $url = F3::get('POST["url"]');
        $file = $_FILES['file'];
        $fileId = f3::get('POST["fileId"]');
        $fileGuId = "";
        //Check is file GUID entered
        if ($fileId != "") {
            $fileGuId = $fileId;
        }
        //If user choose upload file from URL
        if ($url != "") {
            //Upload file from URL
            $uploadResult = $storageApi->UploadWeb($clientId, $url);
            //Check is file uploaded
            if ($uploadResult->status == "Ok") {
                //Get file GUID
                $fileGuId = $uploadResult->result->guid;

                //If it isn't uploaded throw exception to template
            } else {
                throw new Exception($uploadResult->error_message);
            }
        }
        //If user choose upload local file
        if ($file["name"] != "") {
            //Get uploaded file
            $uploadedFile = $_FILES['file'];


            //###Check uploaded file
            if (null === $uploadedFile) {
                return new RedirectResponse("/sample23");
            }
            //Temp name of the file
            $tmpName = $uploadedFile['tmp_name'];
            //Original name of the file
            $name = $uploadedFile['name'];
            //Creat file stream
            $fs = FileStream::fromFile($tmpName);


            //###Make a request to Storage API using clientId
            //Upload file to current user storage
            $uploadResult = $storageApi->Upload($clientId, $name, 'uploaded', "", $fs);
            //###Check if file uploaded successfully
            if ($uploadResult->status == "Ok") {
                $fileGuId = $uploadResult->result->guid;
            } else {
                throw new Exception($uploadResult->error_message);
            }
        }
        //Make request yo the Api to get images for all document pages
        $pageImage = $api->GetDocumentPagesImageUrls($clientId, $fileGuId, 1, 1, '650x500', null, null, null);
       
        $url = "";
        $image = "";
        //Check the result of the request
        if ($pageImage->status == "Ok") {
            
            //### If request was successfull
            for ($i = 0; $i < count($pageImage->result->url); $i++) {
               
                $image .= '<img src="' . $pageImage->result->url[$i] . '"></img><br/>';
            }
        } else {
            throw new Exception($pageImage->error_message);
        }
        //Set variable with results for template
        f3::set("fileId", $fileGuId);
        return f3::set('image', $image);
    }
}

try {
    Iframe($clientId, $privateKey, $basePath);
} catch (Exception $e) {
    $error = 'ERROR: ' . $e->getMessage() . "\n";
    f3::set('error', $error);
}
//Process template
echo Template::serve('sample23.htm');
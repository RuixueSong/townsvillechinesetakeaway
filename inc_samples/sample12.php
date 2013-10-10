<?php

//###This sample will show how to list all annotations from document
// Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
F3::set('fileId', '');
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
$fileId = F3::get('POST["fileId"]');
if (empty($clientId) || empty($privateKey) || empty($fileId)) {
    $error = 'Please enter all required parameters';
    f3::set('error', $error);
} else {
    //Get base path
    $basePath = f3::get('POST["server_type"]');
    F3::set('userId', $clientId);
    F3::set('privateKey', $privateKey);
    F3::set('fileId', $fileId);

    #### Create Signer, ApiClient and Annotation Api objects
    # Create signer object
    $signer = new GroupDocsRequestSigner($privateKey);
    # Create apiClient object
    $apiClient = new ApiClient($signer);
    # Create Annotation object
    $antApi = new AntApi($apiClient);
    if ($basePath == "") {
        //If base base is empty seting base path to prod server
        $basePath = 'https://api.groupdocs.com/v2.0';
    }
    //Set base path
    $antApi->setBasePath($basePath);
    # Make a request to Annotation API using clientId and fileId
    try {
        $list = $antApi->ListAnnotations($clientId, $fileId);
        if ($list->status == "Ok") {
            // Check the result of the request
            if (isset($list->result)) {
                // If request was successfull - set annotations variable for template
                F3::set('annotations', $list->result->annotations);
            }
        } else {
            throw new Exception($list->error_message);
        }
    } catch (Exception $e) {
        $error = 'ERROR: ' . $e->getMessage() . "\n";
        f3::set('error', $error);
    }
}
// Process template
echo Template::serve('sample12.htm');
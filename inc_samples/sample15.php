<?php

//### This sample will show how to check the number of document's views using PHP SDK
// Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
if (empty($clientId) || empty($privateKey)) {
    $error = 'Please enter all required parameters';
    f3::set('error', $error);
} else {
    //Get base path
    $basePath = f3::get('POST["server_type"]');
    F3::set('userId', $clientId);
    F3::set('privateKey', $privateKey);
    // initialization some variables
    $views = 0;
    //### Create Signer, ApiClient and Document Api objects
    // Create signer object
    $signer = new GroupDocsRequestSigner($privateKey);
    // Create apiClient object
    $apiClient = new ApiClient($signer);
    // Create Document object
    $docApi = new DocApi($apiClient);
    if ($basePath == "") {
        //If base base is empty seting base path to prod server
        $basePath = 'https://api.groupdocs.com/v2.0';
    }
    //Set base path
    $docApi->setBasePath($basePath);
    // Make a request to Doc API using clientId
    try {
        $result = $docApi->GetDocumentViews($clientId);
        if ($result->status == "Ok") {
            // Check the result of the request
            if (isset($result->result)) {
                // If request was successfull - set annotations variable for template
                F3::set('views', count($result->result->views));
            }
        } else {
            throw new Exception($result->error_message);
        }
    } catch (Exception $e) {
        $error = 'ERROR: ' . $e->getMessage() . "\n";
        f3::set('error', $error);
    }
}
// Process template
echo Template::serve('sample15.htm');
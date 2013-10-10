<?php

//###This sample will show how to list all annotations from document
//### Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
F3::set('fileId', '');

$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
$fileId = F3::get('POST["fileId"]');

function DeleteAnnotations($clientId, $privateKey, $fileId) {
    if (empty($clientId) || empty($privateKey) || empty($fileId)) {
        throw new Exception('Please enter all required parameters');
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
        $ant = new AntApi($apiClient);
        if ($basePath == "") {
            //If base base is empty seting base path to prod server
            $basePath = 'https://api.groupdocs.com/v2.0';
        }
        //Set base path
        $ant->setBasePath($basePath);
        # Make a request to Annotation API using clientId and fileId
        $list = $ant->ListAnnotations($clientId, $fileId);

        $message = "";
        // Check the result of the request
        if ($list->status == "Ok") {
            if (!empty($list->result->annotations)) {
                for ($i = 0; $i < count($list->result->annotations); $i++) {
                    $del = $ant->DeleteAnnotation($clientId, $list->result->annotations[$i]->guid);
                    if ($del->status == "Ok") {
                        $message = '<span style="color: green">All annotation were deleted successfully</span>';
                        //### If request was successfull
                        //Generation of iframe URL using $pageImage->result->guid
                        //iframe to prodaction server
                        if ($basePath == "https://api.groupdocs.com/v2.0") {
                            $iframe = 'https://apps.groupdocs.com/document-viewer/embed/' . $fileId;
                            //iframe to dev server
                        } elseif ($basePath == "https://dev-api.groupdocs.com/v2.0") {
                            $iframe = 'https://dev-apps.groupdocs.com/document-viewer/embed/' . $fileId;
                            //iframe to test server
                        } elseif ($basePath == "https://stage-apps-groupdocs.dynabic.com/v2.0") {
                            $iframe = 'https://stage-apps-groupdocs.dynabic.com/document-viewer/embed/' . $fileId;
                        } elseif ($basePath == "http://realtime-api.groupdocs.com") {
                            $iframe = 'http://realtime-apps.groupdocs.com/document-viewer/embed/' . $fileId;
                        }
                        f3::set("url", $iframe);
                    } else {
                        $message = $del->error_message;
                    }
                }
            } else {
                $message = '<span style="color: red">There are no annotations</span>';
            }
        } else {
            $message = $list->error_message;
        }
        return F3::set('message', $message);
    }
}

try {
    DeleteAnnotations($clientId, $privateKey, $fileId);
} catch (Exception $e) {
    $error = 'ERROR: ' . $e->getMessage() . "\n";
    f3::set('error', $error);
}

// Process template
echo Template::serve('sample28.htm');
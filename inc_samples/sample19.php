<?php

//###<i>This sample will show how to use <b>Compare</b> method from ComparisonApi to compare two documents</i>
//Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
f3::set('result', "");
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');

$callbackUrl = f3::get('POST["callbackUrl"]');
$basePath = f3::get('POST["server_type"]');
//### Check clientId, privateKey and fileGuId
if (empty($clientId) || empty($privateKey)) {
    $error = 'Please enter all required parameters';
    f3::set('error', $error);
} else {
    //path to settings file - temporary save userId and apiKey like to property file
    $infoFile = fopen(__DIR__ . '/../user_info.txt', 'w');
    fwrite($infoFile, $clientId . "\r\n" . $privateKey);
    fclose($infoFile);
    //check if Downloads folder exists and remove it to clean all old files
    if (file_exists(__DIR__ . '/../downloads')) {
        delFolder(__DIR__ . '/../downloads/');
    }
    //Set variables for Viewer
    F3::set('userId', $clientId);
    F3::set('privateKey', $privateKey);
    //Get entered by user data
    $sourceFileId = "";
    $targetFileId = "";
    $firstFileId = f3::get('POST["sourceFileId"]');
    $secondFileId = f3::get('POST["targetFileId"]');
    $url = F3::get('POST["url"]');
    $targetUrl = F3::get('POST["target_url"]');
    $iframe = "";
    //###Create Signer, ApiClient and Storage Api objects
    //Create signer object
    $signer = new GroupDocsRequestSigner($privateKey);
    //Create apiClient object
    $apiClient = new APIClient($signer);
    //Create ComparisonApi object
    $CompareApi = new ComparisonApi($apiClient);
    //Create Storage Api object
    $storageApi = new StorageApi($apiClient);
    if ($basePath == "") {
        //If base base is empty seting base path to prod server
        $basePath = 'https://api.groupdocs.com/v2.0';
    }
    //Set base path
    $CompareApi->setBasePath($basePath);
    $storageApi->setBasePath($basePath);
    //Check entered source file GUID and target file GUID
    if ($firstFileId != "" || $secondFileId != "") {
        if ($firstFileId != "") {
            $sourceFileId = $firstFileId;
        }
        if ($secondFileId != "") {
            $targetFileId = $secondFileId;
        }
    }
    //Check is user choose local files to upload and compare
    if ($_FILES['file']["name"] != "" || $_FILES["target_file"]["name"] != "") {
        if ($_FILES['file']["name"] != "") {
            //Temp name of the file
            $tmpName = $_FILES['file']['tmp_name'];
            //Original name of the file
            $name = $_FILES['file']['name'];
            //Creat file stream
            $fs = FileStream::fromFile($tmpName);
            //###Make a request to Storage API using clientId
            //Upload file to current user storage
            try {
                $uploadResult = $storageApi->Upload($clientId, $name, 'uploaded', "", $fs);

                //###Check if file uploaded successfully
                if ($uploadResult->status == "Ok") {
                    //Get file GUID
                    $sourceFileId = $uploadResult->result->guid;
                    $firstFileId = "";
                    //If it isn't uploaded throw exception to template
                } else {
                    throw new Exception($uploadResult->error_message);
                }
            } catch (Exception $e) {
                $error = 'ERROR: ' . $e->getMessage() . "\n";
                f3::set('error', $error);
            }
        }
        //Check is user choose upload and compare file from URL
        if ($_FILES['target_file']["name"] != "") {
            //Temp name of the file
            $tmpName = $_FILES["target_file"]['tmp_name'];
            //Original name of the file
            $name = $_FILES["target_file"]['name'];
            //Creat file stream
            $fs = FileStream::fromFile($tmpName);
            //###Make a request to Storage API using clientId
            //Upload file to current user storage
            try {
                $uploadResult = $storageApi->Upload($clientId, $name, 'uploaded', "", $fs);

                //###Check if file uploaded successfully
                if ($uploadResult->status == "Ok") {
                    //Get file GUID
                    $targetFileId = $uploadResult->result->guid;
                    $secondFileId = "";
                    //If it isn't uploaded throw exception to template
                } else {
                    throw new Exception($uploadResult->error_message);
                }
            } catch (Exception $e) {
                $error = 'ERROR: ' . $e->getMessage() . "\n";
                f3::set('error', $error);
            }
        }
    }
    if ($url != "" || $targetUrl != "") {
        if ($url != "") {
            //Upload file from URL
            try {
                $uploadResult = $storageApi->UploadWeb($clientId, $url);
                //Check is file uploaded
                if ($uploadResult->status == "Ok") {
                    //Get file GUID
                    $sourceFileId = $uploadResult->result->guid;
                    //If it isn't uploaded throw exception to template
                } else {
                    throw new Exception($uploadResult->error_message);
                }
            } catch (Exception $e) {
                $error = 'ERROR: ' . $e->getMessage() . "\n";
                f3::set('error', $error);
            }
        }
        if ($targetUrl != "") {
            //Upload file from URL
            try {
                $uploadResult = $storageApi->UploadWeb($clientId, $targetUrl);
                //Check is file uploaded
                if ($uploadResult->status == "Ok") {
                    //Get file GUID
                    $targetFileId = $uploadResult->result->guid;
                    //If it isn't uploaded throw exception to template
                } else {
                    throw new Exception($uploadResult->error_message);
                }
            } catch (Exception $e) {
                $error = 'ERROR: ' . $e->getMessage() . "\n";
                f3::set('error', $error);
            }
        }
    }
    //###Make request to ComparisonApi using user id
    //Comparison of documents where: $clientId - user GuId, $sourceFileId - source file Guid in which will be provided compare, 
    //$targetFileId - file GuId with wich will compare sourceFile, $callbackUrl - Url which will be executed after compare,
    try {
        $info = $CompareApi->Compare($clientId, $sourceFileId, $targetFileId, $callbackUrl);
        //###Example of handling callback request:
        //  You can handle callback request in separate php file or in the same one. Our service will post JSON data via post request. 
        //In PHP you should get raw data like this:
        //     $json = file_get_contents("php://input"); - get callback data
        //     $fp = fopen(__DIR__ . '/../../temp/signature_request_log.txt', 'a'); - open file for data write
        //     fwrite($fp, $json . "\r\n"); - write data to the file
        //     fclose($fp); - close file
        //Check request status
        if ($info->status == "Ok") {
            //Create AsyncApi object
            $asyncApi = new AsyncApi($apiClient);
            $asyncApi->setBasePath($basePath);
            //### Check job status
            for ($i = 0; $i <= 5; $i++) {
                //Delay necessary that the inquiry would manage to be processed
                sleep(5);
                //Make request to api for get document info by job id
                try {
                    $jobInfo = $asyncApi->GetJobDocuments($clientId, $info->result->job_id);
                    if ($jobInfo->status == "Ok") {
                        //Check job status, if status is Completed or Archived exit from cycle
                        if ($jobInfo->result->job_status == "Completed" || $jobInfo->result->job_status == "Archived") {
                            break;
                            //If job status Postponed throw exception with error
                        } elseif ($jobInfo->result->job_status == "Postponed") {
                            throw new Exception('Job is failure');
                        }
                    } else {
                        throw new Exception($jobInfo->error_message);
                    }
                } catch (Exception $e) {
                    $error = 'ERROR: ' . $e->getMessage() . "\n";
                    f3::set('error', $error);
                }
            }
            //Get file guid
            $guid = $jobInfo->result->outputs[0]->guid;
            $iframe = 'https://apps.groupdocs.com/document-viewer/embed/';
            // Construct iframe using fileId
            if ($basePath == "https://api.groupdocs.com/v2.0") {
                $iframe = 'https://apps.groupdocs.com/document-viewer/embed/' .
                        $guid . ' frameborder="0" width="500" height="650"';
                //iframe to dev server
            } elseif ($basePath == "https://dev-api.groupdocs.com/v2.0") {
                $iframe = 'https://dev-apps.groupdocs.com/document-viewer/embed/' .
                        $guid . ' frameborder="0" width="500" height="650"';
                //iframe to test server
            } elseif ($basePath == "https://stage-apps-groupdocs.dynabic.com/v2.0") {
                $iframe = 'https://stage-apps-groupdocs.dynabic.com/document-viewer/embed/' .
                        $guid . ' frameborder="0" width="500" height="650"';
            } elseif ($basePath == "http://realtime-api.groupdocs.com") {
                $iframe = 'http://realtime-apps.groupdocs.com/document-viewer/embed/' .
                        $guid . '" frameborder="0" width="100%" height="600"';
            }
        } else {
            throw new Exception($info->error_message);
        }
    } catch (Exception $e) {
        $error = 'ERROR: ' . $e->getMessage() . "\n";
        f3::set('error', $error);
    }
    //If request was successfull - set url variable for template
    f3::set('sourceFileId', $sourceFileId);
    f3::set('targetFileId', $targetFileId);
    F3::set('iframe', $iframe);
}

//### Delete downloads folder and all files in this folder
function delFolder($path) {
    $next = null;
    $item = array();
    //Get all items fron folder
    $item = scandir($path);
    //Remove from array "." and ".."
    $item = array_slice($item, 2);
    //Check is there was files
    if (count($item) > 0) {
        //Delete files from folder
        for ($i = 0; $i < count($item); $i++) {
            $next = $path . "\\" . $item[$i];
            if (file_exists($next)) {
                unlink($next);
            }
        }
    }
    //Delete folder
    rmdir($path);
}

//Process template
f3::set('callbackURL', $callbackUrl);
echo Template::serve('sample19.htm');
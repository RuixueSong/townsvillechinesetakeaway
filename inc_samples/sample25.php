<?php

//###<i>This sample will show how to  merge/assemble data fields in docx file with data source and get result file as PDF file</i>
//Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
$basePath = f3::get('POST["server_type"]');

function mergeFields($clientId, $privateKey, $basePath) {
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
        $docApi = new DocApi($apiClient);
        //Create Storage Api object
        $storageApi = new StorageApi($apiClient);
        //Create AsyncApi object
        $api = new AsyncApi($apiClient);
        $mergApi = new MergeApi($apiClient);
        //Set url to choose whot server to use
        if ($basePath == "") {
            //If base base is empty seting base path to prod server
            $basePath = 'https://api.groupdocs.com/v2.0';
        }
        //Set base path
        $docApi->setBasePath($basePath);
        $storageApi->setBasePath($basePath);
        $api->setBasePath($basePath);
        $mergApi->setBasePath($basePath);
        //Get entered by user data
        $url = F3::get('POST["url"]');
        $file = $_FILES['file'];
        $fileId = f3::get('POST["fileId"]');
        //Check if user choose upload file from URL
        if ($url != "") {
            //Upload file from URL
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
        }
        //Check is user choose file GUID
        if ($fileId != "") {
            //Get entered by user file GUID
            $fileGuId = $fileId;
        }
        //Get feilds from template
        $fields = $docApi->GetTemplateFields($clientId, $fileGuId);
        //Check status
        if ($fields->status == "Ok") {
            //Create new Datasource object
            $dataSource = new Datasource();
            //Create empty array
            $array = array();
            //Loop for fields creataion
            for ($i = 0; $i < count($fields->result->fields); $i++) {
                //Create new DatasourceField object
                $field = new DatasourceField();
                //Set DatasourceFiled data
                $field->name = $fields->result->fields[$i]->name;
                $field->type = "text";
                $field->values = array("value1", "value2");
                //Push DatasourceField to array
                array_push($array, $field);
            }
            //Set array feilds array to the Datasourc
            $dataSource->fields = $array;
            //Add Datasource to GroupDocs
            $addDataSource = $mergApi->AddDataSource($clientId, $dataSource);
            //Check status
            if ($addDataSource->status == "Ok") {
                //If status ok  merge/assemble Datasource to new pdf file
                $job = $mergApi->MergeDatasource($clientId, $fileGuId, $addDataSource->result->datasource_id, "pdf", null);
                //Check status
                if ($job->status == "Ok") {
                    //### Check job status
                    for ($n = 0; $n <= 5; $n++) {
                        //Delay necessary that the inquiry would manage to be processed
                        sleep(2);
                        //Make request to api for get document info by job id
                        $jobInfo = $api->GetJobDocuments($clientId, $job->result->job_id);
                        //Check job status, if status is Completed or Archived exit from cycle
                        if ($jobInfo->result->job_status == "Completed" || $jobInfo->result->job_status == "Archived") {
                            break;
                            //If job status Postponed throw exception with error
                        } elseif ($jobInfo->result->job_status == "Postponed") {
                            throw new Exception("Job is failed");
                        }
                    }
                    if ($jobInfo->result->job_status == "Pending") {
                        throw new Exception("Job is pending");
                    }
                    //Get file guid
                    $guid = $jobInfo->result->inputs[0]->outputs[0]->guid;
                    F3::set('fileId', $guid);
                    //Get file name
                    $name = $jobInfo->result->inputs[0]->outputs[0]->name;
                    //Local path to the downloads folder
                    $downloadFolder = dirname(__FILE__) . '/../downloads';
                    //Check is folder exist
                    if (!file_exists($downloadFolder)) {
                        //If folder don't exist create it
                        mkdir($downloadFolder);
                    }
                    //Obtaining file stream of downloading file and definition of folder where to download file
                    $outFileStream = FileStream::fromHttp($downloadFolder, $name);
                    //Download file from GroupDocs.
                    $download = $storageApi->GetFile($clientId, $guid, $outFileStream);
                    f3::set("message", "File was converted and downloaded to the " . $downloadFolder . "/" . $name);
                    //### If request was successfull
                    //Generation of iframe URL using $pageImage->result->guid
                    //iframe to prodaction server
                    if ($basePath == "https://api.groupdocs.com/v2.0") {
                        $iframe = 'https://apps.groupdocs.com/document-viewer/embed/' . $guid;
                        //iframe to dev server
                    } elseif ($basePath == "https://dev-api.groupdocs.com/v2.0") {
                        $iframe = 'https://dev-apps.groupdocs.com/document-viewer/embed/' . $guid;
                        //iframe to test server
                    } elseif ($basePath == "https://stage-apps-groupdocs.dynabic.com/v2.0") {
                        $iframe = 'https://stage-apps-groupdocs.dynabic.com/document-viewer/embed/' . $guid;
                    } elseif ($basePath == "http://realtime-api.groupdocs.com") {
                        $iframe = 'http://realtime-apps.groupdocs.com/document-viewer/embed/' . $guid;
                    }
                } else {
                    throw new Exception($job->error_message);
                }
            } else {
                throw new Exception($addDataSource->error_message);
            }
        } else {
            throw new Exception($fields->error_message);
        }

        //Set variable with results for template

        return f3::set('url', $iframe);
    }
}

try {
    mergeFields($clientId, $privateKey, $basePath);
} catch (Exception $e) {
    $error = 'ERROR: ' . $e->getMessage() . "\n";
    f3::set('error', $error);
}
//Process template
echo Template::serve('sample25.htm');
<?php

//<i>This sample will show how to dinamically create your own questionary using forms and build signature form from the result document using PHP SDK</i>
//###Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
$basePath = f3::get('POST["server_type"]');
$templateGuid = F3::get('POST["template_guid"]');
$name = f3::get('POST["name"]');
$email = f3::get('POST["email"]');
$country = f3::get('POST["country"]');
$city = f3::get('POST["city"]');
$street = f3::get('POST["street"]');

function createQuestionary($clientId, $privateKey, $basePath) {
    $templateGuid = F3::get('POST["template_guid"]');
    //###Check if user entered all parameters
    if (empty($clientId) || empty($privateKey)) {
        throw new Exception('Please enter FILE ID');
    } else {
        //path to settings file - temporary save userId and apiKey like to property file
        $infoFile = fopen(__DIR__ . '/../user_info.txt', 'w');
        fwrite($infoFile, $clientId . "\r\n" . $privateKey);
        fclose($infoFile);
        //check if Downloads folder exists and remove it to clean all old files
        $callbackUrl = f3::get('POST["callbackUrl"]');
        if ($callbackUrl != "") {
            if (file_exists(__DIR__ . '/../downloads')) {
                delFolder(__DIR__ . '/../downloads/');
            }
        }
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
        $apiStorage = new StorageApi($apiClient);
        //Create AsyncApi object
        $api = new AsyncApi($apiClient);
        $mergApi = new MergeApi($apiClient);
        $signatureApi = new SignatureApi($apiClient);
        //Set url to choose whot server to use
        if ($basePath == "") {
            //If base base is empty seting base path to prod server
            $basePath = 'https://api.groupdocs.com/v2.0';
        }
        //Set base path
        $docApi->setBasePath($basePath);
        $apiStorage->setBasePath($basePath);
        $api->setBasePath($basePath);
        $mergApi->setBasePath($basePath);
        $signatureApi->setBasePath($basePath);
        //Get entered by user data
        $name = f3::get('POST["name"]');
        $email = f3::get('POST["email"]');
        $country = f3::get('POST["country"]');
        $city = f3::get('POST["city"]');
        $street = f3::get('POST["street"]');
       
        f3::set("email", $email);
        f3::set("country", $country);
        f3::set("name", $name);
        f3::set("street", $street);
        f3::set("city", $city);
        f3::set("callbackUrl", $callbackUrl);
        $enteredData = array("email" => $email, "country" => $country, "name" => $name, "street" => $street, "city" => $city);
        //Create new Datasource object
        $dataSource = new Datasource();
        //Create empty array
        $array = array();
        //Loop for fields creataion
        foreach ($enteredData as $fieldName => $data) {

            //Create new DatasourceField object
            $field = new DatasourceField();
            //Set DatasourceFiled data
            $field->name = $fieldName;
            $field->type = "text";
            $field->values = array($data);
            //Push DatasourceField to array
            array_push($array, $field);
        }
        //Set array feilds array to the Datasourc
        $dataSource->fields = $array;
        //Add Datasource to GroupDocs
        $addDataSource = $mergApi->AddDataSource($clientId, $dataSource);
        //Check status
        if ($addDataSource->status == "Ok") {
            //If status ok merge Datasource to new pdf file
            $job = $mergApi->MergeDatasource($clientId, $templateGuid, $addDataSource->result->datasource_id, "pdf", null);
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
                $envelop = $signatureApi->CreateSignatureEnvelope($clientId, $jobInfo->result->inputs[0]->outputs[0]->name);
                if ($envelop->status == "Ok") {
                    sleep(5);
                    //Add uploaded document to envelope
                    $addDocument = $signatureApi->AddSignatureEnvelopeDocument($clientId, $envelop->result->envelope->id, $guid, null, true);
                    if ($addDocument->status == "Ok") {
                        //Get role list for curent user
                        $recipient = $signatureApi->GetRolesList($clientId);
                        if ($recipient->status == "Ok") {
                            //Get id of role which can sign
                            for ($i = 0; $i < count($recipient->result->roles); $i++) {
                                if ($recipient->result->roles[$i]->name == "Signer") {
                                    $roleId = $recipient->result->roles[$i]->id;
                                }
                            }
                            //Add recipient to envelope
                            $addRecipient = $signatureApi->AddSignatureEnvelopeRecipient($clientId, $envelop->result->envelope->id, $email, $name, 'test', $roleId, null);
                            if ($addRecipient->status == "Ok") {
                                //Get recipient id
                                $getRecipient = $signatureApi->GetSignatureEnvelopeRecipients($clientId, $envelop->result->envelope->id);
                                if ($getRecipient->status == "Ok") {
                                    $recipientId = $getRecipient->result->recipients[0]->id;
                                    $getDocuments = $signatureApi->GetSignatureEnvelopeDocuments($clientId, $envelop->result->envelope->id);
                                    if ($getDocuments->status == "Ok") {
                                        $send = $signatureApi->SignatureEnvelopeSend($clientId, $envelop->result->envelope->id, $callbackUrl);
                                        if ($send->status == "Ok") {
                                            $envelopeId = $envelop->result->envelope->id;
                                            if ($basePath == "https://api.groupdocs.com/v2.0") {
                                                $iframe = 'https://apps.groupdocs.com/signature2/signembed/' . $envelopeId . '/' . $recipientId;
                                                //iframe to dev server
                                            } elseif ($basePath == "https://dev-api.groupdocs.com/v2.0") {
                                                $iframe = 'https://dev-apps.groupdocs.com/signature2/signembed/' . $envelopeId . '/' . $recipientId;
                                                //iframe to test server
                                            } elseif ($basePath == "https://stage-apps-groupdocs.dynabic.com/v2.0") {
                                                $iframe = 'https://stage-apps-groupdocs.dynabic.com/signature2/signembed/' . $envelopeId . '/' . $recipientId;
                                            } elseif ($basePath == "http://realtime-api.groupdocs.com") {
                                                $iframe = 'https://relatime-apps.groupdocs.com/signature2/signembed/' . $envelopeId . '/' . $recipientId;
                                            }
                                        } else {
                                            throw new Exception($send->error_message);
                                        }
                                    } else {
                                        throw new Exception($getDocuments->error_message);
                                    }
                                } else {
                                    throw new Exception($getRecipient->error_message);
                                }
                            } else {
                                throw new Exception($addRecipient->error_message);
                            }
                        } else {
                            throw new Exception($recipient->error_message);
                        }
                    } else {
                        throw new Exception($addDocument->error_message);
                    }
                } else {
                    throw new Exception($envelop->error_message);
                }
            } else {
                throw new Exception($job->error_message);
            }
        } else {
            throw new Exception($addDataSource->error_message);
        }

        //Set variable with results for template
        return f3::set('url', $iframe);
    }
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

try {
    createQuestionary($clientId, $privateKey, $basePath);
} catch (Exception $e) {
    $error = 'ERROR: ' . $e->getMessage() . "\n";
    f3::set('error', $error);
}
//Process template
echo Template::serve('sample31.htm');
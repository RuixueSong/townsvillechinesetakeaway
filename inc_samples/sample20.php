<?php

//###<i>This sample will show how to use <b>GetChanges</b> method from ComparisonApi to return a list of changes of a Document</i>
//Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
f3::set('result', "");
$clientId = F3::get('POST["client_id"]');
$privateKey = F3::get('POST["private_key"]');
$resultFileId = f3::get('POST["resultFileId"]');
//### Check clientId, privateKey and fileGuId
if (empty($clientId) || empty($privateKey) || empty($resultFileId)) {
    $error = 'Please enter all required parameters';
    f3::set('error', $error);
} else {
    //Get base path
    $basePath = f3::get('POST["server_type"]');
    //Set variables for Viewer
    F3::set('userId', $clientId);
    F3::set('privateKey', $privateKey);
    //###Create Signer, ApiClient and Storage Api objects
    //Create signer object
    $signer = new GroupDocsRequestSigner($privateKey);
    //Create apiClient object
    $apiClient = new APIClient($signer);
    //Create ComparisonApi object
    $compareApi = new ComparisonApi($apiClient);
    if ($basePath == "") {
        //If base base is empty seting base path to prod server
        $basePath = 'https://api.groupdocs.com/v2.0';
    }
    //Set base path
    $compareApi->setBasePath($basePath);
    //###Make request to ComparisonApi using user id
    //Get changes list for document
    try {
        $info = $compareApi->GetChanges($clientId, $resultFileId);

        //Check request status
        if ($info->status == "Ok") {
            //###Create table with changes for template
            $table = "<table class='border'>";
            $table .= "<tr><td><font color='green'>Change Name</font></td><td>
                    <font color='green'>Change</font></td></tr>";
            //Count of iterations
            for ($i = 0; $i < count($info->result->changes); $i++) {
                //Cycle for the massif of the top level
                foreach ($info->result->changes[$i] as $name => $content) {
                    $table .= "<tr>";
                    //Check is curent element is object
                    if (is_object($content)) {
                        //If object make cycle for the curent object
                        foreach ($content as $subName => $subContent) {

                            $table .= "<tr><td>" . $subName . "</td><td>" . $subContent . "</td></tr>";
                        }
                    } elseif (!is_object($content)) {
                        //Get curent element data
                        $table .= "<td>" . $name . "</td><td>" . $content . "</td>";
                        $table .= "</tr>";
                    }
                }
                $table .= "<tr bgcolor='#808080'><td></td><td></td></tr>";
            }
            $table .= "</table>";
            f3::set('change', $table);
        } else {
            throw new Exception($info->error_message);
        }
    } catch (Exception $e) {
        $error = 'ERROR: ' . $e->getMessage() . "\n";
        f3::set('error', $error);
    }
    //If request was successfull - set url variable for template
//            return f3::set('change', $table);
}
//Process template
f3::set('resultFileId', $resultFileId);
//    f3::set('result', $result);
echo Template::serve('sample20.htm');
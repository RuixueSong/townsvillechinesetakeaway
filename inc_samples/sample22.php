<?php

//### This sample will show how create or update user and add him to collaborators using PHP SDK
// Set variables and get POST data
F3::set('userId', '');
F3::set('privateKey', '');
F3::set('email', '');
F3::set('first_name', '');
F3::set('fileId', '');
F3::set('last_name', '');
$clientId = F3::get('POST["client_id]');
$privateKey = F3::get('POST["private_key"]');
$email = F3::get('POST["email"]');
$firstName = F3::get('POST["first_name"]');
$lastName = F3::get('POST["last_name"]');
$basePath = f3::get('POST["server_type"]');

function updateUser($clientId, $privateKey, $email, $firstName, $lastName, $basePath) {
    //Check if all requared parameters were transferred
    if (empty($clientId) || empty($privateKey) || empty($email) || empty($firstName) || empty($lastName)) {
        //if not send error message
        throw new Exception('Please enter all required parameters');
    } else {
        //path to settings file - temporary save userId and apiKey like to property file
        $infoFile = fopen(__DIR__ . '/../user_info.txt', 'w');
        fwrite($infoFile, $clientId . "\r\n" . $privateKey);
        fclose($infoFile);
        //check if Downloads folder exists and remove it to clean all old files
        if (file_exists(__DIR__ . '/../downloads')) {
            delFolder(__DIR__ . '/../downloads/');
        }
        //Set variables for template "You are entered" block
        F3::set('userId', $clientId);
        F3::set('privateKey', $privateKey);
        F3::set('email', $email);
        F3::set('first_name', $firstName);
        F3::set('last_name', $lastName);
        //### Create Signer, ApiClient and Mgmt Api objects
        // Create signer object
        $signer = new GroupDocsRequestSigner($privateKey);
        // Create apiClient object
        $apiClient = new ApiClient($signer);
        // Create MgmtApi object
        $mgmtApi = new MgmtApi($apiClient);
        //Create Storage Api object
        $storageApi = new StorageApi($apiClient);
        //Declare which Server to use
        if ($basePath == "") {
            //If base base is empty seting base path to prod server
            $basePath = 'https://api.groupdocs.com/v2.0';
        }
        //Set base path
        $mgmtApi->setBasePath($basePath);
        $storageApi->setBasePath($basePath);
        //Get entered by user data
        $url = F3::get('POST["url"]');
        $file = $_FILES['file'];
        $fileGuId = f3::get('POST["fileId"]');
        $fileId = "";
        //Check is file GUID entered
        if ($fileGuId != "") {
            $fileId = $fileGuId;
        }
        if ($url != "") {
            //Upload file from URL
            $uploadResult = $storageApi->UploadWeb($clientId, $url);
            //Check is file uploaded
            if ($uploadResult->status == "Ok") {
                //Get file GUID
                $fileId = $uploadResult->result->guid;

                //If it isn't uploaded throw exception to template
            } else {
                throw new Exception($uploadResult->error_message);
            }
        }
        //Check is local file chosen
        if ($file["name"] != "") {
            //Get uploaded file
            $uploadedFile = $_FILES['file'];


            //###Check uploaded file
            if (null === $uploadedFile) {
                return new RedirectResponse("/sample21");
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
                $fileId = $uploadResult->result->guid;
            } else {
                throw new Exception($uploadResult->error_message);
            }
        }
        //###Create User info object
        //Create User info object
        $user = new UserInfo();
        //Create Role info object
        $role = new RoleInfo();
        //Set user role Id. Can be: 1 -  SysAdmin, 2 - Admin, 3 - User, 4 - Guest
        $role->id = "3";
        //Set user role name. Can be: SysAdmin, Admin, User, Guest
        $role->name = "User";
        //Create array of roles.
        $roles = array($role);
        //Set nick name as entered first name
        $user->nickname = $firstName;
        //Set first name as entered first name
        $user->firstname = $firstName;
        //Set last name as entered last name
        $user->lastname = $lastName;
        $user->roles = $roles;
        //Set email as entered email
        $user->primary_email = $email;
        //Creating of new user. $clientId - user id, $firstName - entered first name, $user - object with new user info
        $newUser = $mgmtApi->UpdateAccountUser($clientId, $email, $user);
        //Check the result of the request
        if ($newUser->status == "Ok") {
            //### If request was successfull
            //Create Annotation api object
            $ant = new AntApi($apiClient);
            $ant->setBasePath($basePath);
            //Create array with entered email for SetAnnotationCollaborators method 
            $arrayEmail = array($email);
            //Make request to Ant api for set new user as annotation collaborator
            $addCollaborator = $ant->SetAnnotationCollaborators($clientId, $fileId, "2.0", $arrayEmail);
            if ($addCollaborator->status == "Ok") {
                //Make request to Annotation api to receive all collaborators for entered file id
                $getCollaborators = $ant->GetAnnotationCollaborators($clientId, $fileId);
                if ($getCollaborators->status == "Ok") {
                    //Set reviewers rights for new user. $newUser->result->guid - GuId of created user, $fileId - entered file id, 
                    //$getCollaborators->result->collaborators - array of collabotors in which new user will be added
                    $setReviewer = $ant->SetReviewerRights($newUser->result->guid, $fileId, $getCollaborators->result->collaborators);
                    if ($setReviewer->status == "Ok") {
                        //Generating iframe for template
                        if ($basePath == "https://api.groupdocs.com/v2.0") {
                            $iframe = 'https://apps.groupdocs.com//document-annotation2/embed/' . 
                                    $fileId . '?&uid=' . $newUser->result->guid . '&download=true frameborder="0" width="720" height="600"';
                            //iframe to dev server
                        } elseif ($basePath == "https://dev-api.groupdocs.com/v2.0") {
                            $iframe = 'https://dev-apps.groupdocs.com//document-annotation2/embed/' . 
                                    $fileId . '?&uid=' . $newUser->result->guid . '&download=true frameborder="0" width="720" height="600"';
                            //iframe to test server
                        } elseif ($basePath == "https://stage-apps-groupdocs.dynabic.com/v2.0") {
                            $iframe = 'https://stage-apps-groupdocs.dynabic.com/document-annotation2/embed/' . 
                                    $fileId . '?&uid=' . $newUser->result->guid . '&download=true frameborder="0" width="720" height="600"';
                        } elseif ($basePath == "http://realtime-api.groupdocs.com") {
                            $iframe = 'http://realtime-apps.groupdocs.com/document-annotation2/embed/' . 
                                    $fileId . '?&uid=' . $newUser->result->guid . '&download=true frameborder="0" width="720" height="600"';
                        }
                    } else {
                        throw new Exception($setReviewer->error_message);
                    }
                } else {
                    throw new Exception($getCollaborators->error_message);
                }
            } else {
                throw new Exception($addCollaborator->error_message);
            }
            //Set variable with work results for template
            F3::set('fileId', $fileId);
            return F3::set('url', $iframe);
        } else {
            return F3::set("message", $newUser->error_message);
        }
    }
}

//### Delete downloads folder and all files in this folder
function delFolder($path) {
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
            unlink($next);
        }
    }
    //Delete folder
    rmdir($path);
}

try {
    updateUser($clientId, $privateKey, $email, $firstName, $lastName, $basePath);
} catch (Exception $e) {
    $error = 'ERROR: ' . $e->getMessage() . "\n";
    f3::set('error', $error);
}

// Process template
echo Template::serve('sample22.htm');
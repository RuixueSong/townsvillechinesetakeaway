<?php
	//Local path to the text file with user data
    $userInfo = file(__DIR__ . '/../../user_info.txt');
    //Get user data from text file
    $clientId = trim($userInfo[0]);
    $privateKey = trim($userInfo[1]);
    //Get raw data
    $json = file_get_contents("php://input");
    //Decode json with raw data to array
	$callBack_data = json_decode($json, true);
    //Get job id from array
	$envelopeId = $callBack_data["SourceId"];
    //Create signer object
    $signer = new GroupDocsRequestSigner(trim($privateKey));
    //Create apiClient object
    $apiClient = new APIClient($signer);
    //Create AsyncApi object
    $signatureApi = new SignatureApi($apiClient);
    //Create Storage Api object
    $storageApi = new StorageApi($apiClient);
	$document = $signatureApi->GetSignatureEnvelopeDocuments($clientId, $envelopeId);
	if ($document->status == "Ok") {
		$guid = $document->result->documents[0]->documentId;
		$name = $document->result->documents[0]->name;
	}
    //Local path to the downloads folder
    $downloadFolder = dirname(__FILE__). '/../../downloads';
    //Check is folder exist
    if (!file_exists($downloadFolder)) {
        //If folder don't exist create it
        mkdir($downloadFolder);
    }
    //Obtaining file stream of downloading file and definition of folder where to download file
    $outFileStream =  FileStream::fromHttp($downloadFolder, $name);
    //Download file from GroupDocs.
    $download = $storageApi->GetFile($clientId, $guid, $outFileStream);
    

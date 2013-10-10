<?php
require_once __DIR__.'/FatFree_Framework/lib/base.php';
require_once __DIR__.'/vendor/autoload.php';
F3::set('CACHE',FALSE);
F3::set('DEBUG',1);
F3::set('UI','templates/');
F3::set('IMPORTS','inc_samples/');


F3::route('GET /','home');
F3::route('GET /index.php','home');
F3::route('GET|POST /sample01','sample01.php'); //route for GET and POST requests
//F3::route('GET /sample1','sample1.php'); //we can have saparate handles for GET and POST requests
//F3::route('POST /sample1','sample1.php');

F3::route('GET|POST /sample02','sample02.php');
F3::route('GET|POST /sample03','sample03.php');
F3::route('GET|POST /sample04','sample04.php');
F3::route('GET|POST /sample05','sample05.php');
F3::route('GET|POST /sample06','sample06.php');
F3::route('GET|POST /sample07','sample07.php');
F3::route('GET|POST /sample08','sample08.php');
F3::route('GET|POST /sample09','sample09.php');
F3::route('GET|POST /sample10','sample10.php');
F3::route('GET|POST /sample11','sample11.php');
F3::route('GET|POST /sample12','sample12.php');
F3::route('GET|POST /sample13','sample13.php');
F3::route('GET|POST /sample14','sample14.php');
F3::route('GET|POST /sample15','sample15.php');
F3::route('GET|POST /sample16','sample16.php');
F3::route('GET|POST /sample17','sample17.php');
F3::route('GET|POST /sample18','sample18.php');
F3::route('GET|POST /sample19','sample19.php');
F3::route('GET|POST /sample20','sample20.php');
F3::route('GET|POST /sample21','sample21.php');
F3::route('GET|POST /sample22','sample22.php');
F3::route('GET|POST /sample23','sample23.php');
F3::route('GET|POST /sample24','sample24.php');
F3::route('GET|POST /sample25','sample25.php');
F3::route('GET|POST /sample26','sample26.php');
F3::route('GET|POST /sample27','sample27.php');
F3::route('GET|POST /sample28','sample28.php');
F3::route('GET|POST /sample29','sample29.php');
F3::route('GET|POST /sample30','sample30.php');
F3::route('GET|POST /sample31','sample31.php');
F3::route('GET|POST /sample32','sample32.php');
F3::route('GET|POST /callbacks/signature_callback','callbacks/signature_callback.php');
F3::route('GET|POST /callbacks/signature_check_file','callbacks/signature_check_file.php');
F3::route('GET|POST /callbacks/annotation_callback','callbacks/annotation_callback.php');
F3::route('GET|POST /callbacks/annotation_check_file','callbacks/annotation_check_file.php');
F3::route('GET|POST /callbacks/compare_callback','callbacks/compare_callback.php');
F3::route('GET|POST /callbacks/compare_check_file','callbacks/compare_check_file.php');
F3::route('GET|POST /callbacks/convert_callback','callbacks/convert_callback.php');
F3::route('GET|POST /callbacks/check_file','callbacks/check_file.php');
F3::route('GET|POST /callbacks/download_file','callbacks/download_file.php');
F3::route('GET|POST /callbacks/publish_callback','callbacks/publish_callback.php');

F3::route('GET /about_framework.php','about');

	function home() {
        //sample code
        //$apiKey = 'cebff9b66782df9e519c1fc11c0a7ac3';
        //$clientId = '60bef2f950c9cd0e';
        //$fileId = '47d86daacf8bbcd66c1dab08791a459272dcfa48cbc5ed8f07ec297f43d21186';
        //$signer = new GroupDocsRequestSigner($apiKey);
        //$apiClient = new APIClient($signer); // PHP SDK V1.1
        //$antApi = new AntApi($apiClient);
        //$annotations = $antApi->ListAnnotations($clientId, $fileId);
        //echo var_dump($annotations);
		echo Template::serve('index.htm');
	}
    function about() {
		echo Template::serve('welcome.htm');
	}
F3::run();

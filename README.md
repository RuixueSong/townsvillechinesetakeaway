## Examples

### GroupDocs PHP SDK Api samples

This is the GroupDocs PHP SDK Api samples application written with FatFree framework. Here you can find a lot of samples of GroupDocs SDK API functions using.

### How to deploy and run samples

 1. Download api-samples folder or full SDK (you can find api-samples under examples folder).
 2. Copy all files from api-samples folder to web root folder.
 3. Configure composer.json to use required PHP SDK version.
 4. Open console, cd to web root folder and run command: php composer.phar install (this will download GroupDocs PHP SDK into vendor folder and create autoload.php).
 5. Restart apache and open "VIRTUALHOST_NAME"/index.php.

### Requirements:

* PHP 5.3
* Apache ModRewrite
* PHP Curl extension
* PHP Sockets extension (php_sockets.dll)
* composer.phar (http://getcomposer.org/download/ or use included version)

### How to configure composer.json

To download required version of PHP SDK with composer it's enough to set this setting to composer.json

     {
         "require": {
             "groupdocs/groupdocs-php": "v1.4.0"
         }
      }

To update sdk: php composer.phar update

To see all available PHP SDK versions tags visit this page - https://packagist.org/packages/groupdocs/groupdocs-php

### List of samples:

* How to authorize to GroupDocs using the API and get user info
* How to list files within GroupDocs Storage using the Storage API
* How to upload a file to GroupDocs using the Storage API
* How to download a file from GroupDocs Storage using the Storage API
* How to copy / move a file using the GroupDocs Storage API
* How to add a Signature to a document in GroupDocs Signature
* How to create a list of thumbnails for a document
* How to return a URL representing a single page of a Document
* How to generate an embedded Viewer URL for a Document
* How to share a document to other users
* How programmatically create and post an annotation into document. How to delete the annotation
* How to list all annotations from document
* How to add collaborator to doc with annotations
* How to check the list of shares for a folder
* How to check the number of document's views
* How to insert Assembly questionary into webpage
* How to upload a file into the storage and compress it into zip archive



###[View, Sign, Manage, Annotate, Assemble, Compare and Convert Documents with GroupDocs](http://groupdocs.com)
* [View and Annotate Doc, PDF, Docx, PPT and other documents online with GroupDocs Viewer](http://groupdocs.com/apps)
* [All GroupDocs SDK] (http://groupdocs.com/api/sdk-platforms)
* [All GroupDocs SDK examples] (http://groupdocs.com/api/sdk-examples)

###Created by [Marketplace Team](http://groupdocs.com/marketplace/).

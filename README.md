# defileuploader



# Usage

Receive a file sent from client.

```
$oUploader = new CDeFileUploader();
$oUploader->setAllowFileExt( Array( 'jpg', 'jpeg', 'png' ) );
$oUploader->setLmtMaxSize( 2 * 1024 * 1024 );	//	2M
$oUploader->setOverwrite( true );
$oUploader->saveUploadFile( $sDstFilePath, & $vStreamData = "" );
```
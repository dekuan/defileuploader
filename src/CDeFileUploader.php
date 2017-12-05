<?php
namespace dekuan\defileuploader;

use dekuan\delib\CLib;


/***
 --------------------------------------------------------------------------------

 Rewrote in composer format
	@ Aug. 28, 2017 by liuqixing@gmail.com

 New De File Uploader
	@ Aug., 2012 by liuqixing@gmail.com

 Usage:
	$oUploader = new CDeFileUploader();
	$oUploader->setAllowFileExt( Array( 'jpg', 'jpeg', 'png' ) );
	$oUploader->setLmtMaxSize( 2 * 1024 * 1024 );	//	2M
	$oUploader->setOverwrite( true );
	$oUploader->saveUploadFile( $sDstFilePath, & $vStreamData = "" );

 --------------------------------------------------------------------------------
 ***/

	
/**
 *	field name
 */
define( 'DEFILEUPLOADER_FIELDNAME',			'defile' );	//	<input type=file name="defile" ...

define( 'DEFILEUPLOADER_SUCCESS',			 0 );
define( 'DEFILEUPLOADER_ERROR_UNKNOWN',			-1 );
define( 'DEFILEUPLOADER_FAILED_SAVE_BY_FORM',		-100 );
define( 'DEFILEUPLOADER_FAILED_SAVE_BY_XHR',		-200 );
define( 'DEFILEUPLOADER_ERROR_PARAM',			-1100 );
define( 'DEFILEUPLOADER_ERROR_PARAM_SAVE_FILE',		-1101 );
define( 'DEFILEUPLOADER_ERROR_MOVEFILE',		-1200 );
define( 'DEFILEUPLOADER_ERROR_READ_SOURCE_FILE',	-1210 );
define( 'DEFILEUPLOADER_ERROR_WRITE_DEST_FILE',		-1215 );
define( 'DEFILEUPLOADER_ERROR_CREATE_TMP_FILE',		-1220 );
define( 'DEFILEUPLOADER_ERROR_SERVER_ERROR',		-1301 );	//	server error
define( 'DEFILEUPLOADER_ERROR_NOT_ALLOWED',		-1400 );
define( 'DEFILEUPLOADER_ERROR_TOO_LARGE',		-1500 );	//	File is too large
define( 'DEFILEUPLOADER_ERROR_NO_FILES',		-1510 );	//	No files were uploaded.
define( 'DEFILEUPLOADER_ERROR_EMPTY_FILE',		-1520 );	//	File is empty
define( 'DEFILEUPLOADER_ERROR_DIR_UNWRITABLE',		-1600 );	//	Upload directory isn't writable.







/**
 *	CDeFileUploader
 *	Created by XING @ 11:56 AM August 28, 2017
 **/
class CDeFileUploader extends CDeUploadedFileFieldName
{
	var $m_oFile		= null;
	var $m_arrAllowFileExt	= null;		//	Array( 'jpg', 'jpeg', 'gif', 'png' );
	var $m_arrImageExt	= null;
	var $m_nLmtMaxSize	= 0;		//	10485760
	var $m_bOverwrite	= true;		//	overwrite if file exists

	public function __construct()
	{
		parent::__construct();

		//	...
		$this->m_oFile			= null; 
		$this->m_arrAllowFileExt	= [ 'jpg', 'jpeg', 'png' ];
		$this->m_arrImageExt		= [ 'jpg', 'jpeg', 'png', 'bmp', 'gif' ];
		$this->m_nLmtMaxSize		= 500 * 1024;	//	500K
		$this->m_bOverwrite		= true;

		if ( isset( $_FILES[ $this->m_sFieldName ] ) )
		{
			$this->m_oFile = new CDeUploadedFileForm();
			$this->m_oFile->setFieldName( $this->m_sFieldName );
		}
		else if ( isset( $_GET[ $this->m_sFieldName ] ) )
		{
			$this->m_oFile = new CDeUploadedFileXhr();
			$this->m_oFile->setFieldName( $this->m_sFieldName );
		}
	}

	public function setAllowFileExt( $arrAllowFileExt )
	{
		$this->m_arrAllowFileExt = $arrAllowFileExt;
	}
	public function setImageExt( $arrImageExt )
	{
		$this->m_arrImageExt = $arrImageExt;
	}

	public function setLmtMaxSize( $nLmtMaxSize )
	{
		//	in bytes
		$this->m_nLmtMaxSize = $nLmtMaxSize;
	}
	public function setOverwrite( $bOverwrite )
	{
		$this->m_bOverwrite = $bOverwrite;
	}

	public function isUploadSucc( $nErrorCode )
	{
		return ( DEFILEUPLOADER_SUCCESS == $nErrorCode );
	}

	public function getTempFullFilename()
	{
		if ( $this->_isValidLmtMaxSize() && $this->m_oFile->getSize() <= $this->m_nLmtMaxSize )
		{
			return $this->m_oFile->getTempFullFilename();	
		}
		else
		{
			return null;
		}
	}

	public function saveUploadFile( $sDstFilePath = null, & $vStreamData = null )
	{
		//
		//	sDstFilePath	- the destination filename, values:( full filename or null )
		//	vStreamData	- if the value of sDstFilePath is null, then,
		//				this function will copy file stream to vStreamData
		//	RETURN		- errorid or succ as 0
		//
		//	$_FILE = Array
		//	(
		//		[qqfile] => Array
		//		(
		//			[name] => 2003-2010 pm2.5.png
		//			[type] => image/jpeg
		//			[tmp_name] => D:\soft\Wamp\tmp\php20BF.tmp
		//			[error] => 0
		//			[size] => 111631
		//		)
		//	)
		//
		$nRet = DEFILEUPLOADER_ERROR_UNKNOWN;
		$sFinalDstFilePath = "";

		if ( ! $this->m_oFile )
		{
			//	No files were uploaded.
			return DEFILEUPLOADER_ERROR_NO_FILES;
		}
		if ( ! $this->_isValidLmtMaxSize() )
		{
			return DEFILEUPLOADER_ERROR_TOO_LARGE;
		}
		if ( $this->m_oFile->getSize() > $this->m_nLmtMaxSize )
		{
			//	File is too large
			return DEFILEUPLOADER_ERROR_TOO_LARGE;
		}
		if ( 0 == $this->m_oFile->getSize() )
		{
			//	File is empty
			return DEFILEUPLOADER_ERROR_EMPTY_FILE;
		}
		if ( ! $this->_isAllowedFile() )
		{
			//	File extension isn't allowed
			return DEFILEUPLOADER_ERROR_NOT_ALLOWED;
		}

		if ( CLib::IsExistingString( $sDstFilePath ) )
		{
			if ( $this->_isDirectoryWritable( $sDstFilePath ) )
			{
				$sFinalDstFilePath = $this->_getFinalDstFilePath( $sDstFilePath );
				$nRet = $this->m_oFile->saveUploadFile( $sFinalDstFilePath );
			}
			else
			{
				//	Server error. Upload directory isn't writable.
				$nRet = DEFILEUPLOADER_ERROR_DIR_UNWRITABLE;
			}
		}
		else if ( ! is_null( $vStreamData ) )
		{
			$nRet = $this->m_oFile->saveUploadFile( null, $vStreamData );
		}
		else
		{
			$nRet = DEFILEUPLOADER_ERROR_PARAM_SAVE_FILE;
		}

		return $nRet;
	}

	public function getName()
	{
		if ( $this->m_oFile )
		{
			return $this->m_oFile->getName();
		}
	}
	public function getExt()
	{
		//	RETURN	'jpg', 'png', 'exe', ...
		$sRet		= "";
		$sFileName	= $this->getName();
		if ( CLib::IsExistingString( $sFileName ) )
		{
			$pDot = strrchr( $sFileName, '.' );
			if ( CLib::IsExistingString( $pDot ) )
			{
				$sRet = strtolower( substr( $pDot, 1 ) );
			}
		}

		return $sRet;
	}
	public function isImageExt()
	{
		$bRet		= false;
		$sFileExt	= $this->getExt();
		if ( CLib::IsExistingString( $sFileExt ) )
		{
			if ( in_array( $sFileExt, $this->m_arrImageExt ) )
			{
				$bRet = true;
			}
		}

		return $bRet;
	}


	////////////////////////////////////////////////////////////////////////////////
	//	Private
	//

	private function _isValidLmtMaxSize()
	{
		$bRet	= false;

		//	...
		$nMaxPostSize	= $this->_toBytes( ini_get( 'post_max_size' ) );
		$nMaxUploadSize	= $this->_toBytes( ini_get( 'upload_max_filesize') );        

		if ( $this->m_nLmtMaxSize <= $nMaxPostSize && $this->m_nLmtMaxSize <= $nMaxUploadSize )
		{
			//	$size = max( 1, $this->m_nLmtMaxSize / 1024 / 1024 ) . 'M';
			//	die( "{'error':'increase post_max_size and upload_max_filesize to $size'}" );
			$bRet = true;
		}

		return $bRet;
	}

	private function _toBytes( $sString )
	{
		$nRet	= 0;

		$sString = trim( $sString );
		if ( CLib::IsExistingString( $sString ) )
		{
			$sLast = strtolower( substr( $sString, -1, 1 ) );
			if ( CLib::IsExistingString( $sLast ) )
			{
				$nRet = intval( substr( $sString, 0, -1 ) );
				switch( $sLast )
				{
					case 'g': $nRet *= 1024;
					case 'm': $nRet *= 1024;
					case 'k': $nRet *= 1024;
				}
			}
		}

		return $nRet;
	}

	private function _isDirectoryWritable( $sDstFilePath )
	{
		$bRet		= false;
		$ArrPathInfo	= Array();

		if ( CLib::IsExistingString( $sDstFilePath ) )
		{
			$ArrPathInfo = @pathinfo( $sDstFilePath );
			if ( is_array( $ArrPathInfo ) && isset( $ArrPathInfo['dirname'] ) )
			{
				if ( is_dir( $ArrPathInfo['dirname'] ) )
				{
					if ( is_writable( $ArrPathInfo['dirname'] ) )
					{
						$bRet = true;
					}
				}
			}
		}

		return $bRet;
	}

	private function _getFinalDstFilePath( $sDstFilePath )
	{
		$sRet		= $sDstFilePath;
		$ArrPathInfo	= Array();
		$sDirName	= "";
		$sBaseName	= "";
		$sExtension	= "";
		$sFileName	= "";

		if ( CLib::IsExistingString( $sDstFilePath ) )
		{
			$ArrPathInfo = @pathinfo( $sDstFilePath );
			if ( is_array( $ArrPathInfo ) &&
				isset( $ArrPathInfo['dirname'] ) &&
				isset( $ArrPathInfo['basename'] ) &&
				isset( $ArrPathInfo['extension'] ) )
			{
				$sDirName	= $ArrPathInfo['dirname'];
				$sBaseName	= $ArrPathInfo['basename'];
				$sExtension	= $ArrPathInfo['extension'];
				$sFileName	= basename( $sBaseName, ( '.' . $sExtension ) );

				if ( ! $this->m_bOverwrite )
				{
					//	don't overwrite previous files that were uploaded
					while ( file_exists( ( $sDirName . '/' . $sFileName . '.' . $sExtension ) ) )
					{
						$sFileName .= rand( 1000, 9999 );
					}

					//	...
					$sRet = ( $sDirName . '/' . $sFileName . '.' . $sExtension );
				}
			}
		}

		return $sRet;
	}

	private function _isAllowedFile()
	{
		//	DEFILEUPLOADER_ERROR_NOT_ALLOWED
		$bRet		= false;
		$sFileExt	= "";

		if ( ! $this->m_oFile )
		{
			return false;
		}

		$ArrPathInfo = pathinfo( $this->m_oFile->getName() );
		if ( is_array( $ArrPathInfo ) &&
			isset( $ArrPathInfo[ 'extension' ] ) )
		{
			$sFileExt = strtolower( $ArrPathInfo[ 'extension' ] );

			if ( CLib::IsExistingString( $sFileExt ) &&
				in_array( $sFileExt, $this->m_arrAllowFileExt ) )
			{
				$bRet = true;
			}
		}

		return $bRet;
	}
}
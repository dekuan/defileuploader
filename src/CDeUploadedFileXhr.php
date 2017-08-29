<?php
namespace dekuan\defileuploader;

use dekuan\delib\CLib;


/**
 *	Handle file uploads via XMLHttpRequest
 *	Created by XING @ 11:56 AM August 28, 2017
 */
class CDeUploadedFileXhr extends CDeUploadedFileFieldName
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getTempFullFilename()
	{
		$sRet		= null;
		$nRealSize	= 0;
		$sTmpFFN	= null;
		$fpTemp		= null;
		$fpInput	= null;

		//	...
		$sTmpFFN	= tempnam( sys_get_temp_dir(), "defileuploader-" );
		if ( CLib::IsExistingString( $sTmpFFN ) )
		{
			$fpTemp	= fopen( $sTmpFFN, "w" );
			if ( $fpTemp )
			{
				$fpInput = fopen( "php://input", "r" );
				if ( $fpInput )
				{
					$nRealSize = stream_copy_to_stream( $fpInput, $fpTemp );
					fclose( $fpInput );

					if ( $nRealSize > 0 && $nRealSize == $this->getSize() )
					{
						//
						//	yes, it's okay now
						//
						$sRet = $sTmpFFN;
					}
				}

				fclose( $fpTemp );
				$fpTemp = null;
			}
		}

		return $sRet;
	}

	public function saveUploadFile( $sDstFilePath = null, & $vStreamData = null )
	{
		//
		//	sDstFilePath	- the destination filename, values:( full filename or null )
		//	vStreamData	- if the value of sDstFilePath is null, then,
		//				this function will copy file stream to vStreamData
		//	RETURN		- true / false
		//
		$nRet		= DEFILEUPLOADER_FAILED_SAVE_BY_XHR;
		$nRealSize	= 0;
		$fpTemp		= null;
		$fpInput	= null;
		$fpTarget	= null;

		//	...
		$fpTemp = tmpfile();
		if ( $fpTemp )
		{
			$fpInput = fopen( "php://input", "r" );
			if ( $fpInput )
			{
				$nRealSize = stream_copy_to_stream( $fpInput, $fpTemp );
				fclose( $fpInput );
			}

			if ( $nRealSize > 0 && $nRealSize == $this->getSize() )
			{
				if ( CLib::IsExistingString( $sDstFilePath ) )
				{
					$fpTarget = fopen( $sDstFilePath, "w" );
					if ( $fpTarget )
					{
						fseek( $fpTemp, 0, SEEK_SET );
						stream_copy_to_stream( $fpTemp, $fpTarget );
						fclose( $fpTarget );
						$fpTarget = null;

						//	...
						$nRet = DEFILEUPLOADER_SUCCESS;
					}
					else
					{
						$nRet = DEFILEUPLOADER_ERROR_WRITE_DEST_FILE;
					}
				}
				else if ( ! is_null( $vStreamData ) )
				{
					fseek( $fpTemp, 0, SEEK_SET );
					while ( ! feof( $fpTemp ) )
					{
						$vStreamData .= fread( $fpTemp, 8192 );
					}

					//	...
					$nRet = DEFILEUPLOADER_SUCCESS;
				}
				else
				{
					$nRet = DEFILEUPLOADER_ERROR_PARAM_SAVE_FILE;
				}
			}
			else
			{
				$nRet = DEFILEUPLOADER_ERROR_EMPTY_FILE;
			}

			//	...
			fclose( $fpTemp );
			$fpTemp = null;
		}
		else
		{
			$nRet = DEFILEUPLOADER_ERROR_CREATE_TMP_FILE;
		}

		return $nRet;
	}

	public function getName()
	{
		$sRet	= "";

		if ( isset( $_GET[ $this->m_sFieldName ] ) )
		{
			$sRet = $_GET[ $this->m_sFieldName ];
		}

		return $sRet;
	}

	public function getSize()
	{
		$nRet	= 0;

		if ( isset( $_SERVER[ 'CONTENT_LENGTH' ] ) )
		{
			$nRet = intval( $_SERVER[ 'CONTENT_LENGTH' ] );
		}

		return $nRet;
	}
}
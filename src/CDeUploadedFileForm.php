<?php
namespace dekuan\defileuploader;

use dekuan\delib\CLib;


/**
 *	Handle file uploads via regular form post (uses the $_FILES array)
 * 	Created by XING @ 11:56 AM August 28, 2017
 */
class CDeUploadedFileForm extends CDeUploadedFileFieldName
{
	function __construct()
	{
		parent::__construct();
	}

	function saveUploadFile( $sDstFilePath = null, & $vStreamData = null )
	{
		//
		//	sDstFilePath	- the destination filename, values:( full filename or null )
		//	vStreamData	- if the value of sDstFilePath is null, then,
		//				this function will copy file stream to vStreamData
		//	RETURN		- true / false
		//
		$nRet		= DEFILEUPLOADER_FAILED_SAVE_BY_FORM;
		$fpTarget	= null;
		$sTmpFilePath	= "";

		if ( CLib::IsExistingString( $sDstFilePath ) )
		{
			if ( move_uploaded_file( $_FILES[ $this->m_sFieldName ]['tmp_name'], $sDstFilePath ) )
			{
				$nRet = DEFILEUPLOADER_SUCCESS;
			}
			else
			{
				$nRet = DEFILEUPLOADER_ERROR_MOVEFILE;
			}
		}
		else if ( ! is_null( $vStreamData ) )
		{
			$sTmpFilePath = $_FILES[ $this->m_sFieldName ]['tmp_name'];
			$fpTarget = fopen( $sTmpFilePath, "rb" );
			if ( $fpTarget )
			{
				while ( ! feof( $fpTarget ) )
				{
					$vStreamData .= fread( $fpTarget, 8192 );
				}
				fclose( $fpTarget );
				unset( $fpTarget );

				//	...
				$nRet = DEFILEUPLOADER_SUCCESS;
			}
			else
			{
				$nRet = DEFILEUPLOADER_ERROR_READ_SOURCE_FILE;
			}
		}
		else
		{
			$nRet = DEFILEUPLOADER_ERROR_PARAM_SAVE_FILE;
		}

		return $nRet;
	}

	function getName()
	{
		$sRet	= "";

		if ( isset( $_FILES[ $this->m_sFieldName ]['name'] ) )
		{
			$sRet = $_FILES[ $this->m_sFieldName ]['name'];
		}

		return $sRet;
	}
	function getSize()
	{
		$nRet	= 0;

		if ( isset( $_FILES[ $this->m_sFieldName ]['size'] ) )
		{
			$nRet = intval( $_FILES[ $this->m_sFieldName ]['size'] );
		}
		if ( 0 == $nRet )
		{
			if ( isset( $_SERVER[ 'CONTENT_LENGTH' ] ) )
			{
				$nRet = intval( $_SERVER[ 'CONTENT_LENGTH' ] );
			}
		}

		return $nRet;
	}
}
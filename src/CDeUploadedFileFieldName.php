<?php
namespace dekuan\defileuploader;

/**
 *	get/set field name
 *	Created by XING @ 11:56 AM August 28, 2017
 */
class CDeUploadedFileFieldName
{
	var $m_sFieldName	= DEFILEUPLOADER_FIELDNAME;	//	<input type=file name="qqfile" ...

	function __construct()
	{
		$this->m_sFieldName = DEFILEUPLOADER_FIELDNAME;
	}

	public function setFieldName( $sFieldName )
	{
		$this->m_sFieldName = $sFieldName;
	}
}
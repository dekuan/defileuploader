<?php
namespace dekuan\defileuploader;

/**
 *	Class CDeUploadedFileFieldName
 *	get/set field name
 *	Created by XING @ 11:56 AM August 28, 2017
 * 
 *	@package dekuan\defileuploader
 */
class CDeUploadedFileFieldName
{
	var $m_sFieldName	= DEFILEUPLOADER_FIELDNAME;	//	<input type=file name="qqfile" ...

	/**
	 *	CDeUploadedFileFieldName constructor.
	 */
	public function __construct()
	{
		$this->m_sFieldName = DEFILEUPLOADER_FIELDNAME;
	}

	/**
	 *	@param $sFieldName
	 */
	public function setFieldName( $sFieldName )
	{
		$this->m_sFieldName = $sFieldName;
	}
}
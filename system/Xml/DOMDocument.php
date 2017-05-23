<?php
/**
 * @brief		Wrapper class for managing DOMDocument objects
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		5 July 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\Xml;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Wrapper class for managing DOMDocument objects
 */
class _DOMDocument extends \DOMDocument
{
	/**
	 * Load XML from a file
	 *
	 * @param	string	$filename	The filename
	 * @param	int		$options	Bitmask of LIBXML_* constants
	 * @return	bool
	 * @note	We are disabling the entity loader after opening the content to prevent XXE
	 */
	public function load( $filename, $options=0 )
	{
		libxml_use_internal_errors(TRUE);
		$entityLoaderValue = libxml_disable_entity_loader( false );
		$opened = parent::load( $filename, $options );
		libxml_disable_entity_loader( $entityLoaderValue );

		return $opened;
	}

	/**
	 * Load HTML from a string 
	 *
	 * @param	string	$source		The HTML to open
	 * @param	int		$options	Bitmask of LIBXML_* constants
	 * @return	bool
	 * @note	We are disabling the entity loader after opening the content to prevent XXE
	 */
	public function loadHTML( $source, $options=0 )
	{
		libxml_use_internal_errors(TRUE);
		$entityLoaderValue = libxml_disable_entity_loader( false );
		$opened = parent::loadHTML( $source, $options );
		libxml_disable_entity_loader( $entityLoaderValue );

		return $opened;
	}

	/**
	 * Load HTML from a file
	 *
	 * @param	string	$filename	The filename
	 * @param	int		$options	Bitmask of LIBXML_* constants
	 * @return	bool
	 * @note	We are disabling the entity loader after opening the content to prevent XXE
	 */
	public function loadHTMLFile( $filename, $options=0 )
	{
		libxml_use_internal_errors(TRUE);
		$entityLoaderValue = libxml_disable_entity_loader( false );
		$opened = parent::loadHTMLFile( $filename, $options );
		libxml_disable_entity_loader( $entityLoaderValue );

		return $opened;
	}

	/**
	 * Load XML from a string 
	 *
	 * @param	string	$source		The HTML to open
	 * @param	int		$options	Bitmask of LIBXML_* constants
	 * @return	bool
	 * @note	We are disabling the entity loader after opening the content to prevent XXE
	 */
	public function loadXML( $source, $options=0 )
	{
		libxml_use_internal_errors(TRUE);
		$entityLoaderValue = libxml_disable_entity_loader( false );
		$opened = parent::loadXML( $source, $options );
		libxml_disable_entity_loader( $entityLoaderValue );

		return $opened;
	}

	/**
	 * Prefix HTML content with certain HTML to force DOMDocument to treat the content as UTF-8-encoded HTML
	 *
	 * @param	string	$content	HTML content to prefix
	 * @return	string
	 */
	static public function wrapHtml( $content )
	{
		return "<!DOCTYPE html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/></head>" . $content;
	}
}
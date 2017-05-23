<?php
/**
 * @brief		Visual Language Editor
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		27 Jun 2013
 * @version		SVN_VERSION_NUMBER
 */
 
namespace IPS\core\modules\admin\system;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Visual Language Editor
 */
class _vle extends \IPS\core\modules\front\system\vle
{
	// This class only exists to extend the front-end controller
	// so that within the ACP we can call this endpoint and the
	// CSRF checks will work
}
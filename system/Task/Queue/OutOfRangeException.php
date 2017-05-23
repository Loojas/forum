<?php
/**
 * @brief		Background (queue) Task Exception
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		12 May 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\Task\Queue;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Task Exception
 */
class _OutOfRangeException extends \OutOfRangeException
{
	/**
	 * Constructor
	 *
	 * @note	This exception is thrown to indicate the task has completed successfully
	 * @param	string		$message	Error Message
	 * @return	void
	 */
	public function __construct( $message='' )
	{
		return parent::__construct( $message );
	}
}
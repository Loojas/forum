<?php
/**
 * @brief		chatlogrefresh Task
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Chat
 * @since		15 Mar 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\chat\tasks;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * chatlogrefresh Task
 */
class _chatlogrefresh extends \IPS\Task
{
	/**
	 * Execute
	 *
	 * If ran successfully, should return anything worth logging. Only log something
	 * worth mentioning (don't log "task ran successfully"). Return NULL (actual NULL, not '' or 0) to not log (which will be most cases).
	 * If an error occurs which means the task could not finish running, throw an \IPS\Task\Exception - do not log an error as a normal log.
	 * Tasks should execute within the time of a normal HTTP request.
	 *
	 * @return	mixed	Message to log or NULL
	 * @throws	\IPS\Task\Exception
	 */
	public function execute()
	{
		if ( !\IPS\Application::appIsEnabled( 'chat' ) )
		{
			return NULL;
		}

		if ( !\IPS\Settings::i()->ipb_reg_number )
		{
			throw new \IPS\Task\Exception( $this, 'ipschat_no_license_admin' );
		}

		/* Get last log time */
		$max = \IPS\Db::i()->select( 'MAX(log_time)', 'chat_log_archive' )->first();

		/* Fetch logs */
		try
		{
			$result		= \IPS\Http\Url::external( \IPS\chat\modules\front\chat\view::MASTERSERVER . "get_logs.php?api_key=" . \IPS\Settings::i()->ipb_reg_number . "&timestamp=" . $max )->request()->get();
		}
		catch( \IPS\Http\Request\Exception $e )
		{
			throw new \IPS\Task\Exception( $this, $e->getMessage() );
		}

		$results	= explode( ',', $result );
		$status		= array_shift( $results );
		
		if( $status == 0 )
		{
			throw new \IPS\Task\Exception( $this, 'connect_gw_error_' . $results[0] );
		}
		
		foreach( $results as $k => $v )
		{
			$_thisResult	= explode( "~~||~~", $v );
			
			if( !isset( $_thisResult[1] ) OR !$_thisResult[1] )
			{
				continue;
			}

			$_insert		= array(
									'log_room_id'		=> $_thisResult[1],
									'log_time'			=> $_thisResult[2],
									'log_code'			=> $_thisResult[3],
									'log_user'			=> $_thisResult[4],
									'log_message'		=> $_thisResult[5],
									'log_extra'			=> $_thisResult[6],
									);

			\IPS\Db::i()->insert( "chat_log_archive", $_insert );
		}

		return NULL;
	}
	
	/**
	 * Cleanup
	 *
	 * If your task takes longer than 15 minutes to run, this method
	 * will be called before execute(). Use it to clean up anything which
	 * may not have been done
	 *
	 * @return	void
	 */
	public function cleanup()
	{
		
	}
}
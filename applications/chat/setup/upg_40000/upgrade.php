<?php
/**
 * @brief		4.0.0 Upgrade Code
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Chat
 * @since		16 June 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\chat\setup\upg_40000;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * 4.0.0 RC 5 Upgrade Code
 */
class _Upgrade
{
	/**
	 * Step 1
	 * Fix anything missing
	 *
	 * @note	We had an old chat services application with directory 'chat', then we released IP.Chat with directory 'ipchat'.
	 *	Sometimes clients are upgrading from the old old 'chat' application but that means they're missing all tables/columns from ipchat.
	 *	Here, we'll just automatically recover.
	 * @return	array	If returns TRUE, upgrader will proceed to next step. If it returns any other value, it will set this as the value of the 'extra' GET parameter and rerun this step (useful for loops)
	 */
	public function step1()
	{
		if( !\IPS\Db::i()->checkForTable( 'chat_log_archive' ) )
		{
			\IPS\Application::load('chat')->installDatabaseSchema();
		}

		return TRUE;
	}

	/**
	 * Custom title for this step
	 *
	 * @return string
	 */
	public function step1CustomTitle()
	{
		return "Checking and fixing Chat database structure";
	}
}
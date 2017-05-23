<?php
/**
 * @brief		4.1.15 Upgrade Code
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Pages
 * @since		25 Aug 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\cms\setup\upg_101056;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * 4.1.15 Upgrade Code
 */
class _Upgrade
{
	/**
	 * Fix star ratings which haven't been averaged correctly.
	 *
	 * @return	array	If returns TRUE, upgrader will proceed to next step. If it returns any other value, it will set this as the value of the 'extra' GET parameter and rerun this step (useful for loops)
	 */
	public function finish()
	{
		\IPS\Db::i()->update( 'cms_databases', array( 'database_field_sort' => 'record_rating' ), array( 'database_field_sort=?', 'rating_real' ) );
		
		foreach( \IPS\Db::i()->select( '*', 'cms_databases' ) as $database )
		{
			try
			{
				\IPS\Task::queue( 'core', 'RecountStarRatings', array( 'class' => 'IPS\cms\Records' . $database['database_id'] ), 3 );
			}
			catch ( \OutOfRangeException $ex ) { }
		}

		return TRUE;
	}
}
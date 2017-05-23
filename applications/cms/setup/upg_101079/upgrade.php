<?php
/**
 * @brief		4.1.18 Beta 1 Upgrade Code
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Pages
 * @since		03 Jan 2017
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\cms\setup\upg_101079;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * 4.1.18 Beta 1 Upgrade Code
 */
class _Upgrade
{
	/**
	 * Reset category FURL paths
	 *
	 * @return	array	If returns TRUE, upgrader will proceed to next step. If it returns any other value, it will set this as the value of the 'extra' GET parameter and rerun this step (useful for loops)
	 */
	public function step1()
	{
		/* Make sure our Application.php has been loaded, as it handles the special dynamic CMS database classes */
		\IPS\Application::load( 'cms' );

		/* Loop over each database */
		foreach( \IPS\Db::i()->select( '*', 'cms_databases' ) as $database )
		{
			$categoryClass = '\IPS\cms\Categories' . $database['database_id'];
			
			/* Loop over root/parent categories in the database */
			foreach( $categoryClass::roots( NULL ) as $node )
			{
				/* Resetting the full path on the parent category automatically updates all children categories too */
				$node->setFullPath();
			}
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
		return "Resetting Pages database category friendly URLs";
	}
}
<?php
/**
 * @brief		4.1.18.1 Upgrade Code
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		20 Jan 2017
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\core\setup\upg_101088;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * 4.1.18.1 Upgrade Code
 */
class _Upgrade
{
	/**
	 * Convert custom profile field database schemas if needed
	 *
	 * @return	array	If returns TRUE, upgrader will proceed to next step. If it returns any other value, it will set this as the value of the 'extra' GET parameter and rerun this step (useful for loops)
	 */
	public function step1()
	{
		$queries	= array();

		foreach( \IPS\Db::i()->select( '*', 'core_pfields_data', array( "pf_type IN( 'Editor','Codemirror','TextArea','Upload','Address','Select' )" ) ) as $field )
		{
			if( \IPS\Db::i()->checkForIndex( 'core_pfields_content', 'field_' . $field['pf_id'] ) )
			{
				\IPS\Db::i()->returnQuery = TRUE;
				$queries[] = array( 'table' => 'core_pfields_content', 'query' => \IPS\Db::i()->dropIndex( 'core_pfields_content', 'field_' . $field['pf_id'] ) );
			}
			
			\IPS\Db::i()->returnQuery = TRUE;
			$queries[] = array( 'table' => 'core_pfields_content', 'query' => \IPS\Db::i()->changeColumn( 'core_pfields_content', 'field_' . $field['pf_id'], array( 'name' => 'field_' . $field['pf_id'], 'type' => 'MEDIUMTEXT' ) ) );
			
			\IPS\Db::i()->returnQuery = TRUE;
			$queries[] = array( 'table' => 'core_pfields_content', 'query' => \IPS\Db::i()->addIndex( 'core_pfields_content', array( 'type' => 'fulltext', 'name' => 'field_' . $field['pf_id'], 'columns' => array( 'field_' . $field['pf_id'] ) ) ) );
			\IPS\Db::i()->returnQuery = FALSE;
		}

		if( count( $queries ) )
		{
			$toRun = \IPS\core\Setup\Upgrade::runManualQueries( $queries );

			if ( count( $toRun ) )
			{
				\IPS\core\Setup\Upgrade::adjustMultipleRedirect( array( 1 => 'core', 'extra' => array( '_upgradeStep' => 2 ) ) );

				/* Queries to run manually */
				return array( 'html' => \IPS\Theme::i()->getTemplate( 'forms' )->queries( $toRun, \IPS\Http\Url::internal( 'controller=upgrade' )->setQueryString( array( 'key' => $_SESSION['uniqueKey'], 'mr_continue' => 1, 'mr' => \IPS\Request::i()->mr ) ) ) );
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
		return "Adjusting custom field definitions";
	}
}
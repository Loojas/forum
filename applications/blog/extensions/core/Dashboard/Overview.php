<?php
/**
 * @brief		Dashboard extension: Overview
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Blog
 * @since		20 Mar 2014
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\blog\extensions\core\Dashboard;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Dashboard extension: Overview
 */
class _Overview
{
	/**
	* Can the current user view this dashboard item?
	*
	* @return	bool
	*/
	public function canView()
	{
		return TRUE;
	}

	/** 
	 * Return the block HTML show on the dashboard
	 *
	 * @return	string
	 */
	public function getBlock()
	{
		/* Basic stats */
		$data = array(
			'total_blogs'		=> (int) \IPS\Db::i()->select( 'COUNT(*)', 'blog_blogs' )->first(),
			'total_entries'		=> (int) \IPS\Db::i()->select( 'COUNT(*)', 'blog_entries' )->first(),
			'total_comments'	=> (int) \IPS\Db::i()->select( 'COUNT(*)', 'blog_comments' )->first(),
		);
		
		/* Display */
		return \IPS\Theme::i()->getTemplate( 'dashboard', 'blog' )->overview( $data );
	}

	/** 
	 * Return the block information
	 *
	 * @return	array	array( 'name' => 'Block title', 'key' => 'unique_key', 'size' => [1,2,3], 'by' => 'Author name' )
	 */
	public function getInfo()
	{
		return array();
	}
}
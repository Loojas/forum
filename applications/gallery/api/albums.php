<?php
/**
 * @brief		Gallery Albums API
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Gallery
 * @since		14 Dec 2015
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\gallery\api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	Gallery Albums API
 */
class _albums extends \IPS\Api\Controller
{	
	/**
	 * GET /gallery/albums
	 * Get list of albums
	 *
	 * @apiparam	string	categories		Comma-delimited list of categiry IDs - if provided, only albums in those categories are returned
	 * @apiparam	string	owners			Comma-delimited list of member IDs - if provided, only albums owned by those members are returned
	 * @apiparam	string	sortBy			What to sort by. Can be 'name', 'count_images' for number of images, or do not specify for ID
	 * @apiparam	string	sortDir			Sort direction. Can be 'asc' or 'desc' - defaults to 'asc'
	 * @apiparam	int		page			Page number
	 * @return		\IPS\Api\PaginatedResponse<IPS\gallery\Album>
	 */
	public function GETindex()
	{
		/* Where clause */
		$where = array();
		
		/* Categories */
		if ( isset( \IPS\Request::i()->categories ) )
		{
			$where[] = array( \IPS\Db::i()->in( 'album_category_id', array_filter( explode( ',', \IPS\Request::i()->categories ) ) ) );
		}
		
		/* Owners */
		if ( isset( \IPS\Request::i()->owners ) )
		{
			$where[] = array( \IPS\Db::i()->in( 'album_owner_id', array_filter( explode( ',', \IPS\Request::i()->owners ) ) ) );
		}
		
		/* Privacy */
		if ( isset( \IPS\Request::i()->privacy ) )
		{
			$privacy = array();
			foreach ( array_filter( explode( ',', \IPS\Request::i()->privacy ) ) as $type )
			{
				switch ( $type )
				{
					case 'public':
						$privacy[] = \IPS\gallery\Album::AUTH_TYPE_PUBLIC;
						break;
					case 'private':
						$privacy[] = \IPS\gallery\Album::AUTH_TYPE_PRIVATE;
						break;
					case 'restricted':
						$privacy[] = \IPS\gallery\Album::AUTH_TYPE_RESTRICTED;
						break;
				}
			}
			
			$where[] = array( \IPS\Db::i()->in( 'album_type', $privacy ) );
		}
			
		/* Sort */
		if ( isset( \IPS\Request::i()->sortBy ) and in_array( \IPS\Request::i()->sortBy, array( 'name', 'count_images' ) ) )
		{
			$sortBy = 'album_' . \IPS\Request::i()->sortBy;
		}
		else
		{
			$sortBy = 'album_id';
		}
		$sortDir = ( isset( \IPS\Request::i()->sortDir ) and in_array( mb_strtolower( \IPS\Request::i()->sortDir ), array( 'asc', 'desc' ) ) ) ? \IPS\Request::i()->sortDir : 'asc';
		
		/* Return */
		return new \IPS\Api\PaginatedResponse(
			200,
			\IPS\Db::i()->select( '*', 'gallery_albums', $where, "{$sortBy} {$sortDir}" ),
			isset( \IPS\Request::i()->page ) ? \IPS\Request::i()->page : 1,
			'IPS\gallery\Album',
			\IPS\Db::i()->select( 'COUNT(*)', 'gallery_albums', $where )->first()
		);
	}
	
	/**
	 * GET /gallery/albums/{id}
	 * Get information about a specific album
	 *
	 * @param		int		$id			ID Number
	 * @throws		2G315/1	INVALID_ID	The album ID does not exist
	 * @return		\IPS\gallery\Album
	 */
	public function GETitem( $id )
	{
		try
		{			
			return new \IPS\Api\Response( 200, \IPS\gallery\Album::load( $id )->apiOutput() );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2G315/1', 404 );
		}
	}
}
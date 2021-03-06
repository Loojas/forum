<?php
/**
 * @brief		Converter Vanilla Class
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2015 Invision Power Services, Inc.
 * @package		IPS Social Suite
 * @subpackage	Converter
 * @since		21 Jan 2015
 * @version		
 */

namespace IPS\convert\Software\Forums;
use \IPS\convert\Software\Core\Vanilla as VanillaCore;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Vanilla extends \IPS\convert\Software
{
	/**
	 * Software Name
	 *
	 * @return	string
	 */
	public static function softwareName()
	{
		/* Child classes must override this method */
		return "Vanilla 2";
	}
	
	/**
	 * Software Key
	 *
	 * @return	string
	 */
	public static function softwareKey()
	{
		/* Child classes must override this method */
		return "vanilla";
	}

	/**
	 * Requires Parent
	 *
	 * @return	boolean
	 */
	public static function requiresParent()
	{
		return TRUE;
	}

	/**
	 * Possible Parent Conversions
	 *
	 * @return	array
	 */
	public static function parents()
	{
		return array( 'core' => array( 'vanilla' ) );
	}
	
	/**
	 * Content we can convert from this software. 
	 *
	 * @return	array
	 */
	public static function canConvert()
	{
		return array(
			'convertForumsForums' => array(
				'table'     => 'Category',
				'where'     => NULL
			),
			'convertForumsTopics' => array(
				'table'         => 'Discussion',
				'where'         => NULL
			),
			'convertForumsPosts' => array(
				'table'			=> 'Comment',
				'where'			=> NULL,
				'extra_steps'   => array( 'convertForumsPosts2' )
			),
			'convertForumsPosts2'  => array(
				'table'     => 'Comment',
				'where'     => NULL
			),
			'convertAttachments'	=> array(
				'table'		=> 'Media',
				'where'		=> array( 'ForeignTable=? OR ForeignTable=?', 'discussion', 'comment' )
			)
		);
	}

	/**
	 * Count Source Rows for a specific step
	 *
	 * @param	string		$table		The table containing the rows to count.
	 * @param	array|NULL	$where		WHERE clause to only count specific rows, or NULL to count all.
	 * @param	bool		$recache	Skip cache and pull directly (updating cache)
	 * @return	integer
	 * @throws	\IPS\convert\Exception
	 */
	public function countRows( $table, $where=NULL, $recache=FALSE )
	{
		switch( $table )
		{
			case 'Comment':
				$count = 0;
				$count += $this->db->select( 'COUNT(*)', 'Discussion' )->first();
				$count += $this->db->select( 'COUNT(*)', 'Comment' )->first();
				return $count;
				break;

			default:
				return parent::countRows( $table, $where, $recache );
				break;
		}
	}
	
	/**
	 * Can we convert passwords from this software.
	 *
	 * @return 	boolean
	 */
	public static function loginEnabled()
	{
		return TRUE;
	}

	/**
	 * List of conversion methods that require additional information
	 *
	 * @return	array
	 */
	public static function checkConf()
	{
		return array(
			'convertForums',
			'convertAttachments'
		);
	}

	/**
	 * Get More Information
	 *
	 * @param	string	$method	Conversion method
	 * @return	array
	 */
	public function getMoreInfo( $method )
	{
		$return = array();

		switch( $method )
		{
			case 'convertForums':
				$return['convertForums'] = array();

				/* Find out where the photos live */
				\IPS\Member::loggedIn()->language()->words['attach_location_desc'] = \IPS\Member::loggedIn()->language()->addToStack( 'attach_location' );
				$return['convertForums']['attach_location'] = array(
					'field_class'			=> 'IPS\\Helpers\\Form\\Text',
					'field_default'			=> NULL,
					'field_required'		=> TRUE,
					'field_extra'			=> array(),
					'field_hint'			=> \IPS\Member::loggedIn()->language()->addToStack('convert_vanilla_photopath'),
				);
				break;
			case 'convertAttachments':
				$return['convertAttachments'] = array(
					'attach_location'	=> array(
						'field_class'		=> 'IPS\\Helpers\\Form\\Text',
						'field_default'		=> NULL,
						'field_required'	=> TRUE,
						'field_extra'		=> array(),
						'field_hint'		=> \IPS\Member::loggedIn()->language()->addToStack('convert_vanilla_photopath'),
						'field_validation'	=> function( $value ) { if ( !@is_dir( $value ) ) { throw new \DomainException( 'path_invalid' ); } },
					),
				);
				break;
		}

		return ( isset( $return[ $method ] ) ) ? $return[ $method ] : array();
	}
	
	/**
	 * Finish - Adds everything it needs to the queues and clears data store
	 *
	 * @return	array		Messages to display
	 */
	public function finish()
	{
		/* Content Rebuilds */
		\IPS\Task::queue( 'core', 'RebuildContainerCounts', array( 'class' => 'IPS\forums\Forum', 'count' => 0 ), 5, array( 'class' ) );
		\IPS\Task::queue( 'convert', 'RebuildContent', array( 'app' => $this->app->app_id, 'link' => 'forums_posts', 'class' => 'IPS\forums\Topic\Post' ), 2, array( 'app', 'link', 'class' ) );
		\IPS\Task::queue( 'core', 'RebuildItemCounts', array( 'class' => 'IPS\forums\Topic' ), 3, array( 'class' ) );
		\IPS\Task::queue( 'convert', 'RebuildFirstPostIds', array( 'app' => $this->app->app_id ), 2, array( 'app' ) );
		\IPS\Task::queue( 'convert', 'DeleteEmptyTopics', array( 'app' => $this->app->app_id ), 4, array( 'app' ) );

		/* Caches */
		\IPS\Task::queue( 'convert', 'RebuildTagCache', array( 'app' => $this->app->app_id, 'link' => 'forums_topics', 'class' => 'IPS\forums\Topic' ), 3, array( 'app', 'link', 'class' ) );

		return array( "f_forum_last_post_data", "f_rebuild_posts", "f_recounting_forums", "f_recounting_topics", "f_topic_tags_recount" );
	}
	
	/**
	 * Fix post data
	 *
	 * @param 	string		raw post data
	 * @return 	string		parsed post data
	 */
	public static function fixPostData( $post )
	{
		/**
		 * Vanilla actually only supports plaintext posts out of the box, but there is a popular quotes plugin I think
		 * we should accommodate for.
		 */
		$quoted = preg_replace_callback( '#<blockquote class="Quote" rel="([^"]+)">(.+)</blockquote>#', function( $match )
		{
			$match[1] = str_replace( '"', '', $match[1] );
			return "[quote name=\"{$match[1]}\"]{$match[2]}[/quote]";
		}, $post );

		return $quoted ?: $post;
	}

	/**
	 * Convert attachments
	 *
	 * @return	void
	 */
	public function convertAttachments()
	{
		$libraryClass = $this->getLibrary();

		$libraryClass::setKey( 'MediaID' );

		foreach( $this->fetch( 'Media', 'MediaID', array( 'ForeignTable=? OR ForeignTable=?', 'discussion', 'comment' ) ) AS $row )
		{
			if( $row['ForeignTable'] == 'discussion' )
			{
				$map = array(
					'id1'	=> 'fp-' . $row['ForeignID'],
					'id2'	=> $row['ForeignID'],
				);
			}
			else
			{
				try
				{
					$discussionId = $this->db->select( 'DiscussionID', 'Comment', array( 'CommentID=?', $row['ForeignID'] ) )->first();
				}
				catch( \UnderflowException $ex )
				{
					$libraryClass->setLastKeyValue( $row['MediaID'] );
				}

				$map = array(
					'id1'	=> $discussionId,
					'id2'	=> $row['ForeignID'],
				);
			}

			/* File extension */
			$ext = explode( '.', $row['Path'] );
			$ext = array_pop( $ext );

			$info = array(
				'attach_id'			=> $row['MediaID'],
				'attach_file'		=> $row['Name'],
				'attach_date'		=> VanillaCore::mysqlToDateTime( $row['DateInserted'] ),
				'attach_member_id'	=> $row['InsertUserID'],
				'attach_hits'		=> 0,
				'attach_ext'		=> $ext,
				'attach_filesize'	=> $row['Size'],
			);

			$libraryClass->convertAttachment( $info, $map, rtrim( $this->app->_session['more_info']['convertAttachments']['attach_location'], '/' ) . '/' . trim( $row['Path'], '/' ) );
			$libraryClass->setLastKeyValue( $row['MediaID'] );
		}
	}

	/**
	 * Convert forums
	 *
	 * @return	void
	 */
	public function convertForumsForums()
	{
		$libraryClass = $this->getLibrary();
		$libraryClass::setKey( 'c.CategoryID' );

		$uploadsPath = $this->app->_session['more_info']['convertAttachments']['attach_location'];

		$forums = $this->fetch( array( 'Category', 'c' ), 'CategoryID', array( 'c.CategoryID<>?', -1 ), 
			'c.*, lcu.UserID as LastCommentUserID, lcu.Name as LastCommentUserName, ld.Name as LastDiscussionName' 
		);
		$forums->join( array( 'User', 'lcu' ), 'c.LastCommentID=lcu.UserID' );
		$forums->join( array( 'Discussion', 'ld' ), 'c.LastDiscussionID=ld.DiscussionID' );

		foreach( $forums AS $row )
		{
			$icon = $row['Icon'] ? VanillaCore::parseMediaLocation( $row['Icon'], $uploadsPath ) : NULL;
			$info = [
				'id'                => $row['CategoryID'],
				'name'              => $row['Name'],
				'description'       => $row['Description'],
				'topics'            => $row['CountDiscussions'],
				'posts'             => $row['CountComments'],
				'last_post'         => VanillaCore::mysqlToDateTime( $row['LastDateInserted'] ),
				'last_poster_id'    => $row['LastCommentID'],
				'last_poster_name'  => $row['LastCommentUserName'],
				'parent_id'         => ( (int) $row['ParentCategoryID'] > 0 ) ? $row['ParentCategoryID'] : NULL,
				'position'          => $row['Sort'],
				'last_title'        => $row['LastDiscussionName'],
				'icon'              => $icon,
				'sub_can_post'		=> $row['AllowDiscussions'] ?: 0
			];

			$libraryClass->convertForumsForum( $info, NULL, $icon );
			$libraryClass->setLastKeyValue( $row['CategoryID'] );
		}
	}

	/**
	 * Convert topics
	 *
	 * @return	void
	 */
	public function convertForumsTopics()
	{
		$libraryClass = $this->getLibrary();
		$libraryClass::setKey( 'd.DiscussionID' );

		$discussions = $this->fetch( array( 'Discussion', 'd' ), 'DiscussionID', NULL, 
			'd.*, u.Name as UserName, lcu.UserID as LastCommentUserID, lcu.Name as LastCommentUserName'
		);
		$discussions->join( array( 'User', 'u' ), 'd.InsertUserID=u.UserID' );
		$discussions->join( array( 'User', 'lcu' ), 'd.LastCommentUserID=lcu.UserID' );

		foreach( $discussions AS $row )
		{
			/* If last post info is empty, fetch it */
			if( $row['DateLastComment'] === NULL )
			{
				$data = $this->db->select( 'Comment.InsertUserID, Comment.DateInserted, User.Name', 'Comment', array( 'DiscussionID=?', $row['DiscussionID'] ), 'CommentID DESC', array( 0, 1 ) )
							->join( 'User', 'Comment.InsertUserID=User.UserID' )
							->first();

				$row['DateLastComment'] = $data['DateInserted'];
				$row['LastCommentUserId'] = $data['InsertUserId'];
				$row['LastCommentUserName'] = $data['Name'];
			}

			$info = array(
				'tid'               => $row['DiscussionID'],
				'title'				=> $row['Name'],
				'forum_id'			=> $row['CategoryID'],
				'state'				=> ( $row['Closed'] == 0 ) ? 'open' : 'closed',
				'posts'				=> $row['CountComments'],
				'starter_id'		=> $row['InsertUserID'],
				'start_date'		=> VanillaCore::mysqlToDateTime( $row['DateInserted'] ),
				'last_poster_id'	=> $row['LastCommentUserId'],
				'last_post'			=> VanillaCore::mysqlToDateTime( $row['DateLastComment'] ),
				'starter_name'		=> $row['UserName'],
				'last_poster_name'	=> $row['LastCommentUserName'],
				'views'				=> $row['CountViews'],
			);

			$libraryClass->convertForumsTopic( $info );

			/* Tags */
			if( !empty( $row['Tags'] ) )
			{
				$tags = explode( ',', $row['Tags'] );

				if ( count( $tags ) )
				{
					foreach( $tags AS $tag )
					{
						$toConvert = explode( ' ', $tag );
						foreach( $toConvert as $spacedTag )
						{
							$libraryClass->convertTag( array(
								'tag_meta_app'			=> 'forums',
								'tag_meta_area'			=> 'forums',
								'tag_meta_parent_id'	=> $row['CategoryID'],
								'tag_meta_id'			=> $row['DiscussionID'],
								'tag_text'				=> $spacedTag,
								'tag_member_id'			=> $row['InsertUserID'],
								'tag_prefix'			=> 0,
							) );
						}
					}
				}
			}

			$libraryClass->setLastKeyValue( $row['DiscussionID'] );
		}
	}

	/**
	 * Convert posts
	 *
	 * @return	void
	 */
	public function convertForumsPosts()
	{
		$libraryClass = $this->getLibrary();
		$libraryClass::setKey( 'DiscussionID' );

		foreach( $this->fetch( 'Discussion', 'DiscussionID' ) AS $row )
		{
			$editName = NULL;

			if( $row['UpdateUserID'] )
			{
				try
				{
					$editName = $this->db->select( 'Name', 'User', array( 'UserID=?', $row['UpdateUserID'] ) )->first();
				}
				catch( \UnderflowException $e ) {}
			}

			// First post
			$info = array(
				'pid'           => 'fp-' . $row['DiscussionID'],
				'topic_id'      => $row['DiscussionID'],
				'post'          => $row['Body'],
				'new_topic'     => 1,
				'edit_time'     => ( $editName === NULL ) ? NULL : VanillaCore::mysqlToDateTime( $row['DateUpdated'] ),
				'edit_name'		=> $editName,
				'author_id'     => $row['InsertUserID'],
				'ip_address'    => $row['InsertIPAddress'],
				'post_date'     => VanillaCore::mysqlToDateTime( $row['DateInserted'] ),
			);

			$libraryClass->convertForumsPost( $info );
			$libraryClass->setLastKeyValue( $row['DiscussionID'] );
		}
	}

	/**
	 * Convert other posts
	 *
	 * @return	void
	 */
	public function convertForumsPosts2()
	{
		$libraryClass = $this->getLibrary();
		$libraryClass::setKey( 'CommentID' );

		foreach( $this->fetch( 'Comment', 'CommentID' ) AS $row )
		{
			$editName = NULL;

			if( $row['UpdateUserID'] )
			{
				try
				{
					$editName = $this->db->select( 'Name', 'User', array( 'UserID=?', $row['UpdateUserID'] ) )->first();
				}
				catch( \UnderflowException $e ) {}
			}

			$info = [
				'pid'        => $row['CommentID'],
				'topic_id'   => $row['DiscussionID'],
				'post'       => $row['Body'],
				'edit_time'  => ( $editName === NULL ) ? NULL : VanillaCore::mysqlToDateTime( $row['DateUpdated'] ),
				'edit_name'	 => $editName,
				'author_id'  => $row['InsertUserID'],
				'ip_address' => $row['InsertIPAddress'],
				'post_date'  => VanillaCore::mysqlToDateTime( $row['DateInserted'] ),
			];

			$libraryClass->convertForumsPost( $info );
			$libraryClass->setLastKeyValue( $row['CommentID'] );
		}
	}

	/**
	 * Check if we can redirect the legacy URLs from this software to the new locations
	 *
	 * @return	NULL|\IPS\Http\Url
	 * @note	Forums and profiles don't use an ID in the URL. While we may be able to somehow cross reference this with our SEO slug, it wouldn't be reliable.
	 */
	public function checkRedirects()
	{
		$url = \IPS\Request::i()->url();

		if( preg_match( '#/discussion/([0-9]+)/#i', $url->data[ \IPS\Http\Url::COMPONENT_PATH ], $matches ) )
		{
			try
			{
				try
				{
					$data = (string) $this->app->getLink( (int) $matches[1], array( 'topics', 'forums_topics' ) );
				}
				catch( \OutOfRangeException $e )
				{
					$data = (string) $this->app->getLink( (int) $matches[1], array( 'topics', 'forums_topics' ), FALSE, TRUE );
				}
				$item = \IPS\forums\Topic::load( $data );

				if( $item->canView() )
				{
					return $item->url();
				}
			}
			catch( \Exception $e )
			{
				return NULL;
			}
		}

		return NULL;
	}
}
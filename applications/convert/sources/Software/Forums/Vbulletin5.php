<?php

/**
 * @brief		Converter vBulletin 5.x Forums Class
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @package		IPS Community Suite
 * @subpackage	Converter
 * @since		21 Jan 2015
 */

namespace IPS\convert\Software\Forums;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Vbulletin5 extends \IPS\convert\Software
{
	/**
	 * Software Name
	 *
	 * @return	string
	 */
	public static function softwareName()
	{
		/* Child classes must override this method */
		return "vBulletin Forums (5.x)";
	}
	
	/**
	 * Software Key
	 *
	 * @return	string
	 */
	public static function softwareKey()
	{
		/* Child classes must override this method */
		return "vbulletin5";
	}
	
	/**
	 * Content we can convert from this software. 
	 *
	 * @return	array
	 */
	public static function canConvert()
	{
		return array(
			'convertForumsForums'	=> array(
				'table'		=> 'forum',
				'where'		=> NULL,
			),
			'convertForumsTopics'	=> array(
				'table'		=> 'thread',
				'where'		=> NULL,
			),
			'convertForumsPosts'	=> array(
				'table'		=> 'post',
				'where'		=> NULL,
			),
			'convertAttachments'	=> array(
				'table'		=> 'attachment',
				'where'		=> NULL
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
			case 'forum':
				return count( $this->fetchForums() );
				break;
			
			case 'thread':
				return parent::countRows( 'node', array( \IPS\Db::i()->in( 'contenttypeid', array( $this->fetchType( 'Text' ), $this->fetchType( 'Poll' ), $this->fetchType( 'Gallery' ), $this->fetchType( 'Video' ), $this->fetchType( 'Link' ) ) ) . " AND " . \IPS\Db::i()->in( 'parentid', array_keys( $this->fetchForums() ) ), $this->fetchType( 'Text' ), $this->fetchType( 'Poll' ) ) );
				break;
			
			case 'post':
				return parent::countRows( 'node', array( \IPS\Db::i()->in( 'contenttypeid', array( $this->fetchType( 'Text' ), $this->fetchType( 'Gallery' ), $this->fetchType( 'Video' ), $this->fetchType( 'Link' ) ) ) ) );
				break;
			
			case 'attachment':
				return parent::countRows( 'node', array( "contenttypeid=?", $this->fetchType( 'Attach' ) ) );
				break;
			
			default:
				return parent::countRows( $table, $where, $recache );
				break;
		}
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
		return array( 'core' => array( 'vbulletin5' ) );
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
		\IPS\Task::queue( 'convert', 'RebuildFirstPostIds', array( 'app' => $this->app->app_id ), 2, array( 'app' ) );
		\IPS\Task::queue( 'convert', 'DeleteEmptyTopics', array( 'app' => $this->app->app_id ), 4, array( 'app' ) );
		
		return array( 'f_rebuild_posts', 'f_recounting_forums', 'f_recounting_topics' );
	}
	
	/**
	 * Fix Post Data
	 *
	 * @param	string	Post
	 * @return	string	Fixed Posts
	 */
	public static function fixPostData( $post )
	{
		return \IPS\convert\Software\Core\Vbulletin5::fixPostData( $post );
	}

	/**
	 * List of conversion methods that require additional information
	 *
	 * @return	array
	 */
	public static function checkConf()
	{
		return array( 'convertAttachments' );
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
			case 'convertAttachments':
				$return['convertAttachments'] = array(
					'file_location' => array(
						'field_class'			=> 'IPS\\Helpers\\Form\\Radio',
						'field_default'			=> 'database',
						'field_required'		=> TRUE,
						'field_extra'			=> array(
							'options'				=> array(
								'database'				=> \IPS\Member::loggedIn()->language()->addToStack( 'database' ),
								'file_system'			=> \IPS\Member::loggedIn()->language()->addToStack( 'file_system' ),
							),
							'userSuppliedInput'	=> 'file_system',
						),
						'field_hint'			=> NULL,
						'field_validation'		=> function( $value ) { if ( !@is_dir( $value ) ) { throw new \DomainException( 'path_invalid' ); } },
					)
				);
				break;
		}
		
		return ( isset( $return[ $method ] ) ) ? $return[ $method ] : array();
	}

	/**
	 * Convert forums
	 *
	 * @return	void
	 */
	public function convertForumsForums()
	{
		$libraryClass = $this->getLibrary();
		
		$libraryClass::setKey( 'node.nodeid' );
		
		foreach( $this->fetch( 'node', 'nodeid', array( "node.nodeid<>? AND closure.parent=? AND node.contenttypeid=?", 2, 2, $this->fetchType( 'Channel' ) ) )->join( 'closure', "closure.child=node.nodeid" ) AS $forum )
		{
			$self = $this;
			$checkpermission = function( $name, $perm ) use ( $forum, $self )
			{
				$key = $name;
				if ( $name == 'forumoptions' )
				{
					$key = 'nodeoptions';
				}
				
				if ( $forum[$key] & $self::$bitOptions[$name][$perm] )
				{
					return TRUE;
				}
				
				return FALSE;
			};
			
			$info = array(
				'id'					=> $forum['nodeid'],
				'name'					=> $forum['title'],
				'description'			=> $forum['description'],
				'topics'				=> $forum['textcount'],
				'posts'					=> $forum['totalcount'],
				'last_post'				=> $forum['lastcontent'],
				'last_poster_id'		=> $forum['lastauthorid'],
				'last_poster_name'		=> $forum['lastcontentauthor'],
				'parent_id'				=> $forum['parentid'],
				'position'				=> $forum['displayorder'],
				'preview_posts'			=> $checkpermission( 'forumoptions', 'moderatenewpost' ),
				'inc_postcount'			=> 1,
				'forum_allow_rating'	=> 1,
				'sub_can_post'			=> ( $forum['parentid'] == 2 ) ? 0 : 1,
			);
			
			$libraryClass->convertForumsForum( $info );
			
			$libraryClass->setLastKeyValue( $forum['nodeid'] );
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
		
		$libraryClass::setKey( 'node.nodeid' );
		
		foreach( $this->fetch( 'node', 'node.nodeid', array( \IPS\Db::i()->in( 'node.parentid', array_keys( $this->fetchForums() ) ) . " AND " . \IPS\Db::i()->in( 'node.contenttypeid', array( $this->fetchType( 'Text' ), $this->fetchType( 'Poll' ), $this->fetchType( 'Gallery' ), $this->fetchType( 'Video' ), $this->fetchType( 'Link' ) ) ) ) )->join( 'text', 'text.nodeid = node.nodeid' ) AS $topic )
		{
			/* Pesky Polls */
			$poll		= NULL;
			$lastVote	= 0;
			try
			{
				/* Does this topic have a corresponding poll? */
				$pollData		= $this->db->select( '*', 'poll', array( "nodeid=?", $topic['nodeid'] ) )->first();
				$pollOptions	= @\unserialize( $pollData['options'] );
				$lastVote		= $pollData['lastvote'];
				
				if ( $pollOptions === FALSE )
				{
					throw new \UnexpectedValueException;
				}
				
				$choices	= array();
				$numvotes	= 0;
				$votes		= array();
				foreach( $pollOptions AS $option )
				{
					$choices[$option['polloptionid']]	= $option['title'];
					$votes[$option['polloptionid']]		= $option['votes'];
					$numvotes							+= $option['votes'];
				}
				
				$poll = array();
				$poll['poll_data'] = array(
					'pid'				=> $pollData['nodeid'],
					'choices'			=> array( 1 => array(
						'question'			=> $topic['title'],
						'multi'				=> $pollData['multiple'],
						'choice'			=> $choices,
						'votes'				=> $votes
					) ),
					'poll_question'		=> $topic['title'],
					'start_date'		=> $topic['publishdate'],
					'starter_id'		=> $topic['userid'],
					'votes'				=> $numvotes,
					'poll_view_voters'	=> $pollData['public']
				);
				
				$poll['vote_data'] = array();
				$ourVotes = array();
				foreach( $this->db->select( '*', 'pollvote', array( "nodeid=?", $pollData['nodeid'] ) ) AS $vote )
				{
					if ( !isset( $ourVotes[$vote['userid']] ) )
					{
						/* Create our structure - vB stores each individual vote as a different row whereas we combine them per user */
						$ourVotes[$vote['userid']] = array( 'votes' => array() );
					}
					
					$ourVotes[$vote['userid']]['votes'][]		= $vote['voteoption'];
					
					/* These don't matter - just use the latest one */
					$ourVotes[$vote['userid']]['vid']			= $vote['pollvoteid'];
					$ourVotes[$vote['userid']]['vote_date'] 	= $vote['votedate'];
					$ourVotes[$vote['userid']]['member_id']		= $vote['userid'];
				}
				
				/* Now we need to re-wrap it all for storage */
				foreach( $ourVotes AS $member_id => $vote )
				{
					$poll['vote_data'][$member_id] = array(
						'vid'				=> $vote['vid'],
						'vote_date'			=> $vote['vote_date'],
						'member_id'			=> $vote['member_id'],
						'member_choices'	=> array( 1 => $vote['votes'] ),
					);
				}
			}
			catch( \UnderflowException $e ) {} # if the poll is missing, don't bother
			catch( \UnexpectedValueException $e ) {} # if poll data is corrupt, then skip
			
			$info = array(
				'tid'				=> $topic['nodeid'],
				'title'				=> $topic['title'],
				'forum_id'			=> $topic['parentid'],
				'state'				=> $topic['open'] ? 'open' : 'closed',
				'posts'				=> $topic['totalcount'],
				'starter_id'		=> $topic['userid'],
				'start_date'		=> $topic['publishdate'],
				'last_poster_id'	=> $topic['lastauthorid'],
				'last_post'			=> $topic['lastcontent'],
				'starter_name'		=> $topic['authorname'],
				'last_poster_name'	=> $topic['lastcontentauthor'],
				'poll_state'		=> $poll,
				'last_vote'			=> $lastVote,
				'approved'			=> $topic['approved'],
				'pinned'			=> $topic['sticky'],
				'topic_open_time'	=> $topic['publishdate'],
				'topic_hiddenposts'	=> $topic['totalunpubcount']
			);
			
			unset( $poll );
			
			$topic_id = $libraryClass->convertForumsTopic( $info );
			
			$libraryClass->setLastKeyValue( $topic['nodeid'] );
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
		
		$libraryClass::setKey( 'node.nodeid' );
		
		foreach( $this->fetch( 'node', 'node.nodeid', array( \IPS\Db::i()->in( 'node.contenttypeid', array( $this->fetchType( 'Text' ), $this->fetchType( 'Gallery' ), $this->fetchType( 'Video' ), $this->fetchType( 'Link' ), $this->fetchType( 'Poll' ) ) ) ) )->join( 'text', 'text.nodeid = node.nodeid' ) AS $post )
		{
			/* Comments of comments of comments of comments */
			$isSubComment = FALSE;
			try
			{
				/* Is this the first post? */
				$this->app->getLink( $post['parentid'], 'forums_forums' );
				
				$post['parentid'] = $post['nodeid'];
			}
			catch( \OutOfRangeException $e )
			{
				try
				{
					/* If parentid exists as a forums_topics link, we don't actually need to do anything */
					$this->app->getLink( $post['parentid'], 'forums_topics' );
				}
				catch( \OutOfRangeException $e )
				{
					try
					{
						$this->app->getLink( $post['parentid'], 'forums_posts' );
						
						$isSubComment = TRUE;
						
						/* Load the parent */
						$parent = $this->db->select( '*', 'node', array( "node.nodeid=?", $post['parentid'] ) )->join( 'text', 'text.nodeid = node.nodeid' )->first();
						
						$post['approved']	= $parent['approved'];
						$post['parentid']	= $parent['parentid'];
						$post['rawtext']	= "[quote name='" . $parent['authorname'] ."' timestamp='" . $parent['created'] . "']" . $parent['rawtext'] . "[/quote]\n" . $post['rawtext'];
					}
					catch( \OutOfRangeException $e ) # we need to go deeper!
					{
						/* Actually, we don't - end of the line */
						$libraryClass->setLastKeyValue( $post['nodeid'] );
						continue;
					}
					catch( \UnderflowException $e )
					{
						/* Loading the parent failed - move on */
						$libraryClass->setLastKeyValue( $post['nodeid'] );
						continue;
					}
				}
			}
			$info = array(
				'pid'				=> $post['nodeid'],
				'topic_id'			=> $post['parentid'],
				'post'				=> $post['rawtext'],
				'author_id'			=> $post['userid'],
				'author_name'		=> $post['authorname'],
				'ip_address'		=> $post['ipaddress'],
				'post_date'			=> $post['publishdate'],
				'queued'			=> $post['approved'] ? 0 : -1,
				'post_htmlstate'	=> ( in_array( $post['htmlstate'], array( 'on', 'on_nl2br' ) ) ) ? 1 : 0,
			);
			
			$post_id = $libraryClass->convertForumsPost( $info );
			
			/* Reputation */
			foreach( $this->db->select( '*', 'reputation', array( "nodeid=?", $post['nodeid'] ) ) AS $rep )
			{
				$libraryClass->convertReputation( array(
					'id'				=> $rep['reputationid'],
					'app'				=> 'forums',
					'type'				=> 'pid',
					'type_id'			=> $post['nodeid'],
					'member_id'			=> $rep['whoadded'],
					'member_received'	=> $rep['userid'],
					'rep_date'			=> $rep['dateline'],
					'rep_rating'		=> $rep['reputation']
				) );
			}
			
			/* Edit History */
			$latestedit = 0;
			$reason		= NULL;
			$name		= NULL;
			foreach( $this->db->select( '*', 'postedithistory', array( "nodeid=?", $post['nodeid'] ) ) AS $edit )
			{
				$libraryClass->convertEditHistory( array(
					'id'			=> $edit['postedithistoryid'],
					'class'			=> 'IPS\\forums\\Topic\\Post',
					'comment_id'	=> $post['nodeid'],
					'member'		=> $edit['userid'],
					'time'			=> $edit['dateline'],
					'old'			=> $edit['pagetext']
				) );
				
				if ( $edit['dateline'] > $latestedit )
				{
					$latestedit = $edit['dateline'];
					$reason		= $edit['reason'];
					$name		= $edit['username'];
				}
			}
			
			/* Warnings */
			foreach( $this->db->select( '*', 'infraction', array( "nodeid=?", $post['nodeid'] ) ) AS $warn )
			{
				$libraryClass->convertWarnLog( array(
					'wl_id'					=> $warn['infractionid'],
					'wl_member'				=> $warn['nodeid'],
					'wl_moderator'			=> $warn['whoadded'],
					'wl_date'				=> $warn['dateline'],
					'wl_points'				=> $warn['points'],
					'wl_note_member'		=> $warn['note'],
					'wl_note_mods'			=> $warn['customreason'],
				) );
			}
			
			/* If we have a latest edit, then update the main post - this should really be in the library, as the converters should not be altering data */
			if ( $latestedit )
			{
				\IPS\Db::i()->update( 'forums_posts', array( 'append_edit' => 1, 'edit_time' => $latestedit, 'edit_name' => $name, 'post_edit_reason' => $reason ), array( "pid=?", $post_id ) );
			}
			
			$libraryClass->setLastKeyValue( $post['nodeid'] );
		}
	}

	/**
	 * Convert attachments
	 *
	 * @return	void
	 */
	public function convertAttachments()
	{
		$libraryClass = $this->getLibrary();
		
		$libraryClass::setKey( 'node.nodeid' );
		
		$main = $this->fetch( 'node', 'node.nodeid', array( "node.contenttypeid=?", $this->fetchType( 'Attach' ) ) )
			->join( 'attach', 'attach.nodeid = node.nodeid' )
			->join( 'filedata', 'filedata.filedataid = attach.filedataid' );
		
		foreach( $main AS $attachment )
		{
			try
			{
				$this->app->getLink( $attachment['parentid'], 'forums_topics' );
				$post_id = $attachment['nodeid'];
			}
			catch( \OutOfRangeException $e )
			{
				try
				{
					$this->app->getLink( $attachment['parentid'], 'forums_posts' );
					
					$parent					= $this->db->select( 'nodeid, parentid', 'node', array( "nodeid=?", $attachment['parentid'] ) )->first();
					$attachment['parentid']	= $parent['parentid'];
					$post_id				= $parent['nodeid'];
				}
				catch( \OutOfRangeException $e )
				{
					$libraryClass->setLastKeyValue( $attachment['nodeid'] );
					continue;
				}
				catch( \UnderflowException $e )
				{
					$libraryClass->setLastKeyValue( $attachment['nodeid'] );
					continue;
				}
			}
			
			$map = array(
				'id1'		=> $attachment['parentid'],
				'id2'		=> $post_id
			);
			
			$info = array(
				'attach_id'			=> $attachment['nodeid'],
				'attach_file'		=> $attachment['filename'],
				'attach_date'		=> $attachment['dateline'],
				'attach_member_id'	=> $attachment['userid'],
				'attach_hits'		=> $attachment['counter'],
				'attach_ext'		=> $attachment['extension'],
				'attach_filesize'	=> $attachment['filesize'],
			);
			
			if ( $this->app->_session['more_info']['convertAttachments']['file_location'] == 'database' )
			{
				/* Simples! */
				$data = $attachment['filedata'];
				$path = NULL;
			}
			else
			{
				$data = NULL;
				$path = implode( '/', preg_split( '//', $attachment['userid'], -1, PREG_SPLIT_NO_EMPTY ) );
				$path = rtrim( $this->app->_session['more_info']['convertAttachments']['file_location'], '/' ) . '/' . $path . '/' . $attachment['filedataid'] . '.attach';
			}
			
			$attach_id = $libraryClass->convertAttachment( $info, $map, $path, $data );
			
			/* Do some re-jiggery on the post itself to make sure attachment displays */
			if ( $attach_id !== FALSE )
			{
				try
				{
					$pid = $this->app->getLink( $post_id, 'forums_posts' );
					
					$post = \IPS\Db::i()->select( 'post', 'forums_posts', array( "pid=?", $pid ) )->first();
				}
				catch( \OutOfRangeException $e )
				{
					/* If the post didn't exist and this is just an orphaned attachment, move along */
					$libraryClass->setLastKeyValue( $attachment['nodeid'] );
					continue;
				}
				
				if ( preg_match( "/\[ATTACH=(.+?)?\]n" . $attachment['nodeid'] . "\[\/ATTACH\]/i", $post ) )
				{
					$post = preg_replace( "/\[ATTACH(.+?)?\]" . $attachment['nodeid'] . "\[\/ATTACH\]/i", '[attachment=' . $attach_id . ':name]', $post );

					\IPS\Db::i()->update( 'forums_posts', array( 'post' => $post ), array( "pid=?", $pid ) );
				}
				else
				{
					$oldAttachId = NULL;

					/* Try old attach ID */
					try
					{
						$oldAttachId = $this->db->select( 'attachmentid', 'attachment', array( 'filedataid=?', $attachment['filedataid'] ) )->first();
					}
					/* Don't fail if this table/value doesn't exist */
					catch( \Exception $e ) { }

					if ( $oldAttachId !== NULL and preg_match( "/\[ATTACH=(.+?)?\]" . $oldAttachId . "\[\/ATTACH\]/i", $post ) )
					{
						$post = preg_replace( "/\[ATTACH(.+?)?\]" . $oldAttachId . "\[\/ATTACH\]/i", '[attachment=' . $attach_id . ':name]', $post );

						\IPS\Db::i()->update( 'forums_posts', array( 'post' => $post ), array( "pid=?", $pid ) );
					}
				}
			}
			
			$libraryClass->setLastKeyValue( $attachment['nodeid'] );
		}
	}
	
	/* !vBulletin Stuff */
	
	/**
	 * @brief	Silly Bitwise
	 */
	public static $bitOptions = array (
		'forumoptions' => array(
			'active' => 1,
			'allowposting' => 2,
			'cancontainthreads' => 4,
			'moderatenewpost' => 8,
			'moderatenewthread' => 16,
			'moderateattach' => 32,
			'allowbbcode' => 64,
			'allowimages' => 128,
			'allowhtml' => 256,
			'allowsmilies' => 512,
			'allowicons' => 1024,
			'allowratings' => 2048,
			'countposts' => 4096,
			'canhavepassword' => 8192,
			'indexposts' => 16384,
			'styleoverride' => 32768,
			'showonforumjump' => 65536,
			'prefixrequired' => 131072,
			'allowvideos' => 262144,
			'bypassdp' => 524288,
			'displaywrt' => 1048576,
			'canreputation' => 2097152,
		),
	);
	
	/**
	 * Helper method to retrieve forums from nodes
	 *
	 * @return void
	 */
	protected function fetchForums()
	{
		$forums = array();

		foreach( $this->db->select( '*', 'node', array( "node.nodeid<>2 AND closure.parent=? AND node.contenttypeid=?", $this->fetchType( 'Thread' ), $this->fetchType( 'Channel' ) ) )->join( 'closure', "closure.child = node.nodeid" ) AS $node )
		{
			$forums[$node['nodeid']] = $node;
		}

		return $forums;
	}
	
	/**
	 * @brief	Types Cache
	 */
	protected $typesCache = array();

	/**
	 * Helper method to retrieve content type ids
	 *
	 * @return void
	 */
	protected function fetchType( $type )
	{
		if ( count( $this->typesCache ) )
		{
			return $this->typesCache[ $type ];
		}
		else
		{
			foreach( $this->db->select( '*', 'contenttype' ) AS $contenttype )
			{
				$this->typesCache[ $contenttype['class'] ] = $contenttype['contenttypeid'];
			}

			return $this->typesCache[ $type ];
		}
	}

	/**
	 * Check if we can redirect the legacy URLs from this software to the new locations
	 *
	 * @return	NULL|\IPS\Http\Url
	 * @note	Forum URLs do not have IDs in them, so we cannot redirect them reliably
	 */
	public function checkRedirects()
	{
		$url = \IPS\Request::i()->url();

		if( preg_match( '#(?<!topic)/([0-9]+)\-([^/]*?)#', $url->data[ \IPS\Http\Url::COMPONENT_PATH ], $matches ) )
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
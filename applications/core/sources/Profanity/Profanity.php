<?php
/**
 * @brief		Profanity Model
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		10 Nov 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\core;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Profanity Model
 */
class _Profanity extends \IPS\Patterns\ActiveRecord implements \JsonSerializable
{
	/**
	 * @brief	Database Table
	 */
	public static $databaseTable = 'core_profanity_filters';
	
	/**
	 * @brief	Database Prefix
	 */
	public static $databasePrefix = '';
	
	/**
	 * @brief	Multiton Store
	 */
	protected static $multitons;
		
	/**
	 * @brief	[ActiveRecord] ID Database Column
	 */
	public static $databaseColumnId = 'wid';
	
	/**
	 * @brief	[ActiveRecord] Database ID Fields
	 */
	protected static $databaseIdFields = array( 'type' );
	
	/**
	 * @brief	[ActiveRecord] Multiton Map
	 */
	protected static $multitonMap	= array();
	
	/**
	 * @brief	Action Type
	 */
	public static $actionTypes = array( 'swap', 'moderate' );
	
	/**
	 * Table
	 *
	 * @return	\IPS\Helpers\Table
	 */
	public static function table()
	{
		$table = new \IPS\Helpers\Table\Db( 'core_profanity_filters', \IPS\Http\Url::internal( 'app=core&module=settings&controller=posting&tab=profanityFilters' ) );
		$table->langPrefix = 'profanity_';
		$table->mainColumn = 'type';
		
		/* Columns we need */
		$table->include = array( 'type', 'action', 'm_exact' );

		/* Default sort options */
		$table->sortBy = $table->sortBy ?: 'type';
		$table->sortDirection = $table->sortDirection ?: 'desc';
		
		/* Filters */
		$table->filters = array(
			'profanity_require_approval'	=> "action='moderate'",
			'profanity_replace_text'		=> "action='swap'",
		);
		
		/* Search */
		$table->quickSearch = 'type';
		
		/* Custom parsers */
		$table->parsers = array(
			'action'				=> function( $val, $row )
			{
				if ( $val == 'swap' )
				{
					return \IPS\Member::loggedIn()->language()->addToStack( 'profanity_replace_with_x', FALSE, array( 'sprintf' => array( $row['swop'] ) ) );
				}
				else
				{
					return \IPS\Member::loggedIn()->language()->addToStack('profanity_filter_action_moderate');
				}
			},
			'm_exact'				=> function( $val, $row )
			{
				return ( $val ) ? \IPS\Member::loggedIn()->language()->addToStack('profanity_filter_exact') : \IPS\Member::loggedIn()->language()->addToStack('profanity_filter_loose');
			}
		);
		
		/* Specify the root buttons */

		$table->rootButtons['add'] = array(
			'icon'		=> 'plus',
			'title'		=> 'profanity_add',
			'link'		=> \IPS\Http\Url::internal( 'app=core&module=settings&controller=posting&do=profanity' ),
			'data'		=> array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('profanity_add') )
		);

		$table->rootButtons['download'] = array(
			'icon'		=> 'download',
			'title'		=> 'download',
			'link'		=> \IPS\Http\Url::internal( 'app=core&module=settings&controller=posting&do=downloadProfanity' ),
			'data'		=> array( 'confirm' => '', 'confirmMessage' => \IPS\Member::loggedIn()->language()->addToStack('profanity_download'), 'confirmIcon' => 'info', 'confirmButtons' => json_encode( array( 'ok' => \IPS\Member::loggedIn()->language()->addToStack('download'), 'cancel' => \IPS\Member::loggedIn()->language()->addToStack('cancel') ) ) )
		);

		/* And the row buttons */
		$table->rowButtons = function( $row )
		{
			$return = array();

			$return['edit'] = array(
				'icon'		=> 'pencil',
				'title'		=> 'edit',
				'link'		=> \IPS\Http\Url::internal( 'app=core&module=settings&controller=posting&do=profanity&id=' ) . $row['wid'],
				'data'		=> array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('edit') )
			);

			$return['delete'] = array(
				'icon'		=> 'times',
				'title'		=> 'delete',
				'link'		=> \IPS\Http\Url::internal( 'app=core&module=settings&controller=posting&do=deleteProfanityFilters&id=' ) . $row['wid'],
				'data'		=> array( 'delete' => '' ),
			);
				
			return $return;
		};
		
		return $table;
	}
	
	/**
	 * Form
	 *
	 * @param	\IPS\core\Profanity|NULL	$current	If we are editing, an \IPS\core\Profanity instance of the record
	 * @return	\IPS\Helpers\Form
	 */
	public static function form( \IPS\core\Profanity $current=NULL )
	{
		$form = new \IPS\Helpers\Form;
		
		if ( !$current )
		{
			$form->addTab('add');
		}
		
		$form->add( new \IPS\Helpers\Form\Text( 'profanity_type', ( $current ) ? $current->type : NULL, NULL, array() ) );
		$form->add( new \IPS\Helpers\Form\Radio( 'profanity_action', ( $current ) ? $current->action : 'swap', FALSE, array(
			'options'	=> array(
				'swap'		=> 'profanity_filter_action_swap',
				'moderate'	=> 'profanity_filter_action_moderate'
			),
			'toggles'	=> array(
				'swap'		=> array( 'profanity_swop' ),
				'moderate'	=> array()
			)
		) ) );
		$form->add( new \IPS\Helpers\Form\Text( 'profanity_swop', ( $current ) ? $current->swop : NULL, NULL, array(), NULL, NULL, NULL, 'profanity_swop' ) );
		$form->add( new \IPS\Helpers\Form\Radio( 'profanity_m_exact', ( $current ) ? $current->m_exact : NULL, FALSE, array(
			'options' => array(
				'1' => 'profanity_filter_exact',
				'0'	=> 'profanity_filter_loose' )
		), NULL, NULL, NULL, 'profanity_m_exact' ) );
		
		if ( !$current )
		{
			$form->addTab('upload');
			$form->add( new \IPS\Helpers\Form\Upload( 'profanity_upload', NULL, NULL, array( 'allowedFileTypes' => array( 'xml' ), 'temporary' => TRUE ) ) );
		}
		
		return $form;
	}
	
	/**
	 * Create From Form
	 *
	 * @param	array						$values		Array of values
	 * @param	\IPS\core\Profanity|NULL	$current	If we are editing, an \IPS\core\Profanity instance of the record
	 * @return	\IPS\core\Profanity
	 */
	public static function createFromForm( array $values, \IPS\core\Profanity $current=NULL )
	{
		if ( $current )
		{
			$obj = $current;
		}
		else
		{
			$obj = new static;
		}
		
		if ( array_key_exists( 'type', $values ) )
		{
			$obj->type = $values['type'];
		}
		
		if ( array_key_exists( 'swop', $values ) )
		{
			$obj->swop = $values['swop'];
		}
		
		if ( array_key_exists( 'm_exact', $values ) )
		{
			$obj->m_exact = $values['m_exact'];
		}
		
		if ( array_key_exists( 'action', $values ) )
		{
			if ( in_array( $values['action'], static::$actionTypes ) )
			{
				$obj->action = $values['action'];
			}
		}
		
		$obj->save();
		
		unset( \IPS\Data\Store::i()->profanityFilters );
		
		return $obj;
	}
	
	/**
	 * Get Profanity
	 *
	 * @return	array
	 */
	public static function getProfanity()
	{
		$return = array();
		if ( isset( \IPS\Data\Store::i()->profanityFilters ) )
		{
			foreach( \IPS\Data\Store::i()->profanityFilters AS $id => $row )
			{
				$return[ $id ] = static::constructFromData( $row );
			}
			
			return $return;
		}
		
		$toCache	= array();

		foreach( \IPS\Db::i()->select( '*', 'core_profanity_filters' ) AS $row )
		{
			$toCache[ $row['wid'] ]	= $row;

			$return[ $row['wid'] ] = static::constructFromData( $row );
		}
		
		\IPS\Data\Store::i()->profanityFilters = $toCache;
		
		return $return;
	}
	
	/**
	 * Check if the content should be hidden by profanity or url filters
	 *
	 * @param	string	$content	The content to check
	 * @return	bool
	 */
	public static function hiddenByFilters( $content )
	{
		$return = static::_checkProfanityFilters( $content );
		
		if ( $return == FALSE )
		{
			$return = static::_checkUrlFilters( $content );
		}
		
		return $return;
	}
	
	/**
	 * Check Profanity Filters
	 *
	 * @param	string	$content	The content to check
	 * @return	bool
	 */
	protected static function _checkProfanityFilters( $content )
	{
		$looseProfanity = array();
		$exactProfanity = array();
		foreach( static::getProfanity() AS $profanity )
		{
			if ( $profanity->action == 'moderate' )
			{
				if ( $profanity->m_exact )
				{
					$exactProfanity[] = $profanity->type;
				}
				else
				{
					$looseProfanity[] = $profanity->type;
				}
			}
		}
		
		/* Loose is easy - if any of the words are present, then mod queue */
		if ( count( $looseProfanity ) )
		{
			foreach( $looseProfanity AS $word )
			{
				if ( mb_stristr( $content, $word ) )
				{
					return TRUE;
					break;
				}
			}
		}
		
		/* Still here? Check exact - this gets a bit more complicated. */
		if ( count( $exactProfanity ) )
		{
			$words = array();
			foreach( $exactProfanity AS $word )
			{
				$words[] = preg_quote( $word, '/' );
			}
			
			$split = preg_split( '/((?=<^|\b)(?:' . implode( '|', $words ) . ')(?=\b|$))/iu', $content, null, PREG_SPLIT_DELIM_CAPTURE );
			
			if ( is_array( $split ) )
			{
				foreach( $split AS $section )
				{
					if ( in_array( $section, $exactProfanity ) )
					{
						return TRUE;
						break;
					}
				}
			}
		}
		
		/* Still here? All good */
		return FALSE;
	}
	
	/**
	 * Check URL Filters
	 *
	 * @param	string	$content	The content to check
	 * @return	bool
	 */
	protected static function _checkUrlFilters( $content )
	{
		/* If we are allowing ANY URL's and not doing anything, then do that */
		if ( \IPS\Settings::i()->ipb_url_filter_option == 'none' AND \IPS\Settings::i()->url_filter_any_action == 'allow' )
		{
			return FALSE;
		}
		
		/* If we are using a black or white list, but not moderating, do that. */
		if ( in_array( \IPS\Settings::i()->ipb_url_filter_option, array( 'black', 'white' ) ) AND \IPS\Settings::i()->url_filter_action == 'block' )
		{
			return FALSE;
		}
		
		$urls = ( \IPS\Settings::i()->ipb_url_filter_option == 'black' ) ? explode( ',', \IPS\Settings::i()->ipb_url_blacklist ) : explode( ',', \IPS\Settings::i()->ipb_url_whitelist );
		
		if ( \IPS\Settings::i()->ipb_url_filter_option == 'white' )
		{
			$urls[] = "http://" . parse_url( \IPS\Settings::i()->base_url, PHP_URL_HOST ) . "/*";
			$urls[] = "https://" . parse_url( \IPS\Settings::i()->base_url, PHP_URL_HOST ) . "/*";
		}
		
		try
		{
			/* Load the content so we can look for URL's */
			$dom = new \IPS\Xml\DOMDocument;
			$dom->loadHTML( $content );
			
			/* Gather up all URL's */
			$tags = $dom->getElementsByTagName('a');
			$good = NULL;
			foreach( $tags AS $tag )
			{
				if ( $tag->hasAttribute( 'href' ) and !$tag->hasAttribute( 'data-mentionid' ) )
				{
					if ( \IPS\Settings::i()->ipb_url_filter_option == 'none' )
					{
						return TRUE;
					}
					
					foreach( $urls AS $url )
					{
						$url = preg_quote( $url, '/' );
						$url = str_replace( '\*', "(.+?)", $url );
						
						if ( \IPS\Settings::i()->ipb_url_filter_option == 'black' )
						{
							if ( preg_match( '/' . $url . '/i', $tag->getAttribute( 'href' ) ) )
							{
								return TRUE;
							}
						}
						else if ( \IPS\Settings::i()->ipb_url_filter_option == 'white' )
						{
							if ( is_null( $good ) AND !preg_match( '/' . $url . '/i', $tag->getAttribute( 'href' ) ) )
							{
								$good = FALSE;
								break 2;
							}
							else
							{
								$good = TRUE;
								break 2;
							}
						}
					}
				}
			}
			
			if ( $good === FALSE AND \IPS\Settings::i()->ipb_url_filter_option == 'white' )
			{
				return TRUE;
			}
		}
		catch( \Exception $e ) { }
		
		return FALSE;
	}

	/**
	 * [ActiveRecord] Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{
		parent::delete();

		unset( \IPS\Data\Store::i()->profanityFilters );
	}
	
	/**
	 * JSON Serialize
	 *
	 * @return	array
	 */
	public function jsonSerialize()
	{
		return array(
			'wid'		=> $this->wid,
			'type'		=> $this->type,
			'swop'		=> $this->swop,
			'm_exact'	=> $this->m_exact,
			'action'	=> $this->action
		);
	}
}
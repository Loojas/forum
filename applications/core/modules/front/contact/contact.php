<?php
/**
 * @brief		Contact Form
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		12 Nov 2013
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\core\modules\front\contact;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Contact Form
 */
class _contact extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		if ( !static::canUseContactUs() )
		{
			\IPS\Output::i()->error( 'no_permission', '2S333/1', 403, '' );
		}

		/* Execute */
		return parent::execute();
	}
	/**
	 * Method
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Get extensions */
		$extensions = \IPS\Application::allExtensions( 'core', 'ContactUs', FALSE, 'core', 'InternalEmail', TRUE );


		\IPS\Output::i()->sidebar['enabled'] = FALSE;
		\IPS\Output::i()->bodyClasses[] = 'ipsLayout_minimal';

		$form = new \IPS\Helpers\Form( 'contact', 'send' );
		$form->class = 'ipsForm_vertical';
		
		$form->add( new \IPS\Helpers\Form\Editor( 'contact_text', NULL, TRUE, array(
				'app'			=> 'core',
				'key'			=> 'Contact',
				'autoSaveKey'	=> 'contact-' . \IPS\Member::loggedIn()->member_id,
		) ) );
		
		if ( !\IPS\Member::loggedIn()->member_id )
		{
			$form->add( new \IPS\Helpers\Form\Text( 'contact_name', NULL, TRUE ) );
			$form->add( new \IPS\Helpers\Form\Email( 'email_address', NULL, TRUE, array( 'bypassProfanity' => TRUE ) ) );
			if ( \IPS\Settings::i()->bot_antispam_type !== 'none' and \IPS\Settings::i()->guest_captcha )
			{
				$form->add( new \IPS\Helpers\Form\Captcha );
			}
		}
		foreach ( $extensions as $k => $class )
		{
			$class->runBeforeFormOutput( $form );
		}
		
		if ( $values = $form->values() )
		{
			foreach ( $extensions as $k => $class )
			{
				if ( $handled = $class->handleForm( $values ) )
				{
					break;
				}
			}

			if( \IPS\Request::i()->isAjax() )
			{
				\IPS\Output::i()->json( 'OK' );
			}

			\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack( 'message_sent' );
			\IPS\Output::i()->output	= \IPS\Theme::i()->getTemplate( 'system' )->contactDone();
		}
		else
		{
			\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack( 'contact' );
			\IPS\Output::i()->output	= \IPS\Theme::i()->getTemplate( 'system' )->contact( $form );	
		}	
		
	}

	/**
	 * Is the current user allowed to use the contact us form
	 *
	 * @return bool
	 */
	public static function canUseContactUs()
	{
		try
		{
			$module = \IPS\Application\Module::get( 'core', 'contact' );
		}
		catch ( \OutOfRangeException $e )
		{
			return FALSE;
		}
		
		if ( !\IPS\Member::loggedIn()->canAccessModule( $module ) )
		{
			return FALSE;
		}

		/* If all groups have access, we can */
		if( \IPS\Settings::i()->contact_access != '*' )
		{
			/* Check member */
			$member	= \IPS\Member::loggedIn();
			$memberGroups	= array_merge( array( $member->member_group_id ), array_filter( explode( ',', $member->mgroup_others ) ) );
			$accessGroups	= explode( ',', \IPS\Settings::i()->contact_access );

			/* Are we in an allowed group? */
			if( count( array_intersect( $accessGroups, $memberGroups ) ) )
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		return TRUE;
	}
}
<?php
/**
 * @brief		Terms of Use
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		25 Sept 2013
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\core\modules\front\system;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Terms of Use
 */
class _terms extends \IPS\Dispatcher\Controller
{
	/**
	 * Terms of Use
	 *
	 * @return	void
	 */
	protected function manage()
	{
		\IPS\Session::i()->setLocation( \IPS\Http\Url::internal( 'app=core&module=system&controller=terms', NULL, 'terms' ), array(), 'loc_viewing_reg_terms' );
		
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('reg_terms');
		\IPS\Output::i()->sidebar['enabled'] = FALSE;
		\IPS\Output::i()->bodyClasses[] = 'ipsLayout_minimal';
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'system' )->terms();
	}
	
	/**
	 * Dismiss Terms
	 *
	 * @return	void
	 */
	protected function dismiss()
	{
		\IPS\Request::i()->setCookie( 'guestTermsDismissed', 1, NULL, FALSE );

		if ( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->json( array( 'message' => \IPS\Member::loggedIn()->language()->addToStack( 'terms_dismissed' ) ) );
		}
		else
		{
			if ( isset( \IPS\Request::i()->ref ) )
			{
				try
				{
					$url = \IPS\Http\Url::createFromString( base64_decode( \IPS\Request::i()->ref ) );
				}
				catch( \IPS\Http\Url\Exception $e )
				{
					$url = NULL;
				}
				
				if ( $url instanceof \IPS\Http\Url\Internal )
				{
					\IPS\Output::i()->redirect( $url, 'terms_dismissed' );
				}
			}
			
			/* Still here? Just redirect to the index */
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( '' ), 'terms_dismissed' );
		}
	}
}
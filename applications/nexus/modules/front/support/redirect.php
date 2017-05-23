<?php
/**
 * @brief		Legacy Redirector
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Commerce
 * @since		07 Sep 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\nexus\modules\front\support;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Legacy Redirector
 */
class _redirect extends \IPS\Dispatcher\Controller
{
	/**
	 * Redirect
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$url = \IPS\Http\Url::internal( "app=core&module=system&controller=redirect" )->setQueryString( array(
			'url'		=> \IPS\Request::i()->url,
			'resource'	=> ( \IPS\Request::i()->resource ) ? 1 : NULL,
			'key'		=> hash_hmac( "sha256", \IPS\Request::i()->url, \IPS\Settings::i()->site_secret_key ),
		) );
		
		\IPS\Output::i()->redirect( $url );
	}
}
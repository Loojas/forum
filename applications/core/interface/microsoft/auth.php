<?php
/**
 * @brief		Microsoft Account Login Handler Redirect URI Handler
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		20 Mar 2013
 * @version		SVN_VERSION_NUMBER
 */

require_once str_replace( 'applications/core/interface/microsoft/auth.php', '', str_replace( '\\', '/', __FILE__ ) ) . 'init.php';

$state = explode( '-', \IPS\Request::i()->state );

if ( $state[0] == 'ucp' )
{
	\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=system&controller=settings&area=profilesync&service=Microsoft&loginProcess=microsoft", 'front', 'settings_Microsoft' )->setQueryString( array( 'state' => $state[1], 'code' => \IPS\Request::i()->code ) ) );
}
else
{
	/* Verify this handler is acceptable for the base we are logging in to */
	$loginHandlers	= \IPS\Login::handlers( TRUE );

	if( !isset( $loginHandlers['Live'] ) OR ( $base[0] == 'admin' AND !$loginHandlers['Live']->acp ) )
	{
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( NULL ) );
	}

	$destination = \IPS\Http\Url::internal( "app=core&module=system&controller=login&loginProcess=live", $state[0], 'login', NULL, \IPS\Settings::i()->logins_over_https ? \IPS\Http\Url::PROTOCOL_HTTPS : 0 )->setQueryString( array( 'state' => $state[1], 'code' => \IPS\Request::i()->code ) );
	if ( !empty( $state[2] ) )
	{
		$destination = $destination->setQueryString( 'ref', $state[2] );
	}
	
	\IPS\Output::i()->redirect( $destination );
}
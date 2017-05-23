<?php
/**
 * @brief		Upgrader: Custom Post Upgrade Message
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		16 May 2016
 * @version		SVN_VERSION_NUMBER
 */

if ( \IPS\Application::appIsEnabled('cms' ) )
{
	$message = \IPS\Theme::i()->getTemplate( 'global' )->block( NULL, "Please check any custom moderator permissions (ACP -> Members -> Moderators) for Pages Database categories. These have been reset to 'All Categories'." );
}	

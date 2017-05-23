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

/* Check for the older style redirect scripts, and if present tell the admin to remove. */
$redirectScriptNames = array(
	'forumdisplay.php',
	'member.php',
	'showthread.php',
	'showgallery.php',
	'showphoto.php',
	'memberlist.php',
	'viewforum.php',
	'viewtopic.php',
	'board.php',
	'profile.php',
	'topic.php',
	'album.php',
	'attachment.php',
	'picture.php',
	'showpost.php',
	'showthread.php',
	'tags.php',
	'archive/index.php',
	'vb_gateway.php',
	'proxy.php'
);

$filesPresent		= array();

foreach( $redirectScriptNames as $filename )
{
	if( file_exists( \IPS\ROOT_PATH . '/' . $filename ) )
	{
		$filesPresent[]	= $filename;
	}
}

if( count( $filesPresent ) )
{
	$message = <<<EOF
	<div class='ipsType_left'>The following files are present in your Community Suite root directory to handle redirects for a conversion, but are no longer needed and should be removed:
	<br><br>&#8226; 
EOF;

	$message .= implode( "<br>&#8226; ", $filesPresent );

	$message .= <<<EOF
	<br><br>
	Please also be sure that your .htaccess file is reset back to the default Community Suite .htaccess code. You can download the default .htaccess file for the Community Suite in the ACP under System -&gt; Site Promotion -&gt; Search Engine Optimization by clicking the link in the setting "Rewrite URLs?".
	</div>
EOF;

	$message = \IPS\Theme::i()->getTemplate( 'global' )->block( NULL, $message );
}

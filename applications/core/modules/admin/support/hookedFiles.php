<?php
/**
 * @brief		hookedFiles
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite

 * @since		11 Nov 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\core\modules\admin\support;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * hookedFiles
 */
class _hookedFiles extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'app_manage' );
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		$table = new \IPS\Helpers\Table\Db( 'core_hooks', \IPS\Http\Url::internal( 'app=core&module=support&controller=hookedFiles' ) );
		$table->langPrefix = 'hooks_';
		$table->quickSearch = array( 'class', 'hook_class' );
		$table->include = array(  'hooks_app', 'hooks_plugin', 'hooks_type', 'hooks_class', 'hooks_file' );

		$table->parsers = array(
			'hooks_app' => function( $val, $row )
			{
				try
				{
					return \IPS\Application::load($row['app'])->_title;
				}
				catch ( \OutOfRangeException $e )
				{
					return \IPS\Member::loggedIn()->language()->addToStack('hook_class_none');
				}
			},
			'hooks_plugin' 	=> function( $val, $row )
			{
				try
				{
					return \IPS\Plugin::load($row['plugin'])->name;
				}
				catch ( \OutOfRangeException $e )
				{
					return \IPS\Member::loggedIn()->language()->addToStack('hook_class_none');
				}
			},
			'hooks_type' 	=> function( $val, $row )
			{
				return \IPS\Member::loggedIn()->language()->addToStack( 'plugin_hook_type_' . mb_strtolower( $row['type'] ) );
			},
			'hooks_class' => function( $val, $row )
			{
				return $row['class'];
			},
			'hooks_file' => function( $val, $row )
			{
				if ( $row['app'] )
				{
					return '/applications/' . $row['app'] . '/hooks/' . $row['filename'] . '.php';
				}
				else if ( $row['plugin'] )
				{
					try
					{
						$plugin = \IPS\Plugin::load($row['plugin']);
						return '/plugins' . ( $plugin->location ? '/' . $plugin->location . '/hooks/' : '/' ) . $row['filename'] . '.php';
					}
					catch ( \OutOfRangeException $e )
					{
						return \IPS\Member::loggedIn()->language()->addToStack('hook_class_none');
					}
				}
			}
		);

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack( 'hooked_classes' );
		\IPS\Output::i()->output = $table;
	}
	
}
<?php
/**
 * @brief		Create Blog
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Blog
 * @since		24 Mar 2014
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\blog\modules\front\blogs;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Create Blog
 */
class _create extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		parent::execute();
	}

	/**
	 * Manage
	 *
	 * @return	void
	 */
	protected function manage()
	{
		if ( !\IPS\blog\Blog::canCreate() )
		{
			\IPS\Output::i()->error( 'no_module_permission', '2B227/1', 403, '' );
		}
		
		$form = new \IPS\Helpers\Form( 'select_blog', 'continue' );
		$form->class = 'ipsForm_vertical';
		
		$blog	= new \IPS\blog\Blog;
		$blog->member_id = \IPS\Member::loggedIn()->member_id;
		$blog->form( $form, TRUE );

		if ( $values = $form->values() )
		{
			$blog->saveForm( $blog->formatFormValues( $values ) );
		
			/* Redirect */
			\IPS\Output::i()->redirect( $blog->url() );
		}
		
		\IPS\Session::i()->setLocation( \IPS\Http\Url::internal( 'app=blog', 'front', 'blogs' ), array(), 'loc_blog_creating' );
					
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('create_blog');

		if( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->output = $form->customTemplate( array( call_user_func_array( array( \IPS\Theme::i(), 'getTemplate' ), array( 'forms', 'core' ) ), 'popupTemplate' ) );
		}
		else
		{
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'submit' )->createBlog( $form );
		}
	}
}
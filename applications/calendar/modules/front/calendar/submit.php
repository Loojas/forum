<?php
/**
 * @brief		Submit Event Controller
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Calendar
 * @since		8 Jan 2014
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\calendar\modules\front\calendar;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Submit Event Controller
 */
class _submit extends \IPS\Dispatcher\Controller
{
	/**
	 * Choose Calendar
	 *
	 * @return	void
	 */
	protected function manage()
	{
			$form = new \IPS\Helpers\Form( 'select_calendar', 'continue' );
			$form->class = 'ipsForm_vertical ipsForm_noLabels';
			$form->add( new \IPS\Helpers\Form\Node( 'calendar', NULL, TRUE, array(
				'url'					=> \IPS\Http\Url::internal( 'app=calendar&module=calendar&controller=submit', 'front', 'calendar_submit' ),
				'class'					=> 'IPS\calendar\Calendar',
				'permissionCheck'		=> 'add',
				'forceOwner'		=> \IPS\Member::loggedIn(),
			) ) );

			/* Are we creating an event for a specific day? If yes, pass the values to the form */
			if( \IPS\Request::i()->y AND \IPS\Request::i()->m AND \IPS\Request::i()->d )
			{
				$form->hiddenValues['y'] = \IPS\Request::i()->y;
				$form->hiddenValues['m'] = \IPS\Request::i()->m;
				$form->hiddenValues['d'] = \IPS\Request::i()->d;
			}

			if ( $values = $form->values() )
			{
				$url = \IPS\Http\Url::internal( 'app=calendar&module=calendar&controller=submit&do=submit', 'front', 'calendar_submit' )->setQueryString( 'id', $values['calendar']->_id );

				if( isset( $values['y'], $values['m'], $values['d'] ) )
				{
					$url = $url->setQueryString( 'd', $values['d'] )->setQueryString( 'm', $values['m'] )->setQueryString( 'y', $values['y'] );
				}

				\IPS\Output::i()->redirect( $url );
			}

		/* Display */
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack( 'submit_event' );
		\IPS\Output::i()->sidebar['enabled'] = FALSE;
		\IPS\Output::i()->breadcrumb[] = array( NULL, \IPS\Member::loggedIn()->language()->addToStack( 'add_cal_event_header' ) );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'submit' )->calendarSelector( $form );
	}

	/**
	 * Submit Event
	 *
	 * @return	void
	 */
	protected function submit()
	{

		try
		{
			$calendar = \IPS\calendar\Calendar::loadAndCheckPerms( \IPS\Request::i()->id );
		}
		catch ( \OutOfRangeException $e )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=calendar&module=calendar&controller=submit', 'front', 'calendar_submit' ) );
		}

		$form = \IPS\calendar\Event::create( $calendar );

		\IPS\Output::i()->output	= \IPS\Theme::i()->getTemplate( 'submit' )->submitPage( $form->customTemplate( array( call_user_func_array( array( \IPS\Theme::i(), 'getTemplate' ), array( 'submit', 'calendar' ) ), 'submitForm' ) ) );

		if ( \IPS\calendar\Event::moderateNewItems( \IPS\Member::loggedIn() ) )
		{
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'forms', 'core' )->modQueueMessage( \IPS\Member::loggedIn()->warnings( 5, NULL, 'mq' ), \IPS\Member::loggedIn()->mod_posts ) . \IPS\Output::i()->output;
		}

		/* Display */
		\IPS\Output::i()->title		= \IPS\Member::loggedIn()->language()->addToStack( 'submit_event' );
		\IPS\Output::i()->sidebar['enabled'] = FALSE;
		\IPS\Output::i()->breadcrumb[] = array( NULL, \IPS\Member::loggedIn()->language()->addToStack( 'add_cal_event_header' ) );
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'front_submit.js', 'calendar', 'front' ) );
	}
}
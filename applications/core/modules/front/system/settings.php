<?php
/**
 * @brief		User CP Controller
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		10 Jun 2013
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
 * User CP Controller
 */
class _settings extends \IPS\Dispatcher\Controller
{

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Output::i()->sidebar['enabled'] = FALSE;
		parent::execute();
	}

	/**
	 * Settings
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Only logged in members */
		if ( !\IPS\Member::loggedIn()->member_id )
		{
			\IPS\Output::i()->error( 'no_module_permission_guest', '2C122/1', 403, '' );
		}
		
		/* Work out output */
		$area = \IPS\Request::i()->area ?: 'overview';
		if ( method_exists( $this, "_{$area}" ) )
		{
			$output = call_user_func( array( $this, "_{$area}" ) );
		}
		
		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('settings');
		\IPS\Output::i()->breadcrumb[] = array( NULL, \IPS\Member::loggedIn()->language()->addToStack('settings') );
		if ( !\IPS\Request::i()->isAjax() )
		{
			if ( \IPS\Request::i()->service )
			{
				$area = "{$area}_" . \IPS\Request::i()->service;
			}
            
            \IPS\Output::i()->cssFiles	= array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'styles/settings.css' ) );
            
            if ( \IPS\Theme::i()->settings['responsive'] )
            {
                \IPS\Output::i()->cssFiles	= array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'styles/settings_responsive.css' ) );
            }
            
            if ( $output )
            {
				\IPS\Output::i()->output .= $this->_wrapOutputInTemplate( $area, $output );
			}
		}
		elseif ( $output )
		{
			\IPS\Output::i()->output .= $output;
		}
	}
	
	/**
	 * Wrap output in template
	 *
	 * @param	string	$area	Active area
	 * @param	string	$output	Output
	 * @return	string
	 */
	protected function _wrapOutputInTemplate( $area, $output )
	{
		/* What can we do? */
		$canChangeEmail = FALSE;
		$canChangePassword = FALSE;
		$canChangeUsername = FALSE;
		$canConfigureMfa = FALSE;
		foreach ( \IPS\Login::handlers( TRUE ) as $k => $handler )
		{
			if ( \IPS\Member::loggedIn()->group['g_dname_changes'] and $handler->canChange( 'username', \IPS\Member::loggedIn() ) )
			{
				$canChangeUsername = TRUE;
			}
			if ( $handler->canChange( 'email', \IPS\Member::loggedIn() ) )
			{
				$canChangeEmail = TRUE;
			}
			if ( $handler->canChange( 'password', \IPS\Member::loggedIn() ) )
			{
				$canChangePassword = TRUE;
			}
		}
		foreach ( \IPS\MFA\MFAHandler::handlers() as $handler )
		{
			if ( $handler->isEnabled() and $handler->memberCanUseHandler( \IPS\Member::loggedIn() ) )
			{
				$canConfigureMfa = TRUE;
				break;
			}
		}

		$sigLimits = explode( ":", \IPS\Member::loggedIn()->group['g_signature_limits'] );
		$canChangeSignature = (bool) ( \IPS\Settings::i()->signatures_enabled && !$sigLimits[0]	);
				
		/* Add sync services */
		$services = \IPS\core\ProfileSync\ProfileSyncAbstract::services();
		
		/* Return */
		return \IPS\Theme::i()->getTemplate( 'system' )->settings( $area, $output, $canChangeEmail, $canChangePassword, $canChangeUsername, $canChangeSignature, $services, $canConfigureMfa );
	}
	
	/**
	 * Overview
	 *
	 * @return	string
	 */
	protected function _overview()
	{
		$services = array();

		foreach ( \IPS\core\ProfileSync\ProfileSyncAbstract::services() as $key => $class )
		{
			$services[$key] = new $class( \IPS\Member::loggedIn() );
		}
				
		return \IPS\Theme::i()->getTemplate( 'system' )->settingsOverview( $services );
	}
	
	/**
	 * Email
	 *
	 * @return	string
	 */
	protected function _email()
	{
		if( \IPS\Member::loggedIn()->isAdmin() )
		{
			return \IPS\Theme::i()->getTemplate( 'system' )->settingsEmail();
		}
		
		$mfaOutput = \IPS\MFA\MFAHandler::accessToArea( 'core', 'EmailChange', \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=password', 'front', 'settings_password' ) );
		if ( $mfaOutput )
		{
			if ( \IPS\Request::i()->isAjax() )
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=mfa', 'front', 'settings_mfa' ) );
			}
			\IPS\Output::i()->output = $mfaOutput;
		}
		
		/* Do we have any pending validation emails? */
		try
		{
			$pending = \IPS\Db::i()->select( '*', 'core_validating', array( 'member_id=? AND email_chg=1', \IPS\Member::loggedIn()->member_id ), 'entry_date DESC' )->first();
		}
		catch( \UnderflowException $e )
		{
			$pending = null;
		}
		
		/* Build the form */
		$form = new \IPS\Helpers\Form;
		$form->class = 'ipsForm_collapseTablet';
		$form->addDummy( 'current_email', htmlspecialchars( \IPS\Member::loggedIn()->email, \IPS\HTMLENTITIES, 'UTF-8', FALSE ) );
		$form->add( new \IPS\Helpers\Form\Email( 'new_email', '', TRUE, array( 'accountEmail' => TRUE ) ) );
		$form->add( new \IPS\Helpers\Form\Password( 'current_password', '', TRUE, array( 'validateFor' => \IPS\Member::loggedIn() ) ) );
		
		/* Handle submissions */
		if ( !$mfaOutput and $values = $form->values() )
		{
			$oldEmail = \IPS\Member::loggedIn()->email;

			/* Change the email */
			foreach ( \IPS\Login::handlers( TRUE ) as $handler )
			{
				/* We cannot update our email address in some login handlers, that's ok */
				try
				{
					$handler->changeEmail( \IPS\Member::loggedIn(), $oldEmail, $values['new_email'] );
				}
				catch( \BadMethodCallException $e ){}
			}
						
			/* Delete any pending validation emails */
			if ( $pending['vid'] )
			{
				\IPS\Db::i()->delete( 'core_validating', array( 'member_id=? AND email_chg=1', \IPS\Member::loggedIn()->member_id ) );
			}
						
			/* Send a validation email if we need to */
			if ( \IPS\Settings::i()->reg_auth_type == 'user' or \IPS\Settings::i()->reg_auth_type == 'admin_user' )
			{
				$vid = \IPS\Login::generateRandomString();
				
				\IPS\Db::i()->insert( 'core_validating', array(
					'vid'			=> $vid,
					'member_id'		=> \IPS\Member::loggedIn()->member_id,
					'entry_date'	=> time(),
					'email_chg'		=> TRUE,
					'ip_address'	=> \IPS\Request::i()->ipAddress(),
					'prev_email'	=> $oldEmail,
					'email_sent'	=> time(),
				) );

				\IPS\Member::loggedIn()->members_bitoptions['validating'] = TRUE;
				\IPS\Member::loggedIn()->save();
				
				\IPS\Email::buildFromTemplate( 'core', 'email_change', array( \IPS\Member::loggedIn(), $vid ), \IPS\Email::TYPE_TRANSACTIONAL )->send( \IPS\Member::loggedIn() );
							
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( '' ) );
			}
			
			/* Or just redirect */
			else
			{
				/* Send a confirmation email */
				\IPS\Email::buildFromTemplate( 'core', 'email_address_changed', array( \IPS\Member::loggedIn(), $oldEmail ), \IPS\Email::TYPE_TRANSACTIONAL )->send( $oldEmail, array(), array(), NULL, NULL, array( 'Reply-To' => \IPS\Settings::i()->email_in ) );

				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=email', 'front', 'settings' ), 'email_changed' );
			}
		}
		
		return \IPS\Theme::i()->getTemplate( 'system' )->settingsEmail( $form );
	}
	
	/**
	 * Password
	 *
	 * @return	string
	 */
	protected function _password()
	{
		if( \IPS\Member::loggedIn()->isAdmin() )
		{
			return \IPS\Theme::i()->getTemplate( 'system' )->settingsPassword();
		}
		
		$mfaOutput = \IPS\MFA\MFAHandler::accessToArea( 'core', 'PasswordChange', \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=password', 'front', 'settings_password' ) );
		if ( $mfaOutput )
		{
			if ( \IPS\Request::i()->isAjax() )
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=mfa', 'front', 'settings_mfa' ) );
			}
			\IPS\Output::i()->output = $mfaOutput;
		}
		
		$form = new \IPS\Helpers\Form;
		$form->class = 'ipsForm_collapseTablet';
		$form->add( new \IPS\Helpers\Form\Password( 'current_password', '', TRUE, array( 'validateFor' => \IPS\Member::loggedIn() ) ) );
		$form->add( new \IPS\Helpers\Form\Password( 'new_password', '', TRUE, array( 'showMeter' => \IPS\Settings::i()->password_strength_meter ) ) );
		$form->add( new \IPS\Helpers\Form\Password( 'confirm_new_password', '', TRUE, array( 'confirm' => 'new_password' ) ) );
		
		if ( !$mfaOutput and $values = $form->values() )
		{
			foreach ( \IPS\Login::handlers( TRUE ) as $handler )
			{
				/* We cannot update our password in some login handlers, that's ok */
				try
				{
					$handler->changePassword( \IPS\Member::loggedIn(), $values['new_password'] );
				}
				catch( \BadMethodCallException $e ){}
			}

			/* If we have a pass_hash cookie, update it so we stay logged in on the next page load */
			if( isset( \IPS\Request::i()->cookie['pass_hash'] ) )
			{
				$expire = new \IPS\DateTime;
				$expire->add( new \DateInterval( 'P3M' ) );
				\IPS\Request::i()->setCookie( 'pass_hash', \IPS\Member::loggedIn()->member_login_key, $expire );
			}

			/* Send a confirmation email */
			\IPS\Email::buildFromTemplate( 'core', 'password_changed', array(), \IPS\Email::TYPE_TRANSACTIONAL )->send( \IPS\Member::loggedIn(), array(), array(), NULL, NULL, array( 'Reply-To' => \IPS\Settings::i()->email_in ) );

			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=password', 'front', 'settings' ), 'password_changed' );
		}
		
		return \IPS\Theme::i()->getTemplate( 'system' )->settingsPassword( $form );
	}
	
	/**
	 * MFA
	 *
	 * @return	string
	 */
	protected function _mfa()
	{
		/* Validate password */
		if ( !isset( $_SESSION['passwordValidatedForMfa'] ) )
		{
			$form = new \IPS\Helpers\Form( 'mfa_password', 'continue' );
			$form->class = 'ipsForm_collapseTablet';
			$form->add( new \IPS\Helpers\Form\Password( 'password', '', TRUE, array( 'validateFor' => \IPS\Member::loggedIn() ) ) );
			if ( $form->values() )
			{
				$_SESSION['passwordValidatedForMfa'] = TRUE;
			}
			else
			{
				return \IPS\Theme::i()->getTemplate( 'system' )->settingsMfaPassword( $form );
			}
		}
		
		/* Do MFA check */
		$mfaOutput = \IPS\MFA\MFAHandler::accessToArea( 'core', 'SecurityQuestions', \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=mfa', 'front', 'settings_mfa' ) );
		if ( $mfaOutput )
		{
			if ( \IPS\Request::i()->isAjax() )
			{
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=mfa', 'front', 'settings_mfa' ) );
			}
			\IPS\Output::i()->output = $mfaOutput;
		}
		
		/* Show it */
		$handlers = array();
		foreach ( \IPS\MFA\MFAHandler::handlers() as $key => $handler )
		{
			if ( $handler->isEnabled() and $handler->memberCanUseHandler( \IPS\Member::loggedIn() ) )
			{
				$handlers[ $key ] = $handler;
			}
		}
		return \IPS\Theme::i()->getTemplate( 'system' )->settingsMfa( $handlers );
	}
	
	/**
	 * Initial MFA Setup
	 *
	 * @return	string
	 */
	protected function initialMfa()
	{
		$ref = \IPS\Http\Url::internal('');
		if ( isset( \IPS\Request::i()->ref ) )
		{
			try
			{
				$ref = \IPS\Http\Url\Friendly::createFromString( base64_decode( \IPS\Request::i()->ref ) );
			}
			catch ( \Exception $e ) { }
		}
		
		$handlers = array();
		foreach ( \IPS\MFA\MFAHandler::handlers() as $key => $handler )
		{
			if ( $handler->isEnabled() and $handler->memberCanUseHandler( \IPS\Member::loggedIn() ) )
			{
				$handlers[ $key ] = $handler;
			}
		}
		
		if ( isset( \IPS\Request::i()->mfa_setup ) )
		{
			foreach ( $handlers as $key => $handler )
			{
				if ( ( count( $handlers ) == 1 ) or $key == \IPS\Request::i()->mfa_method )
				{
					if ( $handler->configurationScreenSubmit( \IPS\Member::loggedIn() ) )
					{							
						$_SESSION['MFAAuthenticated'] = time();
						\IPS\Output::i()->redirect( $ref );
					}
				}
			}
		}
		
		foreach ( $handlers as $key => $handler )
		{
			if ( $handler->memberHasConfiguredHandler( \IPS\Member::loggedIn() ) )
			{
				\IPS\Output::i()->redirect( $ref );
			}
		}

		if ( isset( \IPS\Request::i()->_mfa ) and \IPS\Request::i()->_mfa == 'optout' )
		{
			\IPS\Member::loggedIn()->members_bitoptions['security_questions_opt_out'] = TRUE;
			\IPS\Member::loggedIn()->save();
			\IPS\Output::i()->redirect( $ref );
		}
		
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('reg_complete_details');
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( '2fa.css', 'core', 'global' ) );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'login', 'core', 'global' )->mfaSetup( $handlers, \IPS\Member::loggedIn(), \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&do=initialMfa', 'front', 'settings' )->setQueryString( 'ref', base64_encode( $ref ) ) );
	}
	
	/**
	 * Enable MFA
	 *
	 * @return	string
	 */
	protected function enableMfa()
	{
		/* Get the handler */
		$handlers = \IPS\MFA\MFAHandler::handlers();
		$key = \IPS\Request::i()->type;
		if ( !isset( $handlers[ $key ] ) or !$handlers[ $key ]->isEnabled() or !$handlers[ $key ]->memberCanUseHandler( \IPS\Member::loggedIn() ) or \IPS\MFA\MFAHandler::accessToArea( 'core', 'SecurityQuestions', \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=mfa', 'front', 'settings_mfa' ) ) )
		{
			\IPS\Output::i()->error( 'node_error', '2C122/M', 404, '' );
		}

		/* Include the CSS we'll need */
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( '2fa.css', 'core', 'global' ) );
		
		/* Show the list again as the backdrop */
		\IPS\Output::i()->output = $this->_wrapOutputInTemplate( 'mfa', $this->_mfa() );
				
		/* Did we just submit it? */
		if ( isset( \IPS\Request::i()->mfa_setup ) and $handlers[ $key ]->configurationScreenSubmit( \IPS\Member::loggedIn() ) )
		{
			\IPS\Member::loggedIn()->members_bitoptions['security_questions_opt_out'] = FALSE;
			\IPS\Member::loggedIn()->save();
			
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=mfa', 'front', 'settings_mfa' ) );
		}
				
		/* And put the configruation modal over the top */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('settings');
		\IPS\Output::i()->output .= \IPS\Theme::i()->getTemplate( 'system' )->settingsMfaSetup( $handlers[ $key ]->configurationScreen( \IPS\Member::loggedIn() ), \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=mfa&do=enableMfa&type=' . $key, 'front', 'settings_mfa' ) );
	}
	
	/**
	 * Disable MFA
	 *
	 * @return	string
	 */
	protected function disableMfa()
	{
		/* Get the handler */
		$handlers = \IPS\MFA\MFAHandler::handlers();
		$key = \IPS\Request::i()->type;
		if ( !isset( $handlers[ $key ] ) or !$handlers[ $key ]->isEnabled() or !$handlers[ $key ]->memberCanUseHandler( \IPS\Member::loggedIn() ) or \IPS\MFA\MFAHandler::accessToArea( 'core', 'SecurityQuestions', \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=mfa', 'front', 'settings_mfa' ) ) )
		{
			\IPS\Output::i()->error( 'node_error', '2C122/N', 404, '' );
		}
				
		/* Disable it */
		$handlers[ $key ]->disableHandlerForMember( \IPS\Member::loggedIn() );
		\IPS\Member::loggedIn()->save();

		/* If we have now disabled everything, save that we have opted out */
		if ( \IPS\Settings::i()->mfa_required_groups != '*' and !\IPS\Member::loggedIn()->inGroup( explode( ',', \IPS\Settings::i()->mfa_required_groups ) ) )
		{
			$enabledHandlers = FALSE;
			foreach ( $handlers as $handler )
			{
				if ( $handler->memberHasConfiguredHandler( \IPS\Member::loggedIn() ) )
				{
					$enabledHandlers = TRUE;
					break;
				}
			}
			if ( !$enabledHandlers )
			{
				\IPS\Member::loggedIn()->members_bitoptions['security_questions_opt_out'] = TRUE;
				\IPS\Member::loggedIn()->save();
			}
		}
		
		/* Redirect */
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=mfa', 'front', 'settings_mfa' ) );
	}
	
	/**
	 * Security Questions
	 *
	 * @return	string
	 */
	protected function _securityquestions()
	{
		$handler = new \IPS\MFA\SecurityQuestions\Handler();
		
		if ( !$handler->isEnabled() )
		{
			\IPS\Output::i()->error( 'requested_route_404', '2C122/J', 404, '' );
		}
				
		$url = \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=securityquestions', 'front', 'settings_securityquestions' );
		if ( isset( \IPS\Request::i()->initial ) )
		{
			if ( isset( \IPS\Request::i()->ref ) )
			{
				$url = $url->setQueryString( 'ref', \IPS\Request::i()->ref );
			}
						
			if ( !$handler->memberCanUseHandler( \IPS\Member::loggedIn() ) or $handler->memberHasConfiguredHandler( \IPS\Member::loggedIn() ) )
			{
				$ref = NULL;
				if ( isset( \IPS\Request::i()->ref ) )
				{
					try
					{
						$ref = \IPS\Http\Url::createFromString( base64_decode( \IPS\Request::i()->ref ) );
						if ( !( $ref instanceof \IPS\Http\Url\Internal ) )
						{
							$ref = NULL;
						}
					}
					catch ( \Exception $e ) { }
				}
				
				\IPS\Output::i()->redirect( $ref ?: \IPS\Http\Url::internal( '' ) );
			}
			
			$url = $url->setQueryString( 'initial', 1 );
		}
		elseif ( $handler->memberHasConfiguredHandler( \IPS\Member::loggedIn() ) )
		{
			if ( isset( \IPS\Request::i()->_securityQuestionSetup ) )
			{
				return \IPS\Theme::i()->getTemplate( 'system', 'core' )->securityQuestionsFinished();
			}
			elseif ( $output = \IPS\MFA\MFAHandler::accessToArea( 'core', 'SecurityQuestions', $url ) )
			{
				return $output;
			}
		}
		
		$output = $handler->configurationForm( \IPS\Member::loggedIn(), $url, !isset( \IPS\Request::i()->initial ) );
		
		if ( isset( \IPS\Request::i()->initial ) )
		{
			\IPS\Output::i()->bodyClasses[] = 'ipsLayout_minimal';
			\IPS\Output::i()->sidebar['enabled'] = FALSE;
			\IPS\Output::i()->output = $output;
			return;
		}
		else
		{
			return $output;
		}
	}
	
	/**
	 * MFA Email Recovery
	 *
	 * @return	string
	 */
	protected function mfarecovery()
	{
		/* Who are we */
		if ( isset( $_SESSION['processing2FA'] ) )
		{
			$member = \IPS\Member::load( $_SESSION['processing2FA']['memberId'] );
		}
		else
		{
			$member = \IPS\Member::loggedIn();
		}
		
		/* Can we use this? */
		if ( !$member->member_id or !( ( $member->failed_mfa_attempts >= \IPS\Settings::i()->security_questions_tries and \IPS\Settings::i()->mfa_lockout_behaviour == 'email' ) or in_array( 'email', explode( ',', \IPS\Settings::i()->mfa_forgot_behaviour ) ) ) )
		{
			\IPS\Output::i()->error( 'no_module_permission', '2C122/L', 403, '' );
		}
		
		/* If we have an existing validation record, we can just reuse it */
		$sendEmail = TRUE;
		try
		{
			$existing = \IPS\Db::i()->select( array( 'vid', 'email_sent' ), 'core_validating', array( 'member_id=? AND forgot_security=1', $member->member_id ) )->first();
			$vid = $existing['vid'];
			
			/* If we sent an email within the last 15 minutes, don't send another one otherwise someone could be a nuisence */
			if ( $existing['email_sent'] and $existing['email_sent'] > ( time() - 900 ) )
			{
				$sendEmail = FALSE;
			}
			else
			{
				\IPS\Db::i()->update( 'core_validating', array( 'email_sent' => time() ), array( 'vid=?', $vid ) );
			}
		}
		catch ( \UnderflowException $e )
		{
			$vid = md5( $member->members_pass_hash . \IPS\Login::generateRandomString() );

			\IPS\Db::i()->insert( 'core_validating', array(
				'vid'         		=> $vid,
				'member_id'   		=> $member->member_id,
				'entry_date'  		=> time(),
				'forgot_security'   => 1,
				'ip_address'  		=> \IPS\Request::i()->ipAddress(),
				'email_sent'  		=> time(),
			) );
		}
					
		/* Send email */
		if ( $sendEmail )
		{
			\IPS\Email::buildFromTemplate( 'core', 'mfaRecovery', array( $member, $vid ), \IPS\Email::TYPE_TRANSACTIONAL )->send( $member );
			$message = "mfa_recovery_email_sent";
		}
		else
		{
			$message = "mfa_recovery_email_already_sent";
		}
		
		/* Show confirmation page with further instructions */
		\IPS\Output::i()->sidebar['enabled'] = FALSE;
		\IPS\Output::i()->bodyClasses[] = 'ipsLayout_minimal';
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('mfa_account_recovery');
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'system' )->mfaAccountRecovery( $message );
	}
	
	/**
	 * Validate MFA Email Recovery
	 *
	 * @return	void
	 */
	protected function mfarecoveryvalidate()
	{
		/* Validate */
		try
		{
			$record = \IPS\Db::i()->select( '*', 'core_validating', array( 'vid=? AND member_id=? AND forgot_security=1', \IPS\Request::i()->vid, \IPS\Request::i()->mid ) )->first();
		}
		catch ( \UnderflowException $e )
		{
			\IPS\Output::i()->error( 'mfa_recovery_no_validation_key', '2C122/K', 410, '' );
		}
		
		/* Remove all MFA */
		$member = \IPS\Member::load( $record['member_id'] );
		foreach ( \IPS\MFA\MFAHandler::handlers() as $key => $handler )
		{
			$handler->disableHandlerForMember( $member );
		}
		$member->failed_mfa_attempts = 0;
		$member->save();
		
		/* Delete validating record  */
		\IPS\Db::i()->delete( 'core_validating', array( 'member_id=? AND forgot_security=1', $member->member_id ) );
		
		/* Redirect */
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal( '' ) );
	}
	
	/**
	 * Username
	 *
	 * @return	string
	 */
	protected function _username()
	{
		/* Check they have permission to change their username */
		if( !\IPS\Member::loggedIn()->group['g_dname_changes'] )
		{
			\IPS\Output::i()->error( 'username_err_nochange', '1C122/4', 403, '' );
		}
				
		if ( \IPS\Member::loggedIn()->group['g_displayname_unit'] )
		{
			if ( \IPS\Member::loggedIn()->group['gbw_displayname_unit_type'] )
			{
				if ( \IPS\Member::loggedIn()->joined->diff( \IPS\DateTime::create() )->days < \IPS\Member::loggedIn()->group['g_displayname_unit'] )
				{
					\IPS\Output::i()->error(
						\IPS\Member::loggedIn()->language()->addToStack( 'username_err_days', FALSE, array( 'sprintf' => array(
						\IPS\Member::loggedIn()->joined->add(
							new \DateInterval( 'P' . \IPS\Member::loggedIn()->group['g_displayname_unit'] . 'D' )
						)->localeDate()
						), 'pluralize' => array( \IPS\Member::loggedIn()->group['g_displayname_unit'] ) ) ),
					'1C122/5', 403, '' );
				}
			}
			else
			{
				if ( \IPS\Member::loggedIn()->member_posts < \IPS\Member::loggedIn()->group['g_displayname_unit'] )
				{
					\IPS\Output::i()->error( 
						\IPS\Member::loggedIn()->language()->addToStack( 'username_err_posts' , FALSE, array( 'sprintf' => array(
						( \IPS\Member::loggedIn()->group['g_displayname_unit'] - \IPS\Member::loggedIn()->member_posts )
						), 'pluralize' => array( \IPS\Member::loggedIn()->group['g_displayname_unit'] ) ) ),
					'1C122/6', 403, '' );
				}
			}
		}
		
		/* How many changes */
		$nameCount = \IPS\Db::i()->select( 'COUNT(*) as count, MIN(dname_date) as min_date', 'core_dnames_change', array(
			'dname_member_id=? AND dname_date>?',
			\IPS\Member::loggedIn()->member_id,
			\IPS\DateTime::create()->sub( new \DateInterval( 'P' . \IPS\Member::loggedIn()->group['g_dname_date'] . 'D' ) )->getTimestamp()
		) )->first();

		if ( \IPS\Member::loggedIn()->group['g_dname_changes'] != -1 and $nameCount['count'] >= \IPS\Member::loggedIn()->group['g_dname_changes'] )
		{
			return \IPS\Theme::i()->getTemplate( 'system' )->settingsUsernameLimitReached( \IPS\Member::loggedIn()->language()->addToStack('username_err_limit', FALSE, array( 'sprintf' => array( \IPS\Member::loggedIn()->group['g_dname_date'] ), 'pluralize' => array( \IPS\Member::loggedIn()->group['g_dname_changes'] ) ) ) );
		}
		else
		{
			/* Build form */
			$form = new \IPS\Helpers\Form;
			$form->class = 'ipsForm_collapseTablet';
			$form->add( new \IPS\Helpers\Form\Text( 'new_username', '', TRUE, array( 'accountUsername' => \IPS\Member::loggedIn() ) ) );
						
			/* Handle submissions */
			if ( $values = $form->values() )
			{
				$oldName = \IPS\Member::loggedIn()->name;
				
				foreach ( \IPS\Login::handlers( TRUE ) as $handler )
				{
					/* We cannot update our username in some login handlers, that's ok */
					try
					{
						$handler->changeUsername( \IPS\Member::loggedIn(), $oldName, $values['new_username'] );
					}
					catch( \BadMethodCallException $e ){}
				}

				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=username', 'front', 'settings' ), 'username_changed' );
			}
		}

		return \IPS\Theme::i()->getTemplate( 'system' )->settingsUsername( $form, $nameCount['count'], \IPS\Member::loggedIn()->group['g_dname_changes'], $nameCount['min_date'] ? \IPS\DateTime::ts( $nameCount['min_date'] ) : \IPS\Member::loggedIn()->joined, \IPS\Member::loggedIn()->group['g_dname_date'] );
	}
	
	/**
	 * Signature
	 *
	 * @return	string
	 */
	protected function _signature()
	{
		/* Check they have permission to change their signature */
		$sigLimits = explode( ":", \IPS\Member::loggedIn()->group['g_signature_limits']);
		
		if( !\IPS\Settings::i()->signatures_enabled && !$sigLimits[0] )
		{
			\IPS\Output::i()->error( 'signatures_disabled', '2C122/C', 403, '' );
		}
		
		/* Check limits */
		if ( \IPS\Member::loggedIn()->group['g_sig_unit'] )
		{
			/* Days */
			if ( \IPS\Member::loggedIn()->group['gbw_sig_unit_type'] )
			{
				if ( \IPS\Member::loggedIn()->joined->diff( \IPS\DateTime::create() )->days < \IPS\Member::loggedIn()->group['g_sig_unit'] )
				{
					\IPS\Output::i()->error( \IPS\Member::loggedIn()->language()->pluralize(
							sprintf(
									\IPS\Member::loggedIn()->language()->get('sig_err_days'),
									\IPS\Member::loggedIn()->joined->add(
											new \DateInterval( 'P' . \IPS\Member::loggedIn()->group['g_sig_unit'] . 'D' )
									)->localeDate()
							), array( \IPS\Member::loggedIn()->group['g_sig_unit'] ) ),
							'1C122/D', 403, '' );
				}
			}
			/* Posts */
			else
			{
				if ( \IPS\Member::loggedIn()->member_posts < \IPS\Member::loggedIn()->group['g_sig_unit'] )
				{
					\IPS\Output::i()->error( \IPS\Member::loggedIn()->language()->pluralize(
							sprintf(
									\IPS\Member::loggedIn()->language()->get('sig_err_posts'),
									( \IPS\Member::loggedIn()->group['g_sig_unit'] - \IPS\Member::loggedIn()->member_posts )
							), array( \IPS\Member::loggedIn()->group['g_sig_unit'] ) ),
							'1C122/E', 403, '' );
				}
			}
		}
	
		/* Build form */
		$form = new \IPS\Helpers\Form;
		$form->class = 'ipsForm_collapseTablet';
		$form->add( new \IPS\Helpers\Form\YesNo( 'view_sigs', \IPS\Member::loggedIn()->members_bitoptions['view_sigs'], FALSE ) );
		$form->add( new \IPS\Helpers\Form\Editor( 'signature', \IPS\Member::loggedIn()->signature, FALSE, array( 'app' => 'core', 'key' => 'Signatures', 'autoSaveKey' => "frontsig-" .\IPS\Member::loggedIn()->member_id, 'attachIds' => array( \IPS\Member::loggedIn()->member_id ) ) ) );
		
		/* Handle submissions */
		if ( $values = $form->values() )
		{
			if( $values['signature'] )
			{
				/* Check Limits */
				$signature = new \IPS\Xml\DOMDocument( '1.0', 'UTF-8' );
				$signature->loadHTML( \IPS\Xml\DOMDocument::wrapHtml( $values['signature'] ) );
				
				$errors = array();
				
				/* Links */
				if ( is_numeric( $sigLimits[4] ) and $signature->getElementsByTagName('a')->length > $sigLimits[4] )
				{
					$errors[] = \IPS\Member::loggedIn()->language()->addToStack('sig_num_links_exceeded');
				}

				/* Number of Images */
				if ( is_numeric( $sigLimits[1] ) and $signature->getElementsByTagName('img')->length > 0 )
				{
					$imageCount = 0;
					foreach ( $signature->getElementsByTagName('img') as $img )
					{
						if( !$img->hasAttribute("data-emoticon") )
						{
							$imageCount++;
						}
					}
					if( $imageCount > $sigLimits[1] )
					{
						$errors[] = \IPS\Member::loggedIn()->language()->addToStack('sig_num_images_exceeded');
					}
				}
				
				/* Size of images */
				if ( ( is_numeric( $sigLimits[2] ) and $sigLimits[2] ) or ( is_numeric( $sigLimits[3] ) and $sigLimits[3] ) )
				{
					foreach ( $signature->getElementsByTagName('img') as $image )
					{
						$attachId	= $image->getAttribute('data-fileid');
						$checkSrc	= TRUE;

						if( $attachId )
						{
							try
							{
								$attachment = \IPS\Db::i()->select( 'attach_location, attach_thumb_location', 'core_attachments', array( 'attach_id=?', $attachId ) )->first();
								$imageProperties = \IPS\File::get( 'core_Attachment', $attachment['attach_thumb_location'] ?: $attachment['attach_location'] )->getImageDimensions();

								$checkSrc	= FALSE;
							}
							catch( \UnderflowException $e ){}
						}

						if( $checkSrc )
						{
							$src = $image->getAttribute('src');
							\IPS\Output::i()->parseFileObjectUrls( $src );

							$imageProperties = @getimagesize( $src );
						}
						
						if( is_array( $imageProperties ) AND count( $imageProperties ) )
						{
							if( $imageProperties[0] > $sigLimits[2] OR $imageProperties[1] > $sigLimits[3] )
							{
								$errors[] = \IPS\Member::loggedIn()->language()->addToStack( 'sig_imagetoobig', FALSE, array( 'sprintf' => array( $src, $sigLimits[2], $sigLimits[3] ) ) );
							}
						}
						else
						{
							$errors[] = \IPS\Member::loggedIn()->language()->addToStack( 'sig_imagenotretrievable', FALSE, array( 'sprintf' => array( $src ) ) );
						}
					}
				}
				
				/* Lines */
				$preBreaks = 0;
				
				/* Make sure we are not trying to bypass the limit by using <pre> tags, which will not have <p> or <br> tags in its content */
				foreach( $signature->getElementsByTagName('pre') AS $pre )
				{
					$content = nl2br( trim( $pre->nodeValue ) );
					$preBreaks += count( explode( "<br />", $content ) );
				}
				
				if ( is_numeric( $sigLimits[5] ) and ( $signature->getElementsByTagName('p')->length + $signature->getElementsByTagName('br')->length + $preBreaks ) > $sigLimits[5] )
				{
					$errors[] = \IPS\Member::loggedIn()->language()->addToStack('sig_num_lines_exceeded');
				}
			}
			
			if( !empty( $errors ) )
			{
				$form->error = \IPS\Member::loggedIn()->language()->addToStack('sig_restrictions_exceeded');
				$form->elements['']['signature']->error = \IPS\Member::loggedIn()->language()->formatList( $errors );
				
				return \IPS\Theme::i()->getTemplate( 'system' )->settingsSignature( $form, $sigLimits );
			}
			
			\IPS\Member::loggedIn()->signature = $values['signature'];
			\IPS\Member::loggedIn()->members_bitoptions['view_sigs'] = $values['view_sigs'];
			
			\IPS\Member::loggedIn()->save();
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=core&module=system&controller=settings&area=signature', 'front', 'settings' ), 'signature_changed' );
		}

		return \IPS\Theme::i()->getTemplate( 'system' )->settingsSignature( $form, $sigLimits );
	}
	
	/**
	 * Profile Sync
	 *
	 * @return	string
	 */
	protected function _profilesync()
	{
		$service = \IPS\Request::i()->service;
		$class = 'IPS\core\ProfileSync\\' . $service;
		if ( !class_exists( $class ) or !isset( $class::$loginKey ) )
		{
			\IPS\Output::i()->error( 'page_doesnt_exist', '2C122/B', 404, '' );
		}
				
		$obj = new $class( \IPS\Member::loggedIn() );
		
		if ( $obj->connected() )
		{
			if ( isset( \IPS\Request::i()->sync ) )
			{
				$obj->sync();
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=system&controller=settings&area=profilesync&service={$service}", 'front', "settings_{$service}" ), 'profilesync_synced' );
			}
			elseif ( isset( \IPS\Request::i()->disassociate ) )
			{
				/* CSRF check */
				\IPS\Session::i()->csrfCheck();

				/* Check they have another way of signing in */
				$isOkay = FALSE;
				foreach ( \IPS\Login::handlers() as $handler )
				{
					if ( $handler->key != $obj::$loginKey and $handler->canProcess( \IPS\Member::loggedIn() ) )
					{
						$isOkay = TRUE;
						break;
					}
				}
				if ( !$isOkay )
				{
					\IPS\Output::i()->error( \IPS\Member::loggedIn()->language()->addToStack( 'profilesync_cannot_disassociate', FALSE, array( 'sprintf' => array( \IPS\Member::loggedIn()->language()->addToStack("profilesync__{$service}") ) ) ), '1C122/I', 403, '' );
				}
				
				/* Do it */
				$obj->disassociate();
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=core&module=system&controller=settings&area=profilesync&service={$service}", 'front', "settings_{$service}" ), 'profile_disassociated' );
			}
						
			$serviceName = 'profilesync__' . $service;
			$headline = ( $obj->name() ) ? \IPS\Member::loggedIn()->language()->addToStack( 'profilesync_headline', FALSE, array( 'sprintf' => array( \IPS\Member::loggedIn()->language()->addToStack( $serviceName ), $obj->name() ) ) ) : \IPS\Member::loggedIn()->language()->addToStack( 'profilesync_headline_no_name', FALSE, array( 'sprintf' => array( \IPS\Member::loggedIn()->language()->addToStack( $serviceName ) ) ) );
			
			$settings = $obj->settings();
			
			$form = new \IPS\Helpers\Form;
			$form->class = 'ipsForm_vertical';
			if ( method_exists( $obj, 'photo' ) )
			{
				$form->add( new \IPS\Helpers\Form\Checkbox( 'profilesync_photo', $settings['photo'], FALSE, array( 'labelSprintf' => array( \IPS\Member::loggedIn()->language()->addToStack( $serviceName ) ) ) ) );
			}

			if ( method_exists( $obj, 'cover' ) )
			{
				$form->add( new \IPS\Helpers\Form\Checkbox( 'profilesync_cover', $settings['cover'], FALSE, array( 'labelSprintf' => array( \IPS\Member::loggedIn()->language()->addToStack( $serviceName ) ) ) ) );
			}
								
			if ( $obj->canImportStatus( \IPS\Member::loggedIn() ) )
			{
				$form->add( new \IPS\Helpers\Form\Checkbox( 'profilesync_status', $settings['status'] == 'import', FALSE, array( 'labelSprintf' => array( \IPS\Member::loggedIn()->language()->addToStack( $serviceName ) ) ) ) );
			}
						
			if ( $values = $form->values() )
			{
				$obj->save( $values );
			}
			
			$photo = ( method_exists( $obj, 'photo' ) ) ? $obj->photo() : NULL;
			return \IPS\Theme::i()->getTemplate( 'system' )->settingsProfileSync( $photo instanceof \IPS\File ? $photo->url : $photo, $headline, method_exists( $obj, 'status' ) ? $obj->status() : NULL, $form, $service, "profilesync__{$service}", ( $settings['photo'] or $settings['cover'] or $settings['status'] ) );
		}
		else
		{
			$login = \IPS\Login\LoginAbstract::load( $class::$loginKey );
			$url = \IPS\Http\Url::internal( "app=core&module=system&controller=settings&area=profilesync&service={$service}", 'front', "settings_{$service}" );
			
			if ( \IPS\Request::i()->loginProcess )
			{
				try
				{
					$login->authenticate( $url, \IPS\Member::loggedIn() );
					\IPS\Output::i()->redirect( $url );
				}
				catch ( \IPS\Login\Exception $e ) { }
			}
			
			return \IPS\Theme::i()->getTemplate( 'system' )->settingsProfileSyncLogin( $login->loginForm( $url, TRUE ),  "profilesync__{$service}" );
		}
	}
	
	/**
	 * Disable All Signatures
	 *
	 * @return	void
	 */
	protected function toggleSigs()
	{
		if ( !\IPS\Settings::i()->signatures_enabled )
		{
			\IPS\Output::i()->error( 'signatures_disabled', '2C122/F', 403, '' );
		}
			
		\IPS\Session::i()->csrfCheck();
			
		if ( \IPS\Member::loggedIn()->members_bitoptions['view_sigs'] )
		{
			\IPS\Member::loggedIn()->members_bitoptions['view_sigs'] = 0;
		}
		else
		{
			\IPS\Member::loggedIn()->members_bitoptions['view_sigs'] = 1;
		}
		
		\IPS\Member::loggedIn()->save();
		
		if ( \IPS\Request::i()->isAjax() )
		{
			\IPS\Output::i()->json( 'OK' );
		}
		
		$redirectUrl = ( !empty( $_SERVER['HTTP_REFERER'] ) ) ? \IPS\Http\Url::external( $_SERVER['HTTP_REFERER'] ) : \IPS\Http\Url::internal( "app=core&module=system&controller=settings", 'front', 'settings' );
		\IPS\Output::i()->redirect( $redirectUrl, 'signature_pref_toggled' );
	}
}
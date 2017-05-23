<?php
/**
 * @brief		Stripe Pay Out Gateway
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Nexus
 * @since		7 Apr 2014
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\nexus\Gateway\Stripe;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Stripe Pay Out Gateway
 */
class _Payout extends \IPS\nexus\Payout
{
	/**
	 * ACP Settings
	 *
	 * @return	array
	 */
	public static function settings()
	{
		return array();
	}
	
	/**
	 * Payout Form
	 *
	 * @return	array
	 */
	public static function form()
	{
		return array();
	}
	
	/**
	 * Get data and validate
	 *
	 * @param	array	$values	Values from form
	 * @return	mixed
	 * @throws	\DomainException
	 */
	public function getData( array $values )
	{
		return NULL;	
	}
	
	/** 
	 * Process
	 *
	 * @return	void
	 * @throws	\Exception
	 */
	public function process()
	{
		throw new \DomainException('stripe_payout_deprecated');
	}
}
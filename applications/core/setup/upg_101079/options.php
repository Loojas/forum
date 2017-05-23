<?php
/**
 * @brief		Upgrader: Custom Upgrade Options
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @since		11 Nov 2016
 * @version		SVN_VERSION_NUMBER
 */

$options = array();
$options[] = new \IPS\Helpers\Form\YesNo( 'diagnostics_reporting', FALSE );
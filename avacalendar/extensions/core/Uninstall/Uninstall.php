<?php
/**
 * @brief		Uninstall callback
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	Ava Calendar
 * @since		02 May 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\avacalendar\extensions\core\Uninstall;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Uninstall callback
 */
class _Uninstall
{
	/**
	 * Code to execute before the application has been uninstalled
	 *
	 * @param	string	$application	Application directory
	 * @return	array
	 */
	public function preUninstall( $application )
	{
	}

	/**
	 * Code to execute after the application has been uninstalled
	 *
	 * @param	string	$application	Application directory
	 * @return	array
	 */
	public function postUninstall( $application )
	{
		\IPS\Db::i()->dropColumn( 'calendar_events', 'event_ava_won');
		\IPS\Db::i()->dropColumn( 'calendar_events', 'event_ava_position');
		\IPS\Db::i()->dropColumn( 'calendar_events', 'event_ava_meeting');
	}

	/**
	 * Code to execute when other applications are uninstalled
	 *
	 * @param	string	$application	Application directory
	 * @return	void
	 */
	public function onOtherAppUninstall( $application )
	{
	}

}
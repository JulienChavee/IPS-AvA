<?php
/**
 * @brief		avaevent Widget
 * @author		<a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) 2001 - 2016 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/legal/standards/
 * @package		IPS Community Suite
 * @subpackage	avacalendar
 * @since		21 Apr 2016
 * @version		SVN_VERSION_NUMBER
 */

namespace IPS\avacalendar\widgets;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * avaevent Widget
 */
class _avaevent extends \IPS\Widget
{
	/**
	 * @brief	Widget Key
	 */
	public $key = 'avaevent';
	
	/**
	 * @brief	App
	 */
	public $app = 'avacalendar';
		
	/**
	 * @brief	Plugin
	 */
	public $plugin = '';
	
	/**
	 * Initialise this widget
	 *
	 * @return void
	 */ 
	public function init()
	{
		// Use this to perform any set up and to assign a template that is not in the following format:
		// $this->template( array( \IPS\Theme::i()->getTemplate( 'widgets', $this->app, 'front' ), $this->key ) );
		// If you are creating a plugin, uncomment this line:
		// $this->template( array( \IPS\Theme::i()->getTemplate( 'plugins', 'core', 'global' ), $this->key ) );
		// And then create your template at located at plugins/<your plugin>/dev/html/avaevent.phtml
		
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'avacalendar.css', 'avacalendar', 'front' ) );
		
		parent::init();
	}
	
	/**
	 * Specify widget configuration
	 *
	 * @param	null|\IPS\Helpers\Form	$form	Form object
	 * @return	null|\IPS\Helpers\Form
	 */
	public function configuration( &$form=null )
	{
 		if ( $form === null )
 		{
	 		$form = new \IPS\Helpers\Form;
 		} 
		
		$form->add( new \IPS\Helpers\Form\YesNo( 'auto_hide', isset( $this->configuration['auto_hide'] ) ? $this->configuration['auto_hide'] : FALSE, FALSE ) );

		return $form;
 	} 
 	
 	 /**
 	 * Ran before saving widget configuration
 	 *
 	 * @param	array	$values	Values from form
 	 * @return	array
 	 */
 	public function preConfig( $values )
 	{
 		return $values;
 	}

	/**
	 * Render a widget
	 *
	 * @return	string
	 */
	public function render()
	{
		if( !\IPS\Member::loggedIn()->canAccessModule( \IPS\Application\Module::get( 'calendar', 'calendar' ) ) )
		{
			return '';
		}

		$offset	= \IPS\DateTime::create()->setTimezone( new \DateTimeZone( \IPS\Member::loggedIn()->timezone ) )->getOffset();

		$hour = date( 'H', time() + $offset );
      	$minute = date( 'i', time() + $offset );
      	$second = date( 's', time() + $offset );

		$now	= \IPS\calendar\Date::getDate( NULL, NULL, NULL, $hour, $minute, $second, $offset );

		$_today = \IPS\calendar\Date::getDate();

		/* Do we have a days ahead cutoff? */
		$endDate	= NULL;
		
		$calendars = array();
		$attack = -1;
		$defense = -1;

		if ( ! empty( \IPS\Settings::i()->avacalendar_acp_avaevent_attack ) )
		{
			$attack = $calendars[] = \IPS\Settings::i()->avacalendar_acp_avaevent_attack;
		}

		if( !empty( \IPS\Settings::i()->avacalendar_acp_avaevent_defense ) )
		{
			$defense = $calendars[] = \IPS\Settings::i()->avacalendar_acp_avaevent_defense;
		}

		/* How many are we displaying? */
		$count = NULL;

		$events = \IPS\calendar\Event::retrieveEvents( $_today, $endDate, ( $calendars === NULL ? NULL : !is_array( $calendars ) ? array( $calendars ) : $calendars ), $count, FALSE );

		// Les évènements dont la date de début est passée sont supprimés
		$timeUTC = \IPS\calendar\Date::getDate()->adjust( '-2 hours' );
		foreach( $events as $k => $v ) {
			if( strtotime( $v->start_date ) <= strtotime( $timeUTC->mysqlDatetime( TRUE ) ) )
				unset( $events[ $k ] );
		} 
		
		/* Auto hiding? */
		if( !count($events) AND isset( $this->configuration['auto_hide'] ) AND $this->configuration['auto_hide'] )
		{
			return '';
		}

		return $this->output( $events, $now, $attack, $defense );
	}
}
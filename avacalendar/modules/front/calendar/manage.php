<?php


namespace IPS\avacalendar\modules\front\calendar;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * manage
 */
class _manage extends \IPS\Dispatcher\Controller
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

	protected function manage() {
		//\IPS\avacalendar\Zone::_importNewZone( false );die();

		if( !\IPS\Member::loggedIn()->canAccessModule( \IPS\Application\Module::get( 'calendar', 'calendar' ) ) )
		{
			return '';
		}

		$offset	= \IPS\DateTime::create()->setTimezone( new \DateTimeZone( \IPS\Member::loggedIn()->timezone ) )->getOffset();

		$hour = date( 'H', time() + $offset );
      	$minute = date( 'i', time() + $offset );
      	$second = date( 's', time() + $offset );

		$now	= \IPS\calendar\Date::getDate( NULL, NULL, NULL, $hour, $minute, $second, $offset );

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

		/* Load member */
		if ( $member === NULL )
		{
			$member = \IPS\Member::loggedIn();
		}
		$where[] = array( \IPS\Db::i()->in( 'event_calendar_id', $calendars ) );

		$events = iterator_to_array( \IPS\calendar\Event::getItemsWithPermission( $where, 'event_start_date DESC', NULL, 'read', \IPS\Content\Hideable::FILTER_AUTOMATIC, 0, $member ) );

		\IPS\Output::i()->title = "Gestion des AvA";
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'front_calendar.js', 'avacalendar', 'front' ) );
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'manage' )->manage( $events, $now );
	}

	protected function add()
	{
		if( !\IPS\Member::loggedIn()->canAccessModule( \IPS\Application\Module::get( 'calendar', 'calendar' ) ) )
		{
			return '';
		}

		$form = new \IPS\Helpers\Form;

		$form->add( new \IPS\Helpers\Form\Node( 'avacalendar_calendar_manage_calendar', '' , TRUE, array(
			'class'					=> 'IPS\calendar\Calendar',
			'permissionCheck'		=> 'add',
		) ) );

		$form->add( new \IPS\Helpers\Form\Date( 'avacalendar_calendar_manage_date', '', TRUE, array( 'time' => true ) ) );
		$form->add( new \IPS\Helpers\Form\Text( 'avacalendar_calendar_manage_title', '', TRUE, array( 'autocomplete' => array( 'source' => 'app=avacalendar&module=calendar&controller=manage&do=getZone', 'maxItems' => 1, 'prefix' => FALSE ), 'placeholder' => 'avacalendar_calendar_manage_zone' ) ) );
		$form->add( new \IPS\Helpers\Form\Text( 'avacalendar_calendar_manage_meeting_hour', '', FALSE, array( 'placeholder' => \IPS\Member::loggedIn()->language()->addToStack( 'avacalendar_calendar_manage_hh_mm' ), 'regex' => '/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/') ) );
		$form->add( new \IPS\Helpers\Form\Text( 'avacalendar_calendar_manage_meeting_position', '', FALSE, array( 'placeholder' => \IPS\Member::loggedIn()->language()->addToStack( 'avacalendar_calendar_manage_xx_yy' ) ) ) );

		$zones = $this->getZones();

		if ( $values = $form->values() ) {
			//$zones = array("Village de Terrdala");
			//print_r($values['avacalendar_calendar_manage_title']);echo "<br>";print_r($zones);die();
			if( in_array( trim( $values['avacalendar_calendar_manage_title'] ), $zones ) ) {
				$event['event_container'] = ( $values[ 'avacalendar_calendar_manage_calendar' ] instanceof \IPS\Node\Model ) ? $values[ 'avacalendar_calendar_manage_calendar' ]->_id : intval( $values[ 'avacalendar_calendar_manage_calendar' ] );

				$event['event_title'] = $values['avacalendar_calendar_manage_title'];
				
				$event['event_dates']['start_date'] = $values['avacalendar_calendar_manage_date']->localeDate();
				$event['event_dates']['start_time'] = $values['avacalendar_calendar_manage_date']->format('H:i');

				$date = $values['avacalendar_calendar_manage_date'];
				$date->add(new \DateInterval('PT2H'));

				$event['event_dates']['end_date'] = $date->localeDate();
				$event['event_dates']['end_time'] = $date->format('H:i');

				$event['event_dates']['event_timezone'] = \IPS\Member::loggedIn()->timezone;

				$event['event_all_day'] = 0;
				$event['event_recurring'] = NULL;

				$event['event_cover_photo'] = NULL;
				$event['event_location'] = NULL;
				$event['event_tags'] = array();
				$event['event_rsvp'] = 0;

				$event['event_ava_meeting'] = $values[ 'avacalendar_calendar_manage_meeting_hour' ];
				$event['event_ava_position'] = $values[ 'avacalendar_calendar_manage_meeting_position' ];

				try {
					$event = \IPS\calendar\Event::createFromForm( $event, $values['avacalendar_calendar_manage_calendar'], FALSE );
					$event->ava_meeting = !empty( $values[ 'avacalendar_calendar_manage_meeting_hour' ] ) ? $values[ 'avacalendar_calendar_manage_meeting_hour' ] : null;
					$event->ava_position = $values[ 'avacalendar_calendar_manage_meeting_position' ];
					$event->save();
					\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=avacalendar' ), 'avacalendar_calendar_manage_ava_added' );
				}
				catch(\Exception $e) {
					\IPS\Log::i( \LOG_CRIT )->write( get_class( $e ) . "\n" . $e->getCode() . ": " . $e->getMessage() . "\n" . $e->getTraceAsString() );
					\IPS\Output::i()->error( 'avacalendar_calendar_manage_ava_error', '5A134/1' );
				}
			} else {
				\IPS\Output::i()->error( 'avacalendar_calendar_manage_ava_invalid_zone', '5A134/1' );
			}
		}

		\IPS\Output::i()->title = "Ajout d'un AvA";
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'submit' )->submitPage( $form->customTemplate( array( call_user_func_array( array( \IPS\Theme::i(), 'getTemplate' ), array( 'submit', 'avacalendar' ) ), 'submitForm' ) ) );
	}

	protected function getZone()
	{
		if( \IPS\Request::i()->isAjax() ) {
			$data = \IPS\Db::i()->select( '*', 'avacalendar_zones', array( 'name LIKE ?', '%'.\IPS\Request::i()->input.'%' ), 'id DESC' );

			foreach( $data as $k => $v ) {
				$results[] = array(
					'value' => 	$v['name'],
					'html' => $v['name']
				);
			}
			
			\IPS\Output::i()->json( $results );
		} else
			\IPS\Output::i()->json( array( 'res' => 'Bad request' ) );
	}

	protected function getZones()
	{
		$data = \IPS\Db::i()->select( '*', 'avacalendar_zones');

		foreach( $data as $k => $v ) {
			$results[] = trim( $v['name'] );
		}
			
		return $results;
	}

	protected function setResult()
	{
		if( \IPS\Request::i()->isAjax() ) {
			try {
				$event = \IPS\calendar\Event::load( \IPS\Request::i()->id );
			
				$event->ava_won = (int) \IPS\Request::i()->result;

				$event->save();

				\IPS\Output::i()->json( array( 'res' => 'ok' ) );
			} 
			catch( \Exception $e) {
				\IPS\Output::i()->json( array( 'res' => 'ko', 'debug' => $e->getMessage() ) );
			}
			
		} else
			\IPS\Output::i()->json( array( 'res' => 'Bad request' ) );
	}

	protected function edit()
	{
		$event = \IPS\calendar\Event::load( \IPS\Request::i()->id );

		$offset	= \IPS\DateTime::create()->setTimezone( new \DateTimeZone( \IPS\Member::loggedIn()->timezone ) )->getOffset();

		$hour = date( 'H', time() + $offset );
      	$minute = date( 'i', time() + $offset );
      	$second = date( 's', time() + $offset );

		$now	= \IPS\calendar\Date::getDate( NULL, NULL, NULL, $hour, $minute, $second, $offset );

		if( is_null( $event->ava_won ) && $event->nextOccurrence( $now, 'endDate' ) > $now ) {
			$form = new \IPS\Helpers\Form;

			$form->add( new \IPS\Helpers\Form\Node( 'avacalendar_calendar_manage_calendar', $event->container()->id , TRUE, array(
				'class'					=> 'IPS\calendar\Calendar',
				'permissionCheck'		=> 'add',
			) ) );

			$form->add( new \IPS\Helpers\Form\Date( 'avacalendar_calendar_manage_date', \IPS\calendar\Date::parseTime( $event->start_date, $event->all_day ? FALSE : TRUE ), TRUE, array( 'time' => true ) ) );
			$form->add( new \IPS\Helpers\Form\Text( 'avacalendar_calendar_manage_title', $event->title, TRUE, array( 'autocomplete' => array( 'source' => 'app=avacalendar&module=calendar&controller=manage&do=getZone', 'maxItems' => 1, 'prefix' => FALSE ), 'placeholder' => 'avacalendar_calendar_manage_zone' ) ) );
			$form->add( new \IPS\Helpers\Form\Text( 'avacalendar_calendar_manage_meeting_hour', mb_substr( $event->ava_meeting, 0, -3 ), FALSE, array( 'placeholder' => \IPS\Member::loggedIn()->language()->addToStack( 'avacalendar_calendar_manage_hh_mm' ), 'regex' => '/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/') ) );
			$form->add( new \IPS\Helpers\Form\Text( 'avacalendar_calendar_manage_meeting_position', $event->ava_position, FALSE, array( 'placeholder' => \IPS\Member::loggedIn()->language()->addToStack( 'avacalendar_calendar_manage_xx_yy' ) ) ) );

			if ( $values = $form->values() ) {

				$date = $values['avacalendar_calendar_manage_date'];
				$date->setTimezone( new \DateTimeZone( 'Europe/London' ) );

				$event->start_date = $date->format( 'Y-m-d H:i' );

				$date->add(new \DateInterval('PT2H'));

				$event->end_date = $date->format( 'Y-m-d H:i' );

				//print_r($event);die();

				$event->ava_position = $values[ 'avacalendar_calendar_manage_meeting_position' ];
				$event->ava_meeting = !empty( $values[ 'avacalendar_calendar_manage_meeting_hour' ] ) ? $values[ 'avacalendar_calendar_manage_meeting_hour' ] : null;
				$event->title = $values[ 'avacalendar_calendar_manage_title' ];

				try {
					$event->save();

					\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=avacalendar' ), 'avacalendar_calendar_manage_ava_updated' );
				}
				catch( \Exception $e ) {
					\IPS\Output::i()->error( 'avacalendar_calendar_manage_ava_error', '5A207/1' );
				}
			}

			\IPS\Output::i()->title = "Édition d'un AvA";
			\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate( 'submit' )->editPage( $form->customTemplate( array( call_user_func_array( array( \IPS\Theme::i(), 'getTemplate' ), array( 'submit', 'avacalendar' ) ), 'submitForm' ) ) );
		} else {
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=avacalendar' ), 'avacalendar_calendar_manage_ava_already_finished' );
		}
	}
}
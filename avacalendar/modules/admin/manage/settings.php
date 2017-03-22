<?php


namespace IPS\avacalendar\modules\admin\manage;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * settings
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
		\IPS\Dispatcher::i()->checkAcpPermission( 'settings_manage' );
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
		// This is the default method if no 'do' parameter is specified
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('avacalendar_acp_settings_title');

		$form = new \IPS\Helpers\Form;

		$form->add( new \IPS\Helpers\Form\Node( 'avacalendar_acp_avaevent_attack', isset( \IPS\Settings::i()->avacalendar_acp_avaevent_attack ) ? \IPS\Settings::i()->avacalendar_acp_avaevent_attack : 0, FALSE, array(
			'class'           => '\IPS\calendar\Calendar',
			'permissionCheck' => 'view',
			'multiple'        => false
		) ) );

		$form->add( new \IPS\Helpers\Form\Node( 'avacalendar_acp_avaevent_defense', isset( \IPS\Settings::i()->avacalendar_acp_avaevent_defense ) ? \IPS\Settings::i()->avacalendar_acp_avaevent_defense : 0, FALSE, array(
			'class'           => '\IPS\calendar\Calendar',
			'permissionCheck' => 'view',
			'multiple'        => false
		) ) );

		if ( $values = $form->values() )
		{
			if ( isset( $values['avacalendar_acp_avaevent_attack'] ) )
	 			$values['avacalendar_acp_avaevent_attack'] = $values['avacalendar_acp_avaevent_attack']->id;

	 		if ( isset( $values['avacalendar_acp_avaevent_defense'] ) )
	 			$values['avacalendar_acp_avaevent_defense'] = $values['avacalendar_acp_avaevent_defense']->id;

 			$form->saveAsSettings();

			\IPS\Session::i()->log( 'avacalendar_acp_log_settings' );
		}

		\IPS\Output::i()->output = $form;
	}
	
	// Create new methods with the same name as the 'do' parameter which should execute it
}
<?php

namespace IPS\avacalendar;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}


class _Zone
{
	public function _importNewZone( $all = true ) {
		try {
			$handle = fopen( dirname(__FILE__).DIRECTORY_SEPARATOR."zones.txt", "r" );
			if( $handle ) {
				while( ( $line = fgets( $handle ) ) !== false ) {
					if( preg_match( '/^(?P<action>\+|\-|\~)?\s?(?P<value>.*)$/',  $line, $matches ) ) {
						try {
							switch( $matches[ 'action' ] ) {
								case '+':
									\IPS\Db::i()->insert( 'avacalendar_zones', array( 'name' => $matches[ 'value' ] ) );
									break;
								case '-':
									\IPS\Db::i()->delete( 'avacalendar_zones', array( 'name=?', $matches[ 'value' ] ) );
									break;
								case '~':
									// TODO
									break;
								case '':
									if( $all ) {
										\IPS\Db::i()->insert( 'avacalendar_zones', array( 'name' => $matches[ 'value' ] ) );
									}
									break;
							}
						} catch( \Exception $e ) {
							echo $e->getMessage();
						}
					}
				}

				fclose( $handle );
			} else {
				// error opening the file.
			}
		}
		catch( \Exception $e ) {

		} 
	}
}
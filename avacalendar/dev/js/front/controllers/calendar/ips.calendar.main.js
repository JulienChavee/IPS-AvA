;( function($, _, undefined){
	"use strict";

	ips.controller.register('avacalendar.setResult', {

		_ajaxObj: null,

		initialize: function () {
			this.on( 'click', '[data-setResult]', this.setResult );
		},

		setResult: function (e) {
			e.preventDefault();
			
			var self = this;
			var link = ips.getSetting( 'baseURL' ) + '?app=avacalendar&module=calendar&controller=manage&do=setResult'
			var id = $( e.currentTarget ).data( 'id' );
			var result = $( e.currentTarget ).data( 'result' );

			// Cancel any current requests
			if( this._ajaxObj && _.isFunction( this._ajaxObj.abort ) ){
				this._ajaxObj.abort();
			}

			this._ajaxObj = ips.getAjax()( link, {
				type: 'post',
				dataType: 'json',
				data: {
					id: id,
					result: result
				}
			})
				.done( function (response) {
					if( response.res == "ok" ) {
						self.scope.remove();
						if( result )
							$( '.avaResult' + id ).removeClass( 'ipsBadge_new ipsBadge_intermediary' ).addClass( 'ipsBadge_positive' ).text( ips.getString('avacalendar_ava_won') );
						else
							$( '.avaResult' + id ).removeClass( 'ipsBadge_new ipsBadge_intermediary' ).addClass( 'ipsBadge_negative' ).text( ips.getString('avacalendar_ava_lost') );
					} else
						Debug.log( response.debug );
				});
		}
	});
}(jQuery, _));
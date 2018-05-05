<?php
//API Calling Functions For Different Gateways
function MSGWOW_sms_api_func( $MSGWOW_sms_settings, $telefono, $mensaje ) {
	switch ( $MSGWOW_sms_settings['servicio'] ) {
		//for msg91
		case "msg91":
			$argumentos['body'] = array( 
				'authkey' 	=> $MSGWOW_sms_settings['clave_msg91'],
				'mobiles' 	=> $telefono,
				'message' 	=> MSGWOW_sms_message( MSGWOW_sms_ct_code( $mensaje ) ),
				'sender' 	=> $MSGWOW_sms_settings['identificador_msg91'],
				'route' 	=> $MSGWOW_sms_settings['ruta_msg91']
			 );
			$respuesta = wp_remote_post( "http://control.msg91.com/sendhttp.php", $argumentos );
			break;
//For MSG Post Method 
		case "msgwow":
			$argumentos['body'] = array( 
				'authkey' 	=> $MSGWOW_sms_settings['clave_msgwow'],
				'mobiles' 	=> $telefono,
				'message' 	=> MSGWOW_sms_message( MSGWOW_sms_ct_code( $mensaje ) ),
				'sender' 	=> $MSGWOW_sms_settings['identificador_msgwow'],
				'route' 	=> $MSGWOW_sms_settings['ruta_msgwow'],
				'country'  => $MSGWOW_sms_settings['servidor_msgwow']
			 );
			$respuesta = wp_remote_post( "http://my.msgwow.com/api/sendhttp.php", $argumentos );
			break;
}
	if ( isset( $MSGWOW_sms_settings['debug'] ) && $MSGWOW_sms_settings['debug'] == "1" && isset( $MSGWOW_sms_settings['campo_debug'] ) ) {
		$correo	= __( 'Mobile number:', 'woocommerce-MSGWOW-sms-notifications' ) . "\r\n" . $telefono . "\r\n\r\n";
		$correo	.= __( 'Message: ', 'woocommerce-MSGWOW-sms-notifications' ) . "\r\n" . $mensaje . "\r\n\r\n"; 
		$correo	.= __( 'Gateway answer: ', 'woocommerce-MSGWOW-sms-notifications' ) . "\r\n" . print_r( $respuesta, true );
		wp_mail( $MSGWOW_sms_settings['campo_debug'], 'WC - MSGWOW SMS Notifications', $correo, 'charset=UTF-8' . "\r\n" ); 
	}
}
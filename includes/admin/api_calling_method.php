<?php
//API Calling Functions For Different Gateways
function biz_sms_api_func( $biz_sms_settings, $telefono, $mensaje ) {
	switch ( $biz_sms_settings['servicio'] ) {
		//for msg91
		case "msg91":
			$argumentos['body'] = array( 
				'authkey' 	=> $biz_sms_settings['clave_msg91'],
				'mobiles' 	=> $telefono,
				'message' 	=> biz_sms_message( biz_sms_ct_code( $mensaje ) ),
				'sender' 	=> $biz_sms_settings['identificador_msg91'],
				'route' 	=> $biz_sms_settings['ruta_msg91']
			 );
			$respuesta = wp_remote_post( "http://control.msg91.com/sendhttp.php", $argumentos );
			break;
//For MSG Post Method 
		case "msgwow":
			$argumentos['body'] = array( 
				'authkey' 	=> $biz_sms_settings['clave_msgwow'],
				'mobiles' 	=> $telefono,
				'message' 	=> biz_sms_message( biz_sms_ct_code( $mensaje ) ),
				'sender' 	=> $biz_sms_settings['identificador_msgwow'],
				'route' 	=> $biz_sms_settings['ruta_msgwow'],
				'country'  => $biz_sms_settings['servidor_msgwow']
			 );
			$respuesta = wp_remote_post( "http://my.msgwow.com/api/sendhttp.php", $argumentos );
			break;
}
	if ( isset( $biz_sms_settings['debug'] ) && $biz_sms_settings['debug'] == "1" && isset( $biz_sms_settings['campo_debug'] ) ) {
		$correo	= __( 'Mobile number:', 'woocommerce-biz-sms-notifications' ) . "\r\n" . $telefono . "\r\n\r\n";
		$correo	.= __( 'Message: ', 'woocommerce-biz-sms-notifications' ) . "\r\n" . $mensaje . "\r\n\r\n"; 
		$correo	.= __( 'Gateway answer: ', 'woocommerce-biz-sms-notifications' ) . "\r\n" . print_r( $respuesta, true );
		wp_mail( $biz_sms_settings['campo_debug'], 'WC - biz SMS Notifications', $correo, 'charset=UTF-8' . "\r\n" ); 
	}
}
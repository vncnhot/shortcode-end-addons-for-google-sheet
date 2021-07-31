<?php
/*
 * Utilities class for Google Sheet API
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
   exit;
}

/**
 * Utilities class - singleton class
 * @since 1.0
 */
class Gsheet_Utility {
    
    private function __construct() {
      // Do Nothing
    }


    /**
    * Get the singleton instance of the Utility class
    *
    * @return singleton instance of Utility
    */
   public static function instance() {

    static $instance = NULL;
    if ( is_null( $instance ) ) {
       $instance = new Gsheet_Utility();
    }
    return $instance;
    }


   /**
    * Utility function to get the current user's role
    *
    * @since 1.0
    */
    public function get_current_user_role() {
        global $wp_roles;
        foreach ( $wp_roles->role_names as $role => $name ) :
           if ( current_user_can( $role ) )
              return $role;
        endforeach;
    }

    /**
    * Utility function to get the current user's role
    *
    * @since 1.0
    */
    public static function Gsheet_debug_log($error){
		try{	
			if( ! is_dir( iFnt_Gsheet_DIR.'logs' ) ){
				mkdir( iFnt_Gsheet_DIR . 'logs', 0755, true );
			}
		} catch (Exception $e) {

		}
		try{
			$log = fopen( iFnt_Gsheet_DIR . "logs/log.txt", 'a');
			if ( is_array( $error ) ) {
            fwrite($log, print_r(date_i18n( 'j F Y H:i:s', current_time( 'timestamp' ) )." \t PHP ".phpversion(), TRUE));
            fwrite( $log, print_r($error, TRUE));   
         } else {
			$result = fwrite($log, print_r(date_i18n( 'j F Y H:i:s', current_time( 'timestamp' ) )." \t PHP ".phpversion()." \t $error \r\n", TRUE));
         }
			fclose( $log );
		} catch (Exception $e) {
			
		}
    }

}
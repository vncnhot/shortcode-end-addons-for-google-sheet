<?php

if ( !defined( 'ABSPATH' ) ) {
   exit;
}

include_once ( plugin_dir_path( __FILE__ ) . 'lib/vendor/autoload.php' );

class Get_API_googlesheet {

    private $get_token;
    private $get_spreadsheet;
    private $get_worksheet;

    const clientId = '526753838746-sks75u6f8716gd4oi69h99g73f6v1gi3.apps.googleusercontent.com';
    const clientSecret = '1Q7LElS-Tc_TskvZ05W1JOMu';
    const redirect = 'urn:ietf:wg:oauth:2.0:oob';

    private static $instance;

    public function __construct() {
      
    }
    
   public static function set_instance( Google_Client $instance = null ) {
      self::$instance = $instance;
   }

   public static function get_instance() {
      if ( is_null( self::$instance ) ) {
         throw new LogicException( "Invalid Client" );
      }

      return self::$instance;
   }

   // get token on call
   public static function preauth_code( $access_code ) {
    $client = new Google_Client();
    $client->setClientId( Get_API_googlesheet::clientId );
    $client->setClientSecret( Get_API_googlesheet::clientSecret );
    $client->setRedirectUri( Get_API_googlesheet::redirect );
    $client->setScopes( Google_Service_Sheets::SPREADSHEETS );
    $client->setScopes( Google_Service_Drive::DRIVE_METADATA_READONLY );
    $client->setAccessType( 'offline' );
    $client->fetchAccessTokenWithAuthCode( $access_code );
    $tokenData = $client->getAccessToken();

    Get_API_googlesheet::update_token( $tokenData );
    }

    public static function update_token( $tokenData ) {
        $tokenData['expire'] = time() + intval( $tokenData['expires_in'] );
        try {
           $tokenJson = json_encode( $tokenData );
           update_option( 'Gsheet_token_active', $tokenJson );
        } catch ( Exception $e ) {
            Gsheet_Utility::Gsheet_debug_log( "Token write fail! - " . $e->getMessage() );
        }
    }

    public function auth() {
        $tokenData = json_decode( get_option( 'Gsheet_token_active' ), true );
        if ( !isset( $tokenData['refresh_token'] ) || empty( $tokenData['refresh_token'] ) ) {
        //throw new LogicException( "Auth, Invalid OAuth2 access token" );
          // exit();
        } else {
  
        try {
           $client = new Google_Client();
           $client->setClientId( Get_API_googlesheet::clientId );
           $client->setClientSecret( Get_API_googlesheet::clientSecret );
           $client->setScopes( Google_Service_Sheets::SPREADSHEETS );
           $client->setScopes( Google_Service_Drive::DRIVE_METADATA_READONLY );
           $client->refreshToken( $tokenData['refresh_token'] );
           $client->setAccessType( 'offline' );
           Get_API_googlesheet::update_token( $tokenData );
  
           self::set_instance( $client );
        } catch ( Exception $e ) {
           throw new LogicException( "Auth, Error fetching OAuth2 access token, message: " . $e->getMessage() );
           exit();
        }
      }
     }
     
   //preg_match is a key of error handle in this case
   public function set_spreadsheetId( $id ) {
      $this->get_spreadsheet = $id;
   }

   public function get_spreadsheetId() {

      return $this->get_spreadsheet;
   }

   public function set_workTabId( $id ) {
      $this->get_worksheet = $id;
   }

   public function get_workTabId() {
      return $this->get_worksheet;
   }

   public function add_row( $data ) {
    try {
       $client = self::get_instance();
       $service = new Google_Service_Sheets( $client );
       $spreadsheetId = $this->get_spreadsheetId();
       $work_sheets = $service->spreadsheets->get( $spreadsheetId );

       if ( !empty( $work_sheets ) && !empty( $data ) ) {
          foreach ( $work_sheets as $sheet ) {
             $properties = $sheet->getProperties();
             $sheet_id = $properties->getSheetId();

             $worksheet_id = $this->get_workTabId();

             if ( $sheet_id == $worksheet_id ) {
                $worksheet_id = $properties->getTitle();
                $worksheetCell = $service->spreadsheets_values->get( $spreadsheetId, $worksheet_id . "!1:1" );
                $insert_data = array();
                if ( isset( $worksheetCell->values[0] ) ) {
                   foreach ( $worksheetCell->values[0] as $k => $name ) {
                      if ( isset( $data[$name] ) && $data[$name] != '' ) {
                         $insert_data[] = $data[$name];
                      } else {
                         $insert_data[] = '';
                      }
                   }
                }
                
                /*RASHID*/
                  $tab_name = $worksheet_id;
                  $full_range = $tab_name."!A1:Z";
                  $response   = $service->spreadsheets_values->get( $spreadsheetId, $full_range );
                  $get_values = $response->getValues();
                  
                  if( $get_values) {
                      $row  = count( $get_values ) + 1;
                  }
                  else {
                      $row = 1;
                  }
                  $range = $tab_name."!A".$row.":Z"; 
                
                $range_new = $worksheet_id;

                // Create the value range Object
                $valueRange = new Google_Service_Sheets_ValueRange();

                // set values of inserted data
                $valueRange->setValues( ["values" => $insert_data ] );
                
                // Add two values
                // Then you need to add configuration
                $conf = ["valueInputOption" => "USER_ENTERED"];

                // append the spreadsheet(add new row in the sheet)
                $result = $service->spreadsheets_values->append( $spreadsheetId, $range, $valueRange, $conf );
             }
          }
       }
    } catch ( Exception $e ) {
       return null;
       exit();
    }
 }

        public function Gsheet_google_account_email() {		
            $google_account = get_option("cf7gf_email_account");
            
            if( $google_account ) {
                return $google_account;
            }
            else {
                
                $google_sheet = new Get_API_googlesheet();
                $google_sheet->auth();				 
                $email = $google_sheet->Gsheet_get_google_account_email();
                if ($email) {
                return $email;
                } else {
                  return esc_html(__( 'Lỗi! điều này sẽ khiến bạn không thể dùng được API. Hãy hủy và lấy lại Code mới để kích hoạt.', 'webnganh' ));
                }
            }		
        }
      	public function Gsheet_get_google_account_email() {		
		$google_account = $this->Gsheet_get_google_account();	
		
		if( $google_account ) {
			return $google_account->email;
		}
		else {
			return "";
		}
	}
	
	public function Gsheet_get_google_account() {		
	
		try {
			$client = $this->get_instance();
			
			if( ! $client ) {
				return false;
			}
			
			$service = new Google_Service_Oauth2($client);
			$user = $service->userinfo->get();			
		}
		catch (Exception $e) {
			Gsheet_Utility::Gsheet_debug_log( __METHOD__ . " Error in fetching user info: \n " . $e->getMessage() );
			return false;
		}
		
		return $user;
	}

}
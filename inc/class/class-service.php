<?php
/**
 * Service Google Sheet
 * @since 1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly
}

/**
 * Gsheet Service Class
 *
 * @since 1.0
 */
class Gsheet_Service {

	private $allowed_tags = array( 'text', 'email', 'url', 'tel', 'number', 'range', 'date', 'textarea', 'select', 'checkbox', 'radio', 'acceptance', 'quiz', 'file', 'hidden' );

	private $special_mail_tags = array( 'date', 'time', 'serial_number', 'remote_ip', 'user_agent', 'url', 'post_id', 'post_name', 'post_title', 'post_url', 'post_author', 'post_author_email', 'site_title', 'site_description', 'site_url', 'site_admin_email', 'user_login', 'user_email', 'user_display_name' ); 
	
	protected $gheet_up   = array();

    public function __construct() {
       // Get key API Google
      add_action( 'wp_ajax_get_key_token_api', array( $this, 'get_key_token_api' ) );
      add_action( 'wp_ajax_del_active_log', array( $this, 'del_active_log' ) );
       // Tabs new to contact form 7 editors panel
      add_filter( 'wpcf7_editor_panels', array( $this, 'cf7_Gsheet_editor_panels' ) );      

      add_action( 'wpcf7_after_save', array( $this, 'save_Gsheet_settings' ) );
      add_action( 'wpcf7_mail_sent', array( $this, 'save_to_google_sheets_in_cf7' ) );
    }
    public function del_active_log() {
      // nonce check
      check_ajax_referer('Gsheet-ajax-nonce', 'security');

      if ( get_option('Gsheet_key_code') !== '' ) {
         delete_option('Gsheet_key_code');
         delete_option('Gsheet_token_active');
         delete_option('Gsheet_active');

         wp_send_json_success();
      } else {
         wp_send_json_error();
      }
   }
  /**
    * Function - To send contact form data to google spreadsheet
    * @param object $form
    * @since 1.0
    */
    public function save_to_google_sheets_in_cf7( $form ) {
      
      $submission = WPCF7_Submission::get_instance();
      
      // get form data
      $form_id = $form->id();
      $form_data = get_post_meta( $form_id, 'Gsheet_settings' );
      $data = array();
    
      // if contact form sheet name and tab name is not empty than send data to spreedsheet
      if ( $submission && (! empty( $form_data[0]['Gsheet-name-cf7'] ) ) && (! empty( $form_data[0]['Gsheet-tab-name-cf7'] ) ) ) {
         $posted_data = $submission->get_posted_data();
	 
         // make sure the form ID matches the setting otherwise don't do anything
         try {
            include_once( iFnt_Gsheet_Root . "/inc/google-sheet-get.php" );
            $doc = new Get_API_googlesheet();
            $doc->auth();
            $doc->set_spreadsheetId( $form_data[0]['Gsheet-id-cf7'] );
            $doc->set_workTabId( $form_data[0]['Gsheet-tab-gid'] );
            // Special Mail Tags  
            $meta = array();
            if ( ! empty( $meta ) ) {
               $data["date"] = $meta["date"];
               $data["time"] = $meta["time"];
               $data["serial-number"] = $meta["serial_number"];
               $data["remote-ip"] = $meta["remote_ip"];
               $data["user-agent"] = $meta["user_agent"];
               $data["url"] = $meta["url"];
               $data["post-id"] = $meta["post_id"];
               $data["post-name"] = $meta["post_name"];
               $data["post-title"] = $meta["post_title"];
               $data["post-url"] = $meta["post_url"];
               $data["post-author"] = $meta["post_author"];
               $data["post-author-email"] = $meta["post_author_email"];
               $data["site-title"] = $meta["site_title"];
               $data["site-description"] = $meta["site_description"];
               $data["site-url"] = $meta["site_url"];
               $data["site-admin-email"] = $meta["site_admin_email"];
               $data["user-login"] = $meta["user_login"];
               $data["user-email"] = $meta["user_email"];
               $data["user-url"] = $meta["user_url"];
               $data["user-first-name"] = $meta["user_first_name"];
               $data["user-last-name"] = $meta["user_last_name"];
               $data["user-nickname"] = $meta["user_nickname"];
               $data["user-display-name"] = $meta["user_display_name"];
            }
            foreach ( $posted_data as $key => $value ) {
               if ( is_array( $value ) ) {
                  $data[ $key ] = implode( ', ', $value );
               } else {
                  $data[ $key ] = stripcslashes( $value );
                   }
            }
            $doc->add_row( $data );
         } catch ( Exception $e ) {
            $data['ERROR_MSG'] = $e->getMessage();
            $data['TRACE_STK'] = $e->getTraceAsString();
            Gsheet_Utility::Gsheet_debug_log( $data );
         }
      }
   }

    /**
    * AJAX function - get the token
    *
    * @since 1.0
    */
   public function get_key_token_api() {
      // nonce check_save_settings
      check_ajax_referer( 'Gsheet-ajax-nonce', 'security' );

      /* sanitize incoming data */
      $Code = sanitize_text_field( $_POST["code"] );

      update_option( 'Gsheet_key_code', $Code );

      if ( get_option( 'Gsheet_key_code' ) != '' ) {
         include_once( iFnt_Gsheet_Root . '/lib/google-sheet-get.php');
         Get_API_googlesheet::preauth_code( get_option( 'Gsheet_key_code' ) );
         update_option( 'Gsheet_active', 'valid' );
         wp_send_json_success();
      } else {
         update_option( 'Gsheet_active', 'invalid' );
         wp_send_json_error();
      }
   }


   public function save_Gsheet_settings( $post ) {
      $default = array(
         "Gsheet-name-cf7" => "",
         "Gsheet-id-cf7" => "",
         "Gsheet-tab-name-cf7" => "",
         "Gsheet-tab-gid-cf7" => ""
          
      );
      // sanitize the input data POST gsheet-cf7
      $get_code = sanitize_text_field( $_POST["gsheet-cf7"] );
      $sheet_data = isset( $get_code ) ? $get_code : $default;
      update_post_meta( $post->id(), 'Gsheet_settings', $sheet_data );
     
   }

    /**
    * Tabs new to contact form 7 editors panel
    * @since 1.0
    */
   public function cf7_Gsheet_editor_panels( $panels ) {
    if ( current_user_can( 'wpcf7_edit_contact_form' ) ) {
       $panels['Gsheets_tab'] = array(
          'title' => __( 'Google Sheets CF7', 'contact-form-7' ),
          'callback' => array( $this, 'cf7_editor_panel_Gsheet' )
       );
    }
    return $panels;
    }
       /*
    * Google sheet settings page  
    * @since 1.0
    */

   public function cf7_editor_panel_Gsheet( $post ) {
    $form_id = sanitize_text_field( $_GET['post'] );
    $form_data = get_post_meta( $form_id, 'Gsheet_settings' );
    ?>
     
         <div class="box-fields">
            <h2><?php echo esc_html( __( 'Google Sheet Settings', 'webnganh' ) ); ?></h2>
            <div class="box-info"><?php echo __( 'Đây là khung dành cho <b>Contact Form 7</b>.', 'webnganh'); ?> </div>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                        <label><?php echo esc_html( __( 'Tên Google Sheet', 'webnganh' ) ); ?></label>
                        </th>
                        <td> 
                           <input type="text" name="gsheet-cf7[Gsheet-name-cf7]" id="Gsheet-name" 
                      value="<?php echo ( isset( $form_data[0]['Gsheet-name-cf7'] ) ) ? esc_attr( $form_data[0]['Gsheet-name-cf7'] ) : ''; ?>"/>
                    <div class='faq-data'><b><?php echo esc_html( __( 'Lấy tên GSheet ở đâu?', 'webnganh' ) ); ?>:</b> <?php echo esc_html( __( 'Bạn lấy tên Gsheet ở trong Drive Google trong lúc tạo bảng tính và tên được đặt cho bảng tính đó.', 'webnganh' ) ); ?> </div>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                        <label><?php echo esc_html(__('ID Google Sheet', 'webnganh')); ?></label>
                        </th>
                        <td> 
                  <input type="text" name="gsheet-cf7[Gsheet-id-cf7]" id="Gsheet-id"
                         value="<?php echo ( isset($form_data[0]['Gsheet-id-cf7']) ) ? esc_attr($form_data[0]['Gsheet-id-cf7']) : ''; ?>"/>
                    <div class='faq-data'><b><?php echo esc_html(__('Lấy ID Google Sheet?', 'webnganh')); ?>:</b> <?php echo esc_html(__('ID được nằm ở đoạn đường dẫn chia sẻ File qua liên kết.', 'webnganh')); ?></div>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                        <label><?php echo esc_html( __( 'Tên Tab Google Sheet', 'webnganh' ) ); ?></label>
                        </th>
                        <td> 
                        <input type="text" name="gsheet-cf7[Gsheet-tab-name-cf7]" id="Gsheet-tab-name"
                              value="<?php echo ( isset( $form_data[0]['Gsheet-tab-name-cf7'] ) ) ? esc_attr( $form_data[0]['Gsheet-tab-name-cf7'] ) : ''; ?>"/>
                           <div class='faq-data'><b><?php echo esc_html( __( 'Tab name ở đâu ?', 'webnganh' ) ); ?>:</b> <?php echo esc_html( __( 'Mở Google Trang tính của bạn mà bạn muốn liên kết biểu mẫu liên hệ của mình. Bạn sẽ thấy tên tab ở cuối màn hình. Sao chép tên tab mà bạn muốn có mục nhập của biểu mẫu liên hệ.', 'webnganh' ) ); ?></div>
                     </td>
                  </tr>

                    <tr>
                        <th scope="row">
                        <?php echo esc_html(__('Gid Tab Google Sheet', 'webnganh')); ?></label>
                        </th>
                        <td> 
                  <input type="text" name="gsheet-cf7[Gsheet-tab-gid-cf7]" id="Gsheet-tab-gid"
                         value="<?php echo ( isset($form_data[0]['Gsheet-tab-gid-cf7']) ) ? esc_attr($form_data[0]['Gsheet-tab-gid-cf7']) : ''; ?>"/>
                  <div class='faq-data'><b><?php echo esc_html(__('Lấy Google Tab Id?', 'webnganh')); ?>:</b> <?php echo esc_html(__('Bạn có thể lấy Gid Tab từ URL trang tính của mình', 'webnganh')); ?></div>
                </td>
                  </tr>

                </tbody>
            </table>
         </div> 
	  <?php
   }



}

$gsheet_service = new Gsheet_Service();

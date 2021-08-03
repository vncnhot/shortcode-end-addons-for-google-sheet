<?php
/*
Plugin Name: ShortCode & Addons For Google Sheets 
Plugin URI: https://webnganh.vn
Description: Công cụ hỗ trợ API Google Sheet Row được chia sẻ bởi inFinity Technology
Contributors: webnganh, ifnt
Version: 1.0.2
Author: inFinity
Author URI: https://webnganh.vn/
Text Domain: webnganh
Tags: WebNganh.Vn, WebNganh.Com, iFnt.Vn
Tested up to: 5.7.2
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://webnganh.vn
*/
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
define('iFnt_Gsheet_Version', '1.0.2' );
define('iFnt_Gsheet_DB_Version', '1.0.2' );
define('iFnt_Gsheet_DIR', plugin_dir_path(__FILE__));
define('iFnt_Gsheet_URL', plugins_url('/', __FILE__));
define('iFnt_Gsheet_Root', dirname( __FILE__ ) );
define( 'iFnt_Gsheet_Base_Name', plugin_basename( __FILE__ ) );

if ( !class_exists( 'Gsheet_Utility' ) ) {
    include( iFnt_Gsheet_Root . '/inc/class/class-utility.php' );
 }
if ( !class_exists( 'Gsheet_Service' ) ) {
    include( iFnt_Gsheet_Root . '/inc/class/class-service.php' );
}
//Include Library Files
require_once iFnt_Gsheet_Root . '/inc/lib/vendor/autoload.php';

include_once( iFnt_Gsheet_Root . '/inc/google-sheet-get.php');


class iFnt_Gsheet_Free {
    /**
    *  Thêm dữ liệu hiển thị
    *  @since 1.0
    */
    public function __construct() {

      // Hiển thị menu con "Contact" > "Integration"
      add_action( 'admin_menu', array( $this, 'register_Gsheet_menu_pages' ) );
      add_action( 'init', array( $this, 'load_css_and_js_files_edit' ) );
      // Add custom link for our plugin
      add_filter( 'plugin_action_links_' . iFnt_Gsheet_Base_Name, array( $this, 'Gsheet_plugin_action_links' ) );
      add_action( 'admin_init', array( $this, 'autorun_on_upgrade' ) );
    }

    public function autorun_on_upgrade() {
        $plugin_options = get_site_option( 'google_sheet_info' );
        
       // if ($plugin_options['version'] <= "3.0") {
           //$this->upgrade_database_40();
       // }
  
        // update the version value
        $google_sheet_info = array(
           'version' => iFnt_Gsheet_Version,
           'db_version' => iFnt_Gsheet_DB_Version
        );
        update_site_option( 'google_sheet_info', $google_sheet_info );
     }

    /**
    * Khởi tạo menu phụ cho Contact Form 7
    * @since 1.0
    */
   public function register_Gsheet_menu_pages() {
    if ( current_user_can( 'wpcf7_edit_contact_forms' ) ) {
       $current_role = Gsheet_Utility::instance()->get_current_user_role();
       add_submenu_page( 'wpcf7', esc_html( __( 'Google Sheet Contact', 'webnganh' ) ), esc_html( __( 'Google Sheet Contact', 'webnganh' ) ), $current_role, 'Gsheet-config-API', array( $this, 'get_google_sheet_configuration' ) );
        }
    }

       /**
    * Add custom link for the plugin beside activate/deactivate links
    * @param array $links Array of links to display below our plugin listing.
    * @return array Amended array of links.    * 
    * @since 1.0
    */
   public function Gsheet_plugin_action_links( $links ) {
      // We shouldn't encourage editing our plugin directly.
      unset( $links['edit'] );

      // Add our custom links to the returned array value.
      return array_merge( array(
         '<a href="' . admin_url( 'admin.php?page=Gsheet-config-API' ) . '">' . esc_html( __( 'Cài đặt', 'webnganh' ) ) . '</a>'
              ), $links );
   }

    /**
    * Google Sheets page action.
    * This method is called when the menu item "Google Sheets" is clicked.
    * @since 1.0
    */
   public function get_google_sheet_configuration() {
        include( iFnt_Gsheet_DIR . "pages/google-sheet-settings.php" );
    }

    /**
    * Google Sheets page action.
    * This method is called when the menu item "Google Sheets" is clicked.
    *
    * @since 1.0
    */
   public function box_google_sheet_config() {
    ?>         	  
    
    <div class="wrap" style=" clear: both; width: 100%; "> 
        <h1><?php echo esc_html( __( 'Google Sheet API - ShortCode & Contact Form 7', 'webnganh' ) ); ?></h1>
        <div class="iFntBoxAPI">
            <h2 class="title"><?php echo esc_html( __( 'Google Sheets Get API', 'webnganh' ) ); ?></h2>
            <div class="Box-Input-API">
                <p>Nhấn <b>"Lấy Code"</b> để kích hoạt Form Google Sheet trong Google Drive. Bạn sao chép đoạn mã đó rồi gắn vào ô bên dưới sau đó lưu lại.<br/>
                <span class="loading-note-active">Loading....</span></p>
                <label><?php echo esc_html( __( 'Google Access Code', 'webnganh' ) ); ?></label>
                <?php if (!empty(get_option('Gsheet_token_active')) && get_option('Gsheet_token_active') !== "") { ?>
                <input type="text" name="gsheet-token" id="gsheet-token" value="" disabled placeholder="<?php echo esc_html(__('Đã được kích hoạt', 'webnganh')); ?>"/>
               <input type="button" name="del-active-log" id="del-active-log" value="<?php _e('Hủy kích hoạt', 'webnganh'); ?>" class="button button-primary" />
               <div class="about-del">Khi hủy kích hoạt, tất cả dữ liệu của bạn được lưu với xác thực sẽ bị xóa và bạn cần xác thực lại bằng tài khoản google của mình.</div>
               
                <?php } else { ?>
               <input type="text" name="gsheet-token" id="gsheet-token" value="" placeholder="<?php echo esc_html(__('Viết Code tại khung này', 'webnganh')); ?>"/>
                    <a href="https://accounts.google.com/o/oauth2/auth?access_type=offline&approval_prompt=force&client_id=526753838746-sks75u6f8716gd4oi69h99g73f6v1gi3.apps.googleusercontent.com&redirect_uri=urn%3Aietf%3Awg%3Aoauth%3A2.0%3Aoob&response_type=code&scope=https%3A%2F%2Fspreadsheets.google.com%2Ffeeds%2F+https://www.googleapis.com/auth/userinfo.email+https://www.googleapis.com/auth/drive.metadata.readonly" target="_blank" class="button">Lấy Code</a>
                     <?php } ?>
                     <p>
                     <?php if (empty(get_option('Gsheet_token_active'))) { ?>
                     <input type="button" name="save-access-code" id="save-access-code" value="<?php echo esc_html(__( 'Lưu mã code', 'webnganh' )); ?>"
                            class="button button-primary" />
                      <?php } ?>
                  </p>
                  <span class="validation-message" id="validation-message"></span>
                  <!-- set nonce -->
                  <input type="hidden" name="Gsheet-ajax-nonce" id="Gsheet-ajax-nonce" value="<?php echo wp_create_nonce( 'Gsheet-ajax-nonce' ); ?>" />

                  <?php
					$token = get_option('Gsheet_token_active');
					if ( ! empty( $token ) && $token !== "") {
						$google_sheet = new Get_API_googlesheet();		
						$email_account = $google_sheet->Gsheet_google_account_email(); 
						if( $email_account ) { ?>
							<p class="get-account"><?php printf( esc_html(__( 'Email đang kích hoạt: %s', 'webnganh' )), $email_account ); ?><p>
						<?php }
					}?>
            </div>
        </div>
        <div class="iFntBoxAPI">
                <h2 class="title"><?php echo esc_html( __( 'Nhà phát triển Plugin', 'webnganh' ) ); ?></h2>
            <div class="Box-Input-API">
                <div class="box-list">
                    <div><?php echo esc_html(__('Chúng tôi là nhà phát triển ứng dụng hiện tại, ứng dụng được phát triển và chia sẻ cộng đồng Wordpress phiên bản Basic để sử dụng.', 'webnganh' )); ?></div>
                    <div><?php echo esc_html( __( 'Phiên bản hiện tại đang phát triển cho Contact Form 7', 'webnganh' ) ); ?></div>
                    <div><?php echo esc_html( __( 'Sắp tới sẽ cập nhật cho các phiên bản ShortCode để các nhà phát triển khác sử dụng Plugin cho việc Customize Wordpress.', 'webnganh' ) ); ?></div>
                    <div><b><?php echo esc_html( __( 'Hướng dẫn sử dụng:', 'webnganh' ) ); ?></b> <a href="https://webnganh.vn/huong-dan-su-dung-plugins-api-google-sheet.html">https://webnganh.vn/huong-dan-su-dung-plugins-api-google-sheet.html</a></div>
                </div>
            </div>
        </div>
        <div class="iFntBoxAPI">
            <h2 class="title"><?php echo esc_html( __( 'Hỗ trợ plugin', 'webnganh' ) ); ?></h2>
            <div class="Box-Input-API">
                <div class="box-list">
                    <div><b><?php echo esc_html( __( 'Email:', 'webnganh' ) ); ?></b> <?php echo esc_html( __( 'info@webnganh.vn', 'webnganh' ) ); ?></div>
                    <div><b><?php echo esc_html( __( 'Ủng hộ:', 'webnganh' ) ); ?></b> <a href="https://paypal.me/tvtoske"><?php echo esc_html( __( 'https://paypal.me/tvtoske', 'webnganh' ) ); ?></a> <?php echo esc_html( __( '( Hãy ủng hộ để chúng tôi có động lực phát triển thêm )', 'webnganh' ) ); ?></div>
                </div>
            </div>
        </div>
   </div>
  

    <?php
    }

    public function load_css_and_js_files_edit() {
        add_action( 'admin_print_styles', array( $this, 'css_files_edit' ) );
        add_action( 'admin_print_scripts', array( $this, 'js_files_edit' ) );
    }

       /**
    * enqueue CSS files
    * @since 1.0
    */
   public function css_files_edit() {
        if ( is_admin() && ( isset( $_GET['page'] ) && ( ( $_GET['page'] == 'wpcf7-new' ) || ( $_GET['page'] == 'Gsheet-config-API' ) || ( $_GET['page'] == 'wpcf7' ) ) ) ) {
            wp_enqueue_style( 'style-css', iFnt_Gsheet_URL . 'assets/css/style.css', iFnt_Gsheet_Version, true );
        }
    }

    /**
     * enqueue JS files
    * @since 1.0
    */
    public function js_files_edit() {
        if ( is_admin() && ( isset( $_GET['page'] ) && ( ( $_GET['page'] == 'wpcf7-new' ) || ( $_GET['page'] == 'Gsheet-config-API' ) ) ) ) {
            wp_enqueue_script( 'main-js', iFnt_Gsheet_URL . 'assets/js/main.js', iFnt_Gsheet_Version, true );
        }
        
        // if ( is_admin() ) {
        // wp_enqueue_script( 'adds-js', iFnt_Gsheet_URL . 'assets/js/adds.js', iFnt_Gsheet_Version, true );
        // }
    }
}

// Initialize the google sheet connector class
$init = new iFnt_Gsheet_Free();
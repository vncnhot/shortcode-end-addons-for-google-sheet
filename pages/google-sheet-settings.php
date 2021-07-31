<?php
/*
 * Google Sheet cài đặt page
 * @since 1.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit();
 }

 $active_tab = ( isset ( $_GET['tab'] ) && sanitize_text_field( $_GET["tab"] )) ?  sanitize_text_field( $_GET['tab'] ) : 'setting-api';
 ?>

<div class="wrap">
	<?php
       $tabs = array(  
        'setting-api' => __( 'Cài đặt kết nối', 'GsiFnt' ),
       //  'support' => __( 'Hỗ trợ', 'GsiFnt' ),
        // 'faq' => __( 'FAQ', 'GsiFnt' ),
        // 'demos' => __( 'Ví dụ', 'GsiFnt' ),
        // 'system-status' => __( 'Trạng thái hệ thống', 'GsiFnt' ),
         );
       echo '<div id="icon-themes" class="icon32"><br></div>';
       echo '<h2 class="nav-tab-wrapper">';
       foreach( $tabs as $tab => $name ){
           $class = ( $tab == $active_tab ) ? ' nav-tab-active' : '';
           echo "<a class='nav-tab$class' href='?page=Gsheet-config-API&tab=$tab'>$name</a>";

       }
       echo '</h2>';
   	switch ( $active_tab ){
        case 'setting-api' :
   		   $gs_intigrate = new iFnt_Gsheet_Free();
			   $gs_intigrate->box_google_sheet_config();
   		   break;
		case 'support' :
   		   include( iFnt_Gsheet_DIR . "pages/Gsheet-support.php" );
   		   break;
		case 'faq' :
   		   include( iFnt_Gsheet_DIR . "pages/Gsheet-faq.php" );
   		   break;
		case 'demos' :
   		   include( iFnt_Gsheet_DIR . "pages/Gsheet-demo.php" );
   		   break;
		case 'system-status' :
   		  include( iFnt_Gsheet_DIR . "pages/Gsheet-info.php" );
   		   break;
	}
	?>
</div>
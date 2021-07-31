jQuery(document).ready(function () {

    jQuery(document).on('click', '#save-access-code', function () {
        jQuery( ".loading-note-active" ).addClass( "loading" );
        var data = {
        action: 'get_key_token_api',
        code: jQuery('#gsheet-token').val(),
        security: jQuery('#Gsheet-ajax-nonce').val()
      };
      jQuery.post(ajaxurl, data, function (response ) {
        console.log(response.success);
        jQuery( "#validation-message" ).addClass( "active" );
          if( response.success == false ) { 
           jQuery( ".loading-note-active" ).removeClass( "loading" );
            jQuery( "#validation-message" ).empty();
            jQuery('#validation-message').html("<span class='error-mess'>Code không được để trống !</span>");
          } else {
            jQuery( ".loading-note-active" ).removeClass( "loading" );
            jQuery( "#validation-message" ).empty();
            jQuery( '#validation-message' ).html("<span class='valid-mess'>Code Google đã lưu thành công ! đợi 5 giây tải lại trang.</span>");
            setTimeout(function () { location.reload(); }, 5000);
          }
      });
    });  

    jQuery(document).on('click', '#del-active-log', function () {
        jQuery(".loading-note-active").addClass( "loading" );
		var txt;
		var r = confirm("Bạn muốn hủy kết nối với tài khoản này ?");
		if (r == true) {
			var data = {
				action: 'del_active_log',
				security: jQuery('#Gsheet-ajax-nonce').val()
			};
			jQuery.post(ajaxurl, data, function (response ) {
				if( ! response.success ) {
					alert('Lỗi khi hủy kích hoạt!');
					jQuery( ".loading-note-active" ).removeClass( "loading" );
					jQuery( "#validation-message" ).empty();
				} else {
          jQuery( "#validation-message" ).addClass( "active" );
					jQuery( ".loading-note-active" ).removeClass( "loading" );
					jQuery( "#validation-message" ).empty();
          jQuery( '#validation-message' ).html("<span class='valid-mess'>Tài khoản của bạn đã bị xóa. Xác thực lại lần nữa để tích hợp Google Sheet.</span>");
		   		    setTimeout(function () { location.reload(); }, 3000);
				}
			});
		} else {
			jQuery( ".loading-note-active" ).removeClass( "loading" );
		}
        
      
      
    }); 

});

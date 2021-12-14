<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WC_Gateway_Paypal Class.
 */
class MomoQrScanGetWay extends QrScanGetWay {

	public function __construct() {
		//parent::__construct();
        

        $this->id                 = 'momo_qr_scan';
        $this->icon = sprintf("%s/public/images/logo-momo.png",MC_QUETMA_PLUGIN_URL);
        $this->has_fields         = false;
        //$this->order_button_text  = __( 'Thanh Toán', 'woocommerce' );
        $this->method_title       = __( 'Quét Mã QR Momo', 'woocommerce' );
        $this->method_description = '';
        $this->supports           = array(
            'products',
            'refunds',
        );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables.
        $this->title          = $this->get_option( 'title' );
        
        $this->description    = $this->get_option( 'description' );
         $this->method_description    = 'Thanh Toán QR Code';
        $this->testmode       = 'yes' === $this->get_option( 'testmode', 'no' );
        $this->debug          = 'yes' === $this->get_option( 'debug', 'no' );

        $this->qr_image_url          = $this->get_option( 'qr_image_url' );
        $this->fullname          = $this->get_option( 'fullname' );
        $this->phone          = $this->get_option( 'phone' );
        $this->email          = $this->get_option( 'email' );
        $this->finish_notify_text          = $this->get_option( 'finish_notify_text' );
        $this->send_qr_image_in_email          = $this->get_option( 'send_qr_image_in_email' );
        $this->default_order_payment_done_status = $this->get_option( 'default_order_payment_done_status' );
        $this->oneUSD2VND = $this->get_option( 'oneUSD2VND' );
        $this->after_pay_redirect    = $this->get_option( 'after_pay_redirect' );
        $this->custom_link_redirect    = $this->get_option( 'custom_link_redirect' );

        self::$log_enabled    = $this->debug;

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
        // Customer Emails.
        //add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
        add_action( 'woocommerce_view_order', array( $this, 'thankyou_page' ), 1, 1 );
        add_action('admin_notices', array($this,'show_notify'));
        add_shortcode( 'momo_thank_you_page', array( $this, 'momo_thank_you_page' ) );
    }

    public function get_qr_image_url($order_id = 0){
        if($order_id){
            $order = new WC_Order($order_id);
            $price = $order->get_total( );
            $phone = $this->phone;

            
            if(get_option('woocommerce_currency') == "USD"){
                $price = $price * floatval($this->get_option( 'oneUSD2VND' ));
            }
           
            $price = apply_filters('quet_ma_qr_price', $price);

            
            $qr_url = home_url("wp-json/plugin-quet-ma-thanh-toan/v1/qr?phone=$phone&price=$price");


            return $qr_url;
        }
        return $this->qr_image_url;
    }

    public function init_form_fields() {
        $page = isset($_GET["page"]) ? $_GET["page"] : "";
        $tab = isset($_GET["tab"]) ? $_GET["tab"] : "";
        if(is_admin() && $page == "wc-settings" && $tab == "checkout"){
            wp_enqueue_media();
            wp_enqueue_script( 'jquery-ui-accordion', false, array('jquery') );    
        }

        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Bật/Tắt', 'woocommerce' ),
                'type' => 'checkbox',
                'label' => __( 'Bật cổng thanh toán này', 'woocommerce' ),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __( 'Tên Cổng Thanh Toán', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'Tên cổng thanh toán mà người dùng sẽ thấy khi thanh toán', 'woocommerce' ),
                'default' => 'Quét Mã MoMo',
                'desc_tip'      => true,
            ),
            'description' => array(
                'title' => __( 'Mô Tả Cho Khách', 'woocommerce' ),
                'type' => 'textarea',
                'description' => __( 'Đoạn mô tả giúp khách hiểu rõ hơn cách thức thanh toán', 'woocommerce' ),
                'default' => 'Hãy mở App Momo lên và nhấn Đặt Hàng để quét mã thanh toán'
            ),
            'fullname' => array(
                'title' => __( 'Tên chủ tài khoản Momo', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'Tên chủ tài khoản Momo', 'woocommerce' ),
                'default' => '',
                'desc_tip'      => true,
            ),
            'phone' => array(
                'title' => __( 'Số Điện Thoại Momo', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'Số điện thoại đăng ký Momo', 'woocommerce' ),
                'default' => '',
                'desc_tip'      => true,
            ),
            'email' => array(
                'title' => __( 'Email Momo', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'Email đăng ký Momo - Dùng để kích hoạt đơn hàng tự động', 'woocommerce' ),
                'default' => '',
                'desc_tip'      => false,
            ),
            'qr_image_url' => array(
                'title' => __( 'Hình QR Code', 'woocommerce' ),
                'type' => 'text',
                'description' => __( '<h3>Lấy hình QR Code ở đâu? - Mở App Momo</h3><div class="mc_guide_accordion"> <h3>Bước 1: Bấm chọn: Ví của tôi (góc dưới cùng bên phải)</h3> <div> <p> <img src="'.sprintf("%s/admin/images/momo/step1.jpg",MC_QUETMA_PLUGIN_URL).'"> </p> </div> <h3>Bước 2: Bấm chọn: Mã QR của tôi</h3> <div> <p> <img src="'.sprintf("%s/admin/images/momo/step2.jpg",MC_QUETMA_PLUGIN_URL).'"> </p> </div> <h3>Bước 3: Nhấn Lưu Hình</h3> <div> <p> <img src="'.sprintf("%s/admin/images/momo/step3.jpg",MC_QUETMA_PLUGIN_URL).'"> </p> </div> </div>', 'woocommerce' ),
                'default' => __( '', 'woocommerce' ),
                'desc_tip'      => false,    
                'class'      => 'uploadinput',    
            ),
            'finish_notify_text' => array(
                'title' => __( 'Thông báo hoàn tất thanh toán', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'Khách bấm Tôi Đã Thanh Toán và sẽ thấy thông báo này', 'woocommerce' ),
                'default' => 'Cám ơn bạn đã thanh toán. Chúng tôi sẽ sớm xử lý đơn hàng cho bạn.',
                'desc_tip'      => false,
            ),
            'send_qr_image_in_email' => array(
                'title' => __( 'Gởi hình QR Code trong email đơn hàng', 'woocommerce' ),
                'type' => 'checkbox',
                'description' => __( '', 'woocommerce' ),
                'default' => 'yes',
                'desc_tip'      => false,
            ),
            'oneUSD2VND' => array(
                'title' => __( '1 USD => VND', 'woocommerce' ),
                'type' => 'number',
                'description' => __( 'Tỉ giá chuyển từ 1 USD qua VND ', 'woocommerce' ),
                'default' => '23128',
                'desc_tip'      => false,
            )
            // 'default_order_payment_done_status' => array(
            //      'title' => 'Trạng thái đơn hàng sau khi đã thanh toán',
            //      'description' => '',
            //      'type' => 'select',
            //      'default' => 'completed',
            //      'class' => '',
            //      'css' => '',
            //      'options' => array(
            //           'completed' => 'completed',
            //           'processing' => 'processing',
            //           'on-hold' => 'on-hold',
            //           'pending' => 'pending'
            //      )
            // ),'oneUSD2VND' => array(
            //     'title' => __( '1 USD => VND', 'woocommerce' ),
            //     'type' => 'number',
            //     'description' => __( 'Tỉ giá chuyển từ 1 USD qua VND ', 'woocommerce' ),
            //     'default' => '23128',
            //     'desc_tip'      => false,
            // ),
            // 'after_pay_redirect' => array(
            //      'title' => 'Điều hướng sau khi thanh toán xong',
            //      'description' => '',
            //      'type' => 'select',
            //      'default' => 'order_detail',
            //      'class' => '',
            //      'css' => '',
            //      'options' => array(
            //           'my_account' => 'Về Trang My Account',
            //           'download' => 'Về danh sách download',
            //           'order_detail' => 'Về chi tiết đơn hàng',
            //           'custom_link' => 'Link tuỳ chọn',
            //      )
            // ),
            // 'custom_link_redirect' => array(
            //     'title' => __( 'Link tuỳ chọn chuyển tới sau khi nhận được thanh toán', 'woocommerce' ),
            //     'type' => 'text',
            //     'description' => __( '', 'woocommerce' ),
            //     'default' => '',
            //     'desc_tip'      => false,
            // ),
        );
    }


    public function momo_thank_you_page($atts){
        
        $order_id = isset($atts['order_id']) ? intval($atts['order_id']): 0;
        if(!$order_id){
            $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']): 0;
        }

        if(!$order_id){
            return "<p>Không có thông tin đơn hàng</p>";
        }
        ob_start();
        $this->thankyou_page($order_id);
        return ob_get_clean();
    }
    
    public function thankyou_page( $order_id ) {
        // hien QR Code ở đây
        $order = new WC_Order($order_id);
        

        if($order->get_status() =='completed' || $order->get_payment_method() != $this->id) return;

        $this->enqueue_script($order_id);
        
        $price = $order->get_total( );
        $phone = $this->phone;
        $link_momo_mobile = "https://nhantien.momo.vn/$phone/$price";
        /*$api_price = $price;
        if(get_option('woocommerce_currency') == "USD"){
            $api_price = $api_price * floatval($this->get_option( 'oneUSD2VND' ));
        }
        
        
        
        $qr_url = $this->qr_image_url;
        $link_momo_mobile = "https://nhantien.momo.vn/$phone/$api_price";
        $qr_url = home_url("wp-json/plugin-quet-ma-thanh-toan/v1/qr?phone=$phone&price=$api_price");*/
        $qr_url = $this->get_qr_image_url($order_id);
        if(wp_is_mobile()) :
            ?>
            <div id="frame-thanhtoan">
                <div class="mc-loinhan">
                        Người nhận: <strong><?php echo $this->fullname; ?></strong><br>
                        Số điện thoại: <strong><?php echo $this->phone; ?></strong><br>
                        Số tiền: <strong><?php echo $order->get_formatted_order_total( ) ?></strong> <br>
                        Ghi chú chuyển tiền (mã đơn hàng):<br> 
                        <div class="field-ghi-chu">
                            <input type="text" class="input-ghi-chu" name="" value="<?php echo $order_id; ?>" id="ghichu">
                            <span class="nutcopy" id="copy_ghi_chu" style="margin: 10px 0;"><img alt="Copy nội dung ghi chú" src="data:image/svg+xml;base64,PHN2ZyBoZWlnaHQ9IjUxMnB0IiB2aWV3Qm94PSItMjEgMCA1MTIgNTEyIiB3aWR0aD0iNTEycHQiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0ibTE4Ni42Njc5NjkgNDE2Yy00OS45ODQzNzUgMC05MC42Njc5NjktNDAuNjgzNTk0LTkwLjY2Nzk2OS05MC42Njc5Njl2LTIxOC42NjQwNjJoLTM3LjMzMjAzMWMtMzIuMzYzMjgxIDAtNTguNjY3OTY5IDI2LjMwMDc4MS01OC42Njc5NjkgNTguNjY0MDYydjI4OGMwIDMyLjM2MzI4MSAyNi4zMDQ2ODggNTguNjY3OTY5IDU4LjY2Nzk2OSA1OC42Njc5NjloMjY2LjY2NDA2MmMzMi4zNjMyODEgMCA1OC42Njc5NjktMjYuMzA0Njg4IDU4LjY2Nzk2OS01OC42Njc5Njl2LTM3LjMzMjAzMXptMCAwIiBmaWxsPSIjMTk3NmQyIi8+PHBhdGggZD0ibTQ2OS4zMzIwMzEgNTguNjY3OTY5YzAtMzIuNDA2MjUtMjYuMjYxNzE5LTU4LjY2Nzk2OS01OC42NjQwNjItNTguNjY3OTY5aC0yMjRjLTMyLjQwNjI1IDAtNTguNjY3OTY5IDI2LjI2MTcxOS01OC42Njc5NjkgNTguNjY3OTY5djI2Ni42NjQwNjJjMCAzMi40MDYyNSAyNi4yNjE3MTkgNTguNjY3OTY5IDU4LjY2Nzk2OSA1OC42Njc5NjloMjI0YzMyLjQwMjM0MyAwIDU4LjY2NDA2Mi0yNi4yNjE3MTkgNTguNjY0MDYyLTU4LjY2Nzk2OXptMCAwIiBmaWxsPSIjMjE5NmYzIi8+PC9zdmc+" /></span>
                        </div>
                        
                    </div>
                    
                <hr>
                <p>Bấm vào nút bên dưới để vào app MoMo thanh toán cho đơn hàng</p>

                <a target="blank" href="<?php echo $link_momo_mobile ?>" id="openappbtn" class="button alt nut-animation"><img src="<?php echo $this->icon;?>"/> Mở App Momo <br> <span>thanh toán đơn #<?php echo $order_id ?></span></a>

                <?php  $this->template_ifinish(); ?>

                <p>Hoặc bạn cũng có thể tải QR để quét và thanh toán qua app</p>
                <div class="qr-wrapper">
                    
                    <img class="mc-qrcode" download="qrcode" src="<?php echo $qr_url; ?>">

                    <a download="qrcode" href="<?php echo $qr_url; ?>"  target="_blank" id="downloadqrcode" class="btn-download-in-image">

                        <img class="mc-downloadicon" src="<?php echo sprintf("%s/public/images/download.png",MC_QUETMA_PLUGIN_URL); ?>">
                         Tải Mã QR Để Quét
                    </a>
                </div>
                

                <div id="mc-mobileguide" style="display: none;">
                    

                    <p>Bạn mở app quét mã và nhấn vào biểu tượng như chỉ dẫn hình dưới để quét mã</p>
                    <div class="framegif">
                        <div class="frame frame0">
                            <img src="<?php echo sprintf("%s/public/images/momo/frame1.png",MC_QUETMA_PLUGIN_URL); ?>">
                        </div>
                        <div class="frame frame1" style="display: none;">
                            <img src="<?php echo sprintf("%s/public/images/momo/frame2.png",MC_QUETMA_PLUGIN_URL); ?>">
                        </div>
                    </div>
                    
                </div>
                
                <script type="text/javascript">

                    btnGhiChu = document.getElementById("copy_ghi_chu");
                    btnGhiChu.onclick = function() { 
                    var copyText = document.getElementById("ghichu");

                      /* Select the text field */
                      copyText.select();
                      copyText.setSelectionRange(0, 99999); /* For mobile devices */

                      /* Copy the text inside the text field */
                      document.execCommand("copy");
                     return false; 

                    }


                    
                </script>
            </div>
            <?php
        else :
        
        ?>
            <div id="frame-thanhtoan">
                <hr>
                <h3>Quét mã để thanh toán <br><img src="<?php echo $this->icon;?>"/></h3>
                <?php if($this->qr_image_url) : ?>
                    <div class="mc-loinhan">
                        Người nhận: <strong><?php echo $this->fullname; ?></strong><br>
                        Số điện thoại: <strong><?php echo $this->phone; ?></strong><br>
                        Số tiền: <strong><?php echo $order->get_formatted_order_total( ) ?></strong> <br>
                        Ghi chú chuyển tiền bạn ghi mã đơn hàng: <strong style="color:red"><?php echo $order_id; ?></strong>  
                    </div>
                    <?php  $this->template_ifinish(); ?>

                    <img class="mc-qrcode" src="<?php echo $qr_url; ?>">
                    
                <?php else : ?>
                    <div class="mc-warning">
                        Chưa cài đặt hình QR Code thanh toán
                    </div>
                <?php endif; ?>

                <?php $this->footer_scan_panel($order_id); ?>
                <hr>
            </div>
            <?php
        endif;
    }
	
}
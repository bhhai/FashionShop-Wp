<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WC_Gateway_Paypal Class.
 */
class ZaloPayQRScanGetWay extends QrScanGetWay {

	public function __construct() {
		//parent::__construct();
        

        $this->id                 = 'zalopay_qr_scan';
        $this->icon = sprintf("%s/public/images/zalopay-icon.png",MC_QUETMA_PLUGIN_URL);
        $this->has_fields         = false;
        //$this->order_button_text  = __( 'Thanh Toán', 'woocommerce' );
        $this->method_title       = __( 'Quét Mã QR ZaloPay', 'woocommerce' );
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
        self::$log_enabled    = $this->debug;

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
        // Customer Emails.
        //add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
        add_action( 'woocommerce_view_order', array( $this, 'thankyou_page' ), 1, 1 );
        add_action('admin_notices', array($this,'show_notify'));
        add_action('admin_notices', array($this,'show_notify'));
    }

    public function init_form_fields() {
        

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
                'default' => 'Quét Mã QR ZaloPay',
                'desc_tip'      => true,
            ),
            'description' => array(
                'title' => __( 'Mô Tả Cho Khách', 'woocommerce' ),
                'type' => 'textarea',
                'description' => __( 'Đoạn mô tả giúp khách hiểu rõ hơn cách thức thanh toán', 'woocommerce' ),
                'default' => 'Hãy mở App ZaloPay lên và nhấn Đặt Hàng để quét mã thanh toán'
            ),
            'fullname' => array(
                'title' => __( 'Tên chủ tài khoản ZaloPay', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'Tên chủ tài khoản ZaloPay', 'woocommerce' ),
                'default' => '',
                'desc_tip'      => true,
            ),
            'phone' => array(
                'title' => __( 'Số Điện Thoại ZaloPay', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'Số điện thoại đăng ký Zalopay', 'woocommerce' ),
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
                'description' => __( '<h3>Lấy hình QR Code ở đâu? - Mở ZaloPay</h3><div class="mc_guide_accordion"> <h3>Bước 1: Bấm chọn: Nhận Tiền</h3> <div> <p> <img src="'.sprintf("%s/admin/images/zalopay/step1.jpg",MC_QUETMA_PLUGIN_URL).'"> </p> </div> <h3>Bước 2: Chụp màn hình QR Code</h3> <div><p>-IOS bấm giữ 2-3 giây: Nút Nguồn + Nút Home <br>-Android bấm giữ 2-3 giây: Nút nguồn + Nút giảm âm lượng</p> <p> <img src="'.sprintf("%s/admin/images/zalopay/step2.jpg",MC_QUETMA_PLUGIN_URL).'"> </p> </div> <h3>Bước 3: Upload hình vào Media và cắt hình QR</h3> <div> <p> Bấm xem video hướng dẫn <br><a target="blank" href="https://youtu.be/D42QycosO6o"> <img src="'.sprintf("%s/admin/images/video-cover.png",MC_QUETMA_PLUGIN_URL).'"></a> </p> </div>  </div>', 'woocommerce' ),
                'default' => __( '', 'woocommerce' ),
                'desc_tip'      => false,    
                'class'      => 'uploadinput',    
            ),
            'finish_notify_text' => array(
                'title' => __( 'Thông báo hoàn tất thanh toán', 'woocommerce' ),
                'type' => 'text',
                'description' => __( 'Khách bấm Tôi Đã Thanh Toán và sẽ thấy thông báo này', 'woocommerce' ),
                'default' => 'Cám ơn bạn đã thanh toán. Chúng tôi sẽ kiểm tra và kích hoạt đơn hàng cho bạn sớm nhất!',
                'desc_tip'      => false,
            ),
            'send_qr_image_in_email' => array(
                'title' => __( 'Gởi hình QR Code trong email đơn hàng', 'woocommerce' ),
                'type' => 'checkbox',
                'description' => __( '', 'woocommerce' ),
                'default' => 'yes',
                'desc_tip'      => false,
            )
        );
    }

    public function thankyou_page( $order_id ) {
        // hien QR Code ở đây
        $order = new WC_Order($order_id);
        $this->enqueue_script($order_id);
        if($order->get_status() =='completed' || $order->get_payment_method() != $this->id) return;
        if(wp_is_mobile()) :
            ?>
            <div id="frame-thanhtoan">
                <hr>
                <h3>Tải mã để thanh toán<br><img src="<?php echo $this->icon;?>"/></h3>

                <a download="qrcode" href="<?php echo $this->qr_image_url; ?>"  target="_blank" id="downloadqrcode" class="button alt">

                    <img class="mc-downloadicon" src="<?php echo sprintf("%s/public/images/download.png",MC_QUETMA_PLUGIN_URL); ?>">
                     Tải Mã QR Để Quét
                </a>
                <a href="zalopay://" style="display: none;" id="openappbtn" class="button alt">Mở App ZaloPay</a>

                <div id="mc-mobileguide" style="display: none;">
                    <div class="mc-loinhan">
                        Người nhận: <strong><?php echo $this->fullname; ?></strong> - <strong><?php echo $this->phone; ?></strong><br>
                        Số tiền: <strong><?php echo $order->get_formatted_order_total( ) ?></strong> <br>
                        Ghi chú chuyển tiền bạn ghi mã đơn hàng: <strong>#<?php echo $order_id; ?></strong>  
                    </div>
                    <?php  $this->template_ifinish(); ?>
                    <div class="framegif">
                        <div class="frame frame0">
                            <img src="<?php echo sprintf("%s/public/images/zalopay/frame1.jpg",MC_QUETMA_PLUGIN_URL); ?>">
                        </div>
                        <div class="frame frame1" style="display: none;">
                            <img src="<?php echo sprintf("%s/public/images/zalopay/frame2.jpg",MC_QUETMA_PLUGIN_URL); ?>">
                        </div>
                    </div>
                    
                </div>
                <img class="mc-qrcode" download="qrcode" src="<?php echo $this->qr_image_url; ?>">
                
            </div>
            <?php
        else :
        
        ?>
            <div id="frame-thanhtoan">
                <hr>
                <h3>Quét mã để thanh toán<br><img src="<?php echo $this->icon;?>"/></h3>
                <?php if($this->qr_image_url) : ?>
                    <div class="mc-loinhan">
                        Người nhận: <strong><?php echo $this->fullname; ?></strong> - <strong><?php echo $this->phone; ?></strong><br>
                        Số tiền: <strong><?php echo $order->get_formatted_order_total( ) ?></strong> <br>
                        Ghi chú chuyển tiền bạn ghi mã đơn hàng: <strong>#<?php echo $order_id; ?></strong>  
                    </div>
                    <?php  $this->template_ifinish(); ?>
                    <img class="mc-qrcode" src="<?php echo $this->qr_image_url; ?>">
                    
                <?php else : ?>
                    <div class="mc-warning">
                        Chưa cài đặt hình QR Code thanh toán
                    </div>
                <?php endif; ?>

                <p>
                    <img width="25" src="<?php echo sprintf("%s/public/images/qr-code-1.png",MC_QUETMA_PLUGIN_URL); ?>" alt="">
                    Sử dụng App <b>ZaloPay</b> để quét mã.
                    <br>
                    <img width="25" src="<?php echo sprintf("%s/public/images/loading.gif",MC_QUETMA_PLUGIN_URL); ?>" alt="">
                </p>
                <hr>
            </div>
            <?php
        endif;
    }
	
}
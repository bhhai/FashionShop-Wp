<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WC_Gateway_Paypal Class.
 */
class QrScanGetWay extends WC_Payment_Gateway {

    /** @var bool Whether or not logging is enabled */
    public static $log_enabled = false;

    /** @var WC_Logger Logger instance */
    public static $log = false;

    
    /**
     * Constructor for the gateway.
     */
    public function __construct() {
        
    }

    public function get_qr_image_url($order_id = 0){
        return $this->qr_image_url;
    }

    public function show_notify(){
        /*
        $screen = get_current_screen();
        $valid = $screen->id == 'woocommerce_page_wc-settings' && isset($_GET['section']) && $_GET['section'] ==$this->id;
        if(!$valid) return;
        ?>
        <div class="notice notice-info is-dismissible">
            <p>Mới: Tự động <strong>xử lý đơn hàng TỰ ĐỘNG</strong>, QR Code trong email đơn hàng, giao diện đẹp hơn <a target="blank" href="https://docs.google.com/forms/d/e/1FAIpQLSe94ioApBSJXJemVkxzBycj930p4VfyuhoQEDuCc2FlNkA8Ng/viewform">Dùng Plugin Quét Mã Thanh Toán Pro - Miễn Phí</a></p>
        </div>
        <?php
        */
    }

    /**
     * Logging method.
     *
     * @param string $message Log message.
     * @param string $level   Optional. Default 'info'.
     *     emergency|alert|critical|error|warning|notice|info|debug
     */
    public static function log( $message, $level = 'info' ) {
        if ( self::$log_enabled ) {
            if ( empty( self::$log ) ) {
                self::$log = wc_get_logger();
            }
            self::$log->log( $level, $message, array( 'source' => 'qrscan' ) );
        }
    }

    /**
     * Check if this gateway is enabled and available in the user's country.
     * @return bool
     */
    public function is_valid_for_use() {
        return true;
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields() {
        
    }

    public function template_ifinish(){
        ?>
        <div id="finishscan" style="display: none;">
            <p class="questionfinish">Bạn đã thanh toán xong?</p>
            <div class="btnfinish-scan nut-animation">Tôi đã thanh toán xong</div>

            <div id="thongbaofinish" style="display: none">
                <?php echo $this->finish_notify_text; ?>
            </div>
        </div>
        <?php
    }


    function process_payment( $order_id ) {
        global $woocommerce;
        $order = new WC_Order( $order_id );

        // Mark as on-hold (we're awaiting the cheque)
        $order->update_status('on-hold', __( 'Đơn hàng tạm giữ', 'woocommerce' ));

        // Remove cart
        $woocommerce->cart->empty_cart();

        // Return thankyou redirect
        return array(
            'result' => 'success',
            'redirect' => $this->get_return_url( $order )
        );
    }

    function get_normal_minute_complete_order(){
        return "5 - 10 phút";
    }

    function enqueue_script($order_id = 0){

        ?>
        <style type="text/css">
            #openappbtn {
                margin-top: 10px;
                background-color: rgba(255, 255, 255, 0.87);
                border: 1px solid #f2f2f2;
                border-radius: 2px;
                box-shadow: 0 2px 2px #999;
                color: #039be5;
                cursor: pointer;
                display: inline-block;
                font-family: -apple-system, "Roboto-Medium", Helvetica, sans-serif;
                letter-spacing: .4px;
                line-height: 14px;
                padding: 17px 20px;
                text-decoration: none;
                vertical-align: middle;
                margin-bottom: 16px;
                margin-right: 0;
            }
            #frame-thanhtoan{
            text-align: center;
            }

            #frame-thanhtoan .mc-qrcode{
            margin-top: 5px;
            margin-bottom: 5px;
                width: 353px;
            }

            .mc-loinhan{
            color: #9F6000;
                background-color: #FEEFB3;
                padding: 10px 10px;
                margin-top: 3px;
                margin-bottom: 20px;
                border-radius: 6px 6px;
            }

            .mc-downloadicon{
            width: 30px;
            }

            #mc-mobileguide{
            margin-top: 10px;
            }

            .woocommerce a.button#openappbtn{
                margin-top: 10px;
            }

            .woocommerce a.button.openappbtn{
                display: block;
                margin-top: 10px;
            }

            #finishscan{
            text-align: center;
            }
            #finishscan .questionfinish{
            margin-bottom: 10px;
            }

            #thongbaofinish{
            background: #fff;
            border: 1px solid gray;
            color: #333;
            padding: 10px 20px;
            margin-top: 20px;
            }
            .btnfinish-scan:hover{
            background: gray;
            color: #fff;
            }
            .btnfinish-scan{
            background: #fff;
                border: 1px solid gray;
                color: gray;
                display: block;
                padding: 8px 20px;
                cursor: pointer;
                width: 300px;
                margin: 0 auto;
                border-radius: 7px;
                max-width: 100%;
            }

            .nut-animation {
                animation: zoominoutsinglefeatured 1.5s infinite;
                font-weight: bold;
            }

            .hover_bkgr_fricc{
                background:rgba(0,0,0,.4);
                cursor:pointer;
                display:none;
                height:100%;
                position:fixed;
                text-align:center;
                top:0;
                left: 0;
                width:100%;
                z-index:10000;
            }
            .hover_bkgr_fricc .helper{
                display:inline-block;
                height:100%;
                vertical-align:middle;
            }
            .hover_bkgr_fricc > div {
                background-color: #fff;
                box-shadow: 10px 10px 60px #555;
                display: inline-block;
                height: auto;
                max-width: 551px;
                min-height: 100px;
                vertical-align: middle;
                width: 60%;
                position: relative;
                border-radius: 8px;
                padding: 15px 5%;
            }
            .popupCloseButton {
                background-color: #fff;
                border: 3px solid #999;
                border-radius: 50px;
                cursor: pointer;
                display: inline-block;
                font-family: arial;
                font-weight: bold;
                position: absolute;
                top: -20px;
                right: -20px;
                font-size: 25px;
                line-height: 30px;
                width: 30px;
                height: 30px;
                text-align: center;
            }
            .popupCloseButton:hover {
                background-color: #ccc;
            }
            .trigger_popup_fricc {
                cursor: pointer;
                font-size: 20px;
                margin: 20px;
                display: inline-block;
                font-weight: bold;
            }
            @keyframes zoominoutsinglefeatured {
                0% {
                    transform: scale(1,1);
                }
                50% {
                    transform: scale(1.04,1.04);
                }
                100% {
                    transform: scale(1,1);
                }
            }

            .field-ghi-chu .input-ghi-chu{
                border: none;
                color: red;
                text-align: center;
                width: 150px;
                display: inline;
                background: #fdefb3 !important;
                border: none;
                font-size: 18px;
                box-shadow: none !important;
                font-weight: bold;
                margin: 0 0;
            }

            .field-ghi-chu{
            position: relative;
            border-radius: 3px;
            border: 1px solid #ddd;
            margin-top:10px;
            }
            .nutcopy:hover{
            opacity: 1;
            }
            .nutcopy{
            opacity: 0.8;
            position: absolute;
            width: 30px;
            height: 30px;
            top: -5px;
            }

            .qr-wrapper{
            margin-bottom: 20px;
            }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function(){
                function runFrame(){
                    if( jQuery('#mc-mobileguide .framegif .frame').length == 0 ) return;
                    var curFrame = 0;
                    setInterval(function(){ 
                        jQuery('#mc-mobileguide .framegif .frame').hide();
                        jQuery('#mc-mobileguide .framegif .frame'+curFrame).fadeIn();
                        curFrame++;
                        curFrame = curFrame % jQuery('#mc-mobileguide .framegif .frame').length;
                    }, 3000);
                }

                function runFrameWithClass(className){
                    var curFrame = 0;
                    setInterval(function(){ 
                        jQuery('#mc-mobileguide .'+className+' .frame').hide();
                        jQuery('#mc-mobileguide .'+className+' .frame'+curFrame).show();
                        curFrame++;
                        curFrame = curFrame % jQuery('#mc-mobileguide .'+className+' .frame').length;
                    }, 3000);
                }
                function showMobileGuide(){
                    jQuery('#mc-mobileguide').show();
                    runFrame();
                }
                jQuery('#downloadqrcode').click(function(){
                    showMobileGuide();
                    jQuery('#openappbtn').show();
                    jQuery('.openappbtn').show();
                });

                jQuery('.openappbtn.moca').click(function(){
                    jQuery('.framemoca').show();
                    jQuery('.framegrab').hide();
                    runFrameWithClass('framemoca');
                });

                jQuery('.openappbtn.grab').click(function(){
                    jQuery('.framegrab').show();
                    jQuery('.framemoca').hide();
                    runFrameWithClass('framegrab');
                });

                setTimeout(function(){
                    jQuery('#finishscan').fadeIn();
                }, 30000);
                jQuery('#finishscan .btnfinish-scan').click(function(){
                    jQuery('.questionfinish').hide();
                    jQuery('#finishscan .btnfinish-scan').hide();
                    jQuery('#thongbaofinish').show();
                    jQuery('#footer_scan .loading-quetma').hide();
                });

                
                function check_order_completed(){
                    //console.log('check don hang');
                    var data = {
                        'action': 'mc_check_order_completed',
                        'order_id': <?php echo $order_id; ?>,
                    };
                    jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>',data, function(res){

                        if(res == 1){
                            play_sound();
                            jQuery('.hover_bkgr_fricc').show();
                            clearInterval(timer);
                        }
                        
                    });
                }

                //var timer = setInterval(check_order_completed, 20000);

                function play_sound(){
                    var sound = document.getElementById("completed_audio");
                    sound.play(); 
                }

                jQuery('.hover_bkgr_fricc').click(function(){
                    jQuery('.hover_bkgr_fricc').hide();
                });

                jQuery('.popupCloseButton').click(function(){
                    jQuery('.hover_bkgr_fricc').hide();
                });
            });

        </script>

        <div class="hover_bkgr_fricc">
            <span class="helper"></span>
            <div>
                <div class="popupCloseButton">&times;</div>
                <p><img style="width: 60px" src="<?php echo sprintf("%s/public/images/foursquare-check-in.png",MC_QUETMA_PLUGIN_URL); ?>"></p>
                <p>Đơn hàng đã được thanh toán xong</p>
                <?php 
                $redirect_link = $this->get_option( 'after_pay_redirect' );
                if($redirect_link == "my_account"){
                    $redirect_link = get_permalink(get_option( 'woocommerce_myaccount_page_id' ));
                }elseif($redirect_link == "download"){
                    $redirect_link = wc_get_account_endpoint_url( 'downloads' );
                }elseif($redirect_link == "order_detail"){
                    $redirect_link = wc_get_account_endpoint_url( 'view-order' ) . $order_id;
                }else{                    
                    $redirect_link = $this->get_option( 'custom_link_redirect' );
                }
                ?>
                <a id="okAfterPay" href="<?php echo $redirect_link ?>">OK</a> 
            </div>
        </div>

        <audio id="completed_audio">          
          <source src="<?php echo MC_QUETMA_PLUGIN_URL . '/public/completed.mp3'; ?>" type="audio/mpeg">
          Your browser does not support the audio element.
        </audio>
        <?php

    }

    function footer_scan_panel(){
        ?>
        <p id="footer_scan">
            <img width="25" src="<?php echo sprintf("%s/public/images/qr-code-1.png",MC_QUETMA_PLUGIN_URL); ?>" alt="">
            Sử dụng App <b>MoMo</b> để quét mã.
            <br>
            <img width="25" class="loading-quetma" src="<?php echo sprintf("%s/public/images/loading.gif",MC_QUETMA_PLUGIN_URL); ?>" alt=""> <br><strong>Đơn hàng sẽ xử lý tự động <?php echo $this->get_normal_minute_complete_order(); ?> </strong>
        </p>
        
        <?php
    }



}
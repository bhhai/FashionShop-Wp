<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://dangngocbinh.com
 * @since      1.0.0
 *
 * @package    Mc_Quetma
 * @subpackage Mc_Quetma/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Mc_Quetma
 * @subpackage Mc_Quetma/public
 * @author     MeCode <dangngocbinh.dnb@gmail.com>
 */
class Mc_Quetma_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action( 'plugins_loaded', array($this,'init_gateway_class') );
		add_filter( 'woocommerce_payment_gateways', array($this,'add_gateway_class') );
		add_action( 'rest_api_init', array($this, 'register_api'));
		add_action( 'woocommerce_email_order_details', array($this, 'display_qr_scan_in_email'), 1, 4);
		add_action('wp_ajax_mc_check_order_completed', array($this, 'check_order_completed'));
		add_action('wp_ajax_nopriv_mc_check_order_completed', array($this, 'check_order_completed'));
	}

	function check_order_completed(){
		$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
		if($order_id){
			$order = wc_get_order($order_id);
			if($order->get_status() == "completed"){
				echo 1; die();
			}
		}
		echo 0;
		die();
	}

	function display_qr_scan_in_email($order, $sent_to_admin, $plain_text, $email){
		
		$method = $order->get_payment_method();
		$all_method = array(
			'momo_qr_scan',
			'airpay_qr_scan',
			'zalopay_qr_scan',
			'moca_qr_scan'
		);


		if($order->get_status() != 'completed' && in_array($method, $all_method)){
			$getways = null;
			
			switch ($method) {
				case 'momo_qr_scan':
					$getways = new MomoQrScanGetWay();
					break;
				case 'airpay_qr_scan':
					$getways = new AirPayQrScanGetWay();
					break;
				case 'zalopay_qr_scan':
					$getways = new ZaloPayQRScanGetWay();
					break;
				case 'moca_qr_scan':
					$getways = new MocaQrScanGetWay();
					break;
				
				default:
					# code...
					break;
			}
			
			if($getways->send_qr_image_in_email == 'no'){
				return ;
			}

			?>
			<div style="text-align: center">
				<h3>Quét Mã Để Thanh Toán</h3>
				<a href="<?php echo $order->get_view_order_url(); ?>">
					<img src="<?php echo  $getways->get_qr_image_url($order->get_id()); ?>" width="280px">
				</a>

				

			</div>
			<?php
		}
	}


	function register_api () {

		register_rest_route( 'plugin-quet-ma-thanh-toan/v1', '/qr', array(
		    'methods' => 'GET',
		    'callback' => array($this,'show_qr'),
		) );
		

	}


	function show_qr(){
		
		include "includes/phpqrcode/qrlib.php";

		$phone = isset($_GET['phone']) ?   filter_var($_GET['phone'], FILTER_SANITIZE_STRING) : "";
		$price = isset($_GET['price']) ?   filter_var($_GET['price'], FILTER_SANITIZE_STRING) : "";
		
		if($phone && $price){
			$text = sprintf("2|99|%s|||0|0|%d", $phone, $price);
			QRcode::png($text,false, QR_ECLEVEL_Q, 10); 
		}else{
			$name = plugin_dir_path( __FILE__ ) . 'images/qr-fail.png';
			$fp = fopen($name, 'rb');

			header("Content-Type: image/png");
			header("Content-Length: " . filesize($name));

			fpassthru($fp);
		}
		
		
		die();
	}
	

	public function init_gateway_class() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/getways/qrscan-getway.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/getways/momo-getway.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/getways/zalopay-getway.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/getways/moca-getway.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/getways/airpay-getway.php';

	}

	public function add_gateway_class($methods ){
		$methods[] = 'MomoQrScanGetWay';
		$methods[] = 'ZaloPayQRScanGetWay';
		$methods[] = 'MocaQRScanGetWay';
		$methods[] = 'AirPayQRScanGetWay';
	    return $methods;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mc_Quetma_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mc_Quetma_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mc-quetma-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mc_Quetma_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mc_Quetma_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mc-quetma-public.js', array( 'jquery' ), $this->version, false );

	}

}

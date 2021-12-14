<?php
/**
 * Cấu hình cơ bản cho WordPress
 *
 * Trong quá trình cài đặt, file "wp-config.php" sẽ được tạo dựa trên nội dung 
 * mẫu của file này. Bạn không bắt buộc phải sử dụng giao diện web để cài đặt, 
 * chỉ cần lưu file này lại với tên "wp-config.php" và điền các thông tin cần thiết.
 *
 * File này chứa các thiết lập sau:
 *
 * * Thiết lập MySQL
 * * Các khóa bí mật
 * * Tiền tố cho các bảng database
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** Thiết lập MySQL - Bạn có thể lấy các thông tin này từ host/server ** //
/** Tên database MySQL */
define( 'DB_NAME', 'fashionshop' );

/** Username của database */
define( 'DB_USER', 'root' );

/** Mật khẩu của database */
define( 'DB_PASSWORD', '' );

/** Hostname của database */
define( 'DB_HOST', 'localhost' );

/** Database charset sử dụng để tạo bảng database. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Kiểu database collate. Đừng thay đổi nếu không hiểu rõ. */
define('DB_COLLATE', '');

/**#@+
 * Khóa xác thực và salt.
 *
 * Thay đổi các giá trị dưới đây thành các khóa không trùng nhau!
 * Bạn có thể tạo ra các khóa này bằng công cụ
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * Bạn có thể thay đổi chúng bất cứ lúc nào để vô hiệu hóa tất cả
 * các cookie hiện có. Điều này sẽ buộc tất cả người dùng phải đăng nhập lại.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '/sliPfG:i~^k//-}Z=)K(xNMS@iy]/l{|DAzs~<9CH!=KrFTu >tiSE7CIqST]~H' );
define( 'SECURE_AUTH_KEY',  'EURlj-oa&IK<rLz~YOiMaKZvB.S8;GC)bN/Y<sm*3o)%cwozU@|x?FnVe-<H*8ON' );
define( 'LOGGED_IN_KEY',    '|Tg2o6+K>.I7UgC/]?#C67#cF(E/GOQQZ-9w0~{ANVje^1##`B]%#@)#l8ksA1ik' );
define( 'NONCE_KEY',        '-ZjQnR2?(/%7pB^kJC@mk%z0=`5fqYvMh)5@,bh/UI/uHOLU1ow|XhTI5BW~NTUe' );
define( 'AUTH_SALT',        'DJrI0F+vNEL%Rr@`#Rfu(`P,1!]nRA4Y@y3DAwKuo@VVd:_a-Q}eQ#m4@@#!zl*9' );
define( 'SECURE_AUTH_SALT', 'B}kT:R>k.ldDwXQ[})Y4MtnVB/Oz%6L`.XC(^`k|nZxQ36?q<WqnU zD3xL.8Z U' );
define( 'LOGGED_IN_SALT',   'n8lDo@QLR@>h<zZjaAaEv|=Jc&:8RdKxs2Jl*[=q5<Q$fEsJ^nVeZ*:-KRR&{d.n' );
define( 'NONCE_SALT',       'pSX`r{qm2~z%iR8J%:% lT;{OZLzzDNH^uxyxKEO~R4VT Y/wF+(Ch}EV[VVCyFd' );

/**#@-*/

/**
 * Tiền tố cho bảng database.
 *
 * Đặt tiền tố cho bảng giúp bạn có thể cài nhiều site WordPress vào cùng một database.
 * Chỉ sử dụng số, ký tự và dấu gạch dưới!
 */
$table_prefix = 'wp_';

/**
 * Dành cho developer: Chế độ debug.
 *
 * Thay đổi hằng số này thành true sẽ làm hiện lên các thông báo trong quá trình phát triển.
 * Chúng tôi khuyến cáo các developer sử dụng WP_DEBUG trong quá trình phát triển plugin và theme.
 *
 * Để có thông tin về các hằng số khác có thể sử dụng khi debug, hãy xem tại Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* Đó là tất cả thiết lập, ngưng sửa từ phần này trở xuống. Chúc bạn viết blog vui vẻ. */

/** Đường dẫn tuyệt đối đến thư mục cài đặt WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Thiết lập biến và include file. */
require_once(ABSPATH . 'wp-settings.php');

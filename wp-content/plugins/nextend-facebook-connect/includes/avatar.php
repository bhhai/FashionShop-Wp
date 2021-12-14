<?php

class NextendSocialLoginAvatar {

    /**
     * @return NextendSocialLoginAvatar
     */
    public static function getInstance() {
        static $inst = null;
        if ($inst === null) {
            $inst = new self();
        }

        return $inst;
    }


    public function __construct() {
        if (NextendSocialLogin::$settings->get('avatar_store')) {
            add_action('nsl_update_avatar', array(
                $this,
                'updateAvatar'
            ), 10, 3);

            // WP User Avatar https://wordpress.org/plugins/wp-user-avatar/
            // Ultimate member
            if (!defined('WPUA_VERSION') && !class_exists('UM', false) && !class_exists('buddypress', false)) {

                add_filter('pre_get_avatar_data', array(
                    $this,
                    'preGetAvatarData'
                ), 1, 2);
            }

            add_filter('post_mime_types', array(
                $this,
                'addPostMimeTypeAvatar'
            ));

            add_filter('ajax_query_attachments_args', array(
                $this,
                'modifyQueryAttachmentsArgs'
            ));
        }
    }

    public function addPostMimeTypeAvatar($types) {
        $types['avatar'] = array(
            __('Avatar', 'nextend-facebook-connect'),
            __('Manage Avatar', 'nextend-facebook-connect'),
            _n_noop('Avatar <span class="count">(%s)</span>', 'Avatar <span class="count">(%s)</span>', 'nextend-facebook-connect')
        );

        return $types;
    }

    public function modifyQueryAttachmentsArgs($query) {
        if (!isset($query['meta_query']) || !is_array($query['meta_query'])) {
            $query['meta_query'] = array();
        }
        if (isset($query['post_mime_type']) && $query['post_mime_type'] === 'avatar') {
            $query['post_mime_type']         = 'image';
            $query['meta_query']['relation'] = 'AND';
            $query['meta_query'][]           = array(
                'key'     => '_wp_attachment_wp_user_avatar',
                'compare' => 'EXISTS'
            );
        } else {
            $avatars_in_all_media = NextendSocialLogin::$settings->get('avatars_in_all_media');

            //Avatars will be loaded in Media Libray Grid view - All media items if $avatars_in_all_media is disabled!
            if (!$avatars_in_all_media) {
                $query['meta_query']['relation'] = 'AND';
                $query['meta_query'][]           = array(
                    'key'     => '_wp_attachment_wp_user_avatar',
                    'compare' => 'NOT EXISTS'
                );
            }
        }

        return $query;
    }

    /**
     * @param NextendSocialProvider $provider
     * @param                       $user_id
     * @param                       $avatarUrl
     */
    public function updateAvatar($provider, $user_id, $avatarUrl) {
        global $blog_id, $wpdb;
        if (!empty($avatarUrl)) {

            if (class_exists('UM', false)) {
                require_once(ABSPATH . '/wp-admin/includes/file.php');
                $profile_photo = get_user_meta($user_id, 'profile_photo', true);
                if (empty($profile_photo)) {
                    $extension = 'jpg';
                    if (preg_match('/\.(jpg|jpeg|gif|png)/', $avatarUrl, $match)) {
                        $extension = $match[1];
                    }
                    $avatarTempPath = self::download_url($avatarUrl);
                    if (!is_wp_error($avatarTempPath)) {
                        $umAvatarKey         = 'profile_photo';
                        $umNameWithExtension = $umAvatarKey . '.' . $extension;
                        $umUserAvatarDir     = UM()
                            ->uploader()
                            ->get_upload_user_base_dir($user_id, true);
                        if ($umUserAvatarDir) {
                            $umUserAvatarPath = $umUserAvatarDir . DIRECTORY_SEPARATOR . $umNameWithExtension;
                            $umAvatarInfo     = @getimagesize($avatarTempPath);

                            /*this copy will be deleted after resizing*/
                            copy($avatarTempPath, $umUserAvatarPath);
                            UM()
                                ->uploader()
                                ->resize_image($umUserAvatarPath, $umUserAvatarPath, $umAvatarKey, $user_id, '0,0,' . $umAvatarInfo[0] . ',' . $umAvatarInfo[0]);
                            /*the final profile_photo*/
                            copy($avatarTempPath, $umUserAvatarPath);

                            update_user_meta($user_id, $umAvatarKey, $umNameWithExtension);
                        }
                    }
                    unlink($avatarTempPath);

                    UM()
                        ->user()
                        ->remove_cache($user_id);
                };

                return;
            }

            //upload user avatar for BuddyPress - bp_displayed_user_avatar() function
            if (class_exists('BuddyPress', false)) {
                if (!empty($avatarUrl)) {
                    $extension = 'jpg';
                    if (preg_match('/\.(jpg|jpeg|gif|png)/', $avatarUrl, $match)) {
                        $extension = $match[1];
                    }

                    require_once(ABSPATH . '/wp-admin/includes/file.php');
                    $avatarTempPath = self::download_url($avatarUrl);

                    if (!is_wp_error($avatarTempPath)) {
                        if (!function_exists('bp_members_avatar_upload_dir')) {
                            $bpMembersFunctionsPath = buddypress()->plugin_dir . '/bp-members/bp-members-functions.php';
                            if (file_exists($bpMembersFunctionsPath)) {
                                require_once($bpMembersFunctionsPath);
                            }
                        }

                        if (function_exists('bp_members_avatar_upload_dir')) {
                            $pathInfo = bp_members_avatar_upload_dir('avatars', $user_id);

                            if (wp_mkdir_p($pathInfo['path'])) {
                                if ($av_dir = opendir($pathInfo['path'] . '/')) {
                                    $hasAvatar = false;
                                    while (false !== ($avatar_file = readdir($av_dir))) {
                                        if ((preg_match("/-bpfull/", $avatar_file) || preg_match("/-bpthumb/", $avatar_file))) {
                                            $hasAvatar = true;
                                            break;
                                        }
                                    }
                                    if (!$hasAvatar) {
                                        copy($avatarTempPath, $pathInfo['path'] . '/' . 'avatar-bpfull.' . $extension);
                                        rename($avatarTempPath, $pathInfo['path'] . '/' . 'avatar-bpthumb.' . $extension);
                                    }
                                }
                                closedir($av_dir);
                            }
                        }
                    }
                }
            }


            /**
             * $original_attachment_id is false, if the user has had avatar set but the path is not found.
             */
            $original_attachment_id  = get_user_meta($user_id, $wpdb->get_blog_prefix($blog_id) . 'user_avatar', true);
            $original_attachment_md5 = false;
            if ($original_attachment_id) {
                $attached_file = get_attached_file($original_attachment_id);
                if (($attached_file && !file_exists($attached_file)) || !$attached_file) {
                    $original_attachment_id = false;
                } else {
                    /**
                     * We should only get the md5 value of the image, if there is an existing attachment, indeed.
                     */
                    $original_attachment_md5 = get_user_meta($user_id, 'nsl_user_avatar_md5', true);
                }
            }
            $overwriteAttachment = false;
            /**
             * Overwrite the original attachment if avatar was set and the provider attachment exits.
             */
            if ($original_attachment_id && get_post_meta($original_attachment_id, $provider->getId() . '_avatar', true)) {
                $overwriteAttachment = true;
            }

            if (!$original_attachment_id) {
                /**
                 * If the user unlink and link the social provider back the original avatar will be used.
                 */
                $args  = array(
                    'post_type'   => 'attachment',
                    'post_status' => 'inherit',
                    'meta_query'  => array(
                        array(
                            'key'   => $provider->getId() . '_avatar',
                            'value' => $provider->getAuthUserData('id')
                        )
                    )
                );
                $query = new WP_Query($args);
                if ($query->post_count > 0) {
                    $original_attachment_id = $query->posts[0]->ID;
                    $overwriteAttachment    = true;
                    update_user_meta($user_id, $wpdb->get_blog_prefix($blog_id) . 'user_avatar', $original_attachment_id);
                }
            }

            /**
             * If there was no original avatar or overwrite mode is on, download the avatar of the selected provider.*
             */
            if (!$original_attachment_id || $overwriteAttachment === true) {
                require_once(ABSPATH . '/wp-admin/includes/file.php');

                $avatarTempPath = self::download_url($avatarUrl);
                if (!is_wp_error($avatarTempPath)) {
                    $mime        = wp_get_image_mime($avatarTempPath);
                    $mime_to_ext = apply_filters('getimagesize_mimes_to_exts', array(
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/gif'  => 'gif',
                        'image/bmp'  => 'bmp',
                        'image/tiff' => 'tif',
                    ));

                    /**
                     * If the uploaded image has extension from the mime type and it is appear in the $mime_to_ext.
                     * Make a unique filename, depending on the extension.
                     * Copy the downloaded file with the new name to the uploads path.
                     * Unlink the downloaded file.
                     */
                    if (isset($mime_to_ext[$mime])) {

                        $wp_upload_dir = wp_upload_dir();

                        /**
                         * The name of the folder inside /wp-content/uploads where the user avatars will be uploaded.
                         * Can be changed by defining the NSL_AVATARS_FOLDER constant.
                         */
                        $nslUploadDirName = 'nsl_avatars';
                        if (defined('NSL_AVATARS_FOLDER')) {
                            $nslUploadDirName = NSL_AVATARS_FOLDER;
                        }
                        $nslUploadDir = trailingslashit($wp_upload_dir['basedir']) . $nslUploadDirName;

                        if (wp_mkdir_p($nslUploadDir)) {

                            $filename = wp_hash($user_id) . '.' . $mime_to_ext[$mime];
                            $filename = wp_unique_filename($nslUploadDir, $filename);

                            $newAvatarPath = trailingslashit($nslUploadDir) . $filename;
                            $newFile       = @copy($avatarTempPath, $newAvatarPath);
                            @unlink($avatarTempPath);

                            if (false !== $newFile) {
                                $url          = $wp_upload_dir['baseurl'] . '/' . $nslUploadDirName . '/' . basename($filename);
                                $newAvatarMD5 = md5_file($newAvatarPath);

                                if ($overwriteAttachment) {
                                    $originalAvatarImage = get_attached_file($original_attachment_id);

                                    // we got the same image, so we do not want to store it
                                    if ($original_attachment_md5 === $newAvatarMD5) {
                                        @unlink($newAvatarPath);
                                    } else {
                                        // Store the new avatar and remove the old one
                                        @unlink($originalAvatarImage);

                                        foreach (get_intermediate_image_sizes() as $size) {
                                            /**
                                             * Delete the previous Avatar sub-sizes to avoid orphan images
                                             */
                                            $originalAvatarSubsize = image_get_intermediate_size($original_attachment_id, $size);
                                            if (isset($originalAvatarSubsize['path'])) {
                                                $originalAvatarSubsizePath = trailingslashit($wp_upload_dir['basedir']) . $originalAvatarSubsize['path'];
                                                if (file_exists($originalAvatarSubsizePath)) {
                                                    @unlink($originalAvatarSubsizePath);
                                                }
                                            }
                                        }

                                        update_attached_file($original_attachment_id, $newAvatarPath);

                                        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                                        require_once(ABSPATH . 'wp-admin/includes/image.php');

                                        wp_update_attachment_metadata($original_attachment_id, wp_generate_attachment_metadata($original_attachment_id, $newAvatarPath));

                                        update_user_meta($user_id, $wpdb->get_blog_prefix($blog_id) . 'user_avatar', $original_attachment_id);
                                        update_user_meta($user_id, 'nsl_user_avatar_md5', $newAvatarMD5);
                                    }
                                } else {
                                    $attachment = array(
                                        'guid'           => $url,
                                        'post_mime_type' => $mime,
                                        'post_title'     => '',
                                        'post_content'   => '',
                                        'post_status'    => 'private',
                                    );

                                    $new_attachment_id = wp_insert_attachment($attachment, $newAvatarPath);
                                    if (!is_wp_error($new_attachment_id)) {

                                        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                                        require_once(ABSPATH . 'wp-admin/includes/image.php');

                                        wp_update_attachment_metadata($new_attachment_id, wp_generate_attachment_metadata($new_attachment_id, $newAvatarPath));

                                        update_post_meta($new_attachment_id, $provider->getId() . '_avatar', $provider->getAuthUserData('id'));
                                        update_post_meta($new_attachment_id, '_wp_attachment_wp_user_avatar', $user_id);

                                        update_user_meta($user_id, $wpdb->get_blog_prefix($blog_id) . 'user_avatar', $new_attachment_id);
                                        update_user_meta($user_id, 'nsl_user_avatar_md5', $newAvatarMD5);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function preGetAvatarData($args, $id_or_email) {
        global $blog_id, $wpdb;

        $id = NextendSocialLogin::getUserIDByIdOrEmail($id_or_email);

        if ($id == 0) {
            return $args;
        }

        /**
         * Get the avatar attachment id of the user.
         */
        $attachment_id = get_user_meta($id, $wpdb->get_blog_prefix($blog_id) . 'user_avatar', true);
        if (wp_attachment_is_image($attachment_id)) {
            $image_src_array = wp_get_attachment_image_src($attachment_id);

            if (isset($args['size'])) {
                $get_size        = is_numeric($args['size']) ? array(
                    $args['size'],
                    $args['size']
                ) : $args['size'];
                $image_src_array = wp_get_attachment_image_src($attachment_id, $get_size);
            }

            $args['url'] = $image_src_array[0];
        }

        return $args;
    }

    public function removeProfilePictureGravatarDescription($description) {
        if (strpos($description, 'Gravatar') !== false) {
            return '';
        }

        return $description;
    }


    /**
     * Adjusted according to WordPress 5.7
     * Override for WordPress default download_url() since wp_tempnam() can not handle long urls properly to generate temp file names.
     *
     * Downloads a URL to a local temporary file using the WordPress HTTP API.
     *
     * Please note that the calling function must unlink() the file.
     *
     * @param string $url                    The URL of the file to download.
     * @param int    $timeout                The timeout for the request to download the file.
     *                                       Default 300 seconds.
     * @param bool   $signature_verification Whether to perform Signature Verification.
     *                                       Default false.
     *
     * @return string|WP_Error Filename on success, WP_Error on failure.
     * @since 2.5.0
     * @since 5.2.0 Signature Verification with SoftFail was added.
     *
     */
    public static function download_url($url, $timeout = 300, $signature_verification = false) {
        // WARNING: The file is not automatically deleted, the script must unlink() the file.
        if (!$url) {
            return new WP_Error('http_no_url', __('Invalid URL Provided.'));
        }

        $tmpfname = wp_tempnam();
        if (!$tmpfname) {
            return new WP_Error('http_no_file', __('Could not create Temporary file.'));
        }

        $response = wp_safe_remote_get($url, array(
                'timeout'  => $timeout,
                'stream'   => true,
                'filename' => $tmpfname,
            ));

        if (is_wp_error($response)) {
            unlink($tmpfname);

            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);

        if (200 != $response_code) {
            $data = array(
                'code' => $response_code,
            );

            // Retrieve a sample of the response body for debugging purposes.
            $tmpf = fopen($tmpfname, 'rb');
            if ($tmpf) {
                /**
                 * Filters the maximum error response body size in `download_url()`.
                 *
                 * @param int $size The maximum error response body size. Default 1 KB.
                 *
                 * @see   download_url()
                 *
                 * @since 5.1.0
                 *
                 */
                $response_size = apply_filters('download_url_error_max_body_size', KB_IN_BYTES);
                $data['body']  = fread($tmpf, $response_size);
                fclose($tmpf);
            }

            unlink($tmpfname);

            return new WP_Error('http_404', trim(wp_remote_retrieve_response_message($response)), $data);
        }

        $content_md5 = wp_remote_retrieve_header($response, 'content-md5');
        if ($content_md5) {
            $md5_check = verify_file_md5($tmpfname, $content_md5);
            if (is_wp_error($md5_check)) {
                unlink($tmpfname);

                return $md5_check;
            }
        }

        // If the caller expects signature verification to occur, check to see if this URL supports it.
        if ($signature_verification) {
            /**
             * Filters the list of hosts which should have Signature Verification attempted on.
             *
             * @param string[] $hostnames List of hostnames.
             *
             * @since 5.2.0
             *
             */
            $signed_hostnames       = apply_filters('wp_signature_hosts', array(
                'wordpress.org',
                'downloads.wordpress.org',
                's.w.org'
            ));
            $signature_verification = in_array(parse_url($url, PHP_URL_HOST), $signed_hostnames, true);
        }

        // Perform signature valiation if supported.
        if ($signature_verification) {
            $signature = wp_remote_retrieve_header($response, 'x-content-signature');
            if (!$signature) {
                // Retrieve signatures from a file if the header wasn't included.
                // WordPress.org stores signatures at $package_url.sig.

                $signature_url = false;
                $url_path      = parse_url($url, PHP_URL_PATH);

                if ('.zip' === substr($url_path, -4) || '.tar.gz' === substr($url_path, -7)) {
                    $signature_url = str_replace($url_path, $url_path . '.sig', $url);
                }

                /**
                 * Filters the URL where the signature for a file is located.
                 *
                 * @param false|string $signature_url The URL where signatures can be found for a file, or false if none are known.
                 * @param string       $url           The URL being verified.
                 *
                 * @since 5.2.0
                 *
                 */
                $signature_url = apply_filters('wp_signature_url', $signature_url, $url);

                if ($signature_url) {
                    $signature_request = wp_safe_remote_get($signature_url, array(
                            'limit_response_size' => 10 * KB_IN_BYTES,
                            // 10KB should be large enough for quite a few signatures.
                        ));

                    if (!is_wp_error($signature_request) && 200 === wp_remote_retrieve_response_code($signature_request)) {
                        $signature = explode("\n", wp_remote_retrieve_body($signature_request));
                    }
                }
            }

            // Perform the checks.
            $signature_verification = verify_file_signature($tmpfname, $signature, basename(parse_url($url, PHP_URL_PATH)));
        }

        if (is_wp_error($signature_verification)) {
            if (/**
             * Filters whether Signature Verification failures should be allowed to soft fail.
             *
             * WARNING: This may be removed from a future release.
             *
             * @param bool   $signature_softfail If a softfail is allowed.
             * @param string $url                The url being accessed.
             *
             * @since 5.2.0
             *
             */ apply_filters('wp_signature_softfail', true, $url)) {
                $signature_verification->add_data($tmpfname, 'softfail-filename');
            } else {
                // Hard-fail.
                unlink($tmpfname);
            }

            return $signature_verification;
        }

        return $tmpfname;
    }
}

NextendSocialLoginAvatar::getInstance();
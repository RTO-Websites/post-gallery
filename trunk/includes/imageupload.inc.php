<?php
    use Thumb\Thumb;

    if ( !defined ( 'ABSPATH' ) ) exit;

    if ( !is_user_logged_in () ) {
        die( 'Login required!' );
    }
    require_once ( POSTGALLERY_DIR . '/includes/fine_uploader.class.php' );

    $maxUploadSize = 8;
    $postSize = (int)( ini_get ( 'post_max_size' ) );
    $uploadSize = (int)( ini_get ( 'upload_max_filesize' ) );
    $maxUploadSize = min ( array ( $postSize, $uploadSize ) );
    $uploads = wp_upload_dir ();
    $upload_dir = $uploads[ 'basedir' ];
    $upload_url = $uploads[ 'baseurl' ];
    $upload_url = str_replace ( get_bloginfo ( 'wpurl' ), '', $upload_url );

    $file_handler = new qqFileUploader( array ( 'JPG', 'PNG', 'GIF', 'JPEG' ), $maxUploadSize * 1024 * 1024 );
    $file_result = $file_handler->handleUpload ( $upload_dir . '/cache/' );

    $safemode = ini_get ( 'safe_mode' );
    if ( $safemode == 'on' || $safemode == 'yes' || $safemode == 'true' ) {
        set_time_limit ( 0 );
    }

    $errorMsg = '';
    $success = false;
    if ( !empty( $file_result ) && empty( $file_result[ 'error' ] ) && !empty( $_REQUEST[ 'uploadfolder' ] ) ) {
        $upload_file = $upload_dir . '/cache/' . $file_handler->getName ();

        $errorMsg = '';
        $filename = str_replace ( array ( 'http://', 'https://', '//:' ), '', esc_url ( $file_handler->getName () ) ); // imagepath
        $filename = str_replace ( array ( '%20', ' ' ), '_', $filename );
        $filename = str_replace ( array ( 'ä', 'ö', 'ü' ), array ( 'ae', 'oe', 'ue' ), $filename );
        $filename = str_replace ( array ( '(', ')', '$', '&', '%', '<', '>', '[', ']', '{', '}', '?', '!', '*', '=', '+', '~' ), '', $filename );

        $imageTypes = array ( IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP, IMAGETYPE_WBMP );
        $allowTypes = array_map ( 'image_type_to_mime_type', $imageTypes );
        array_push ( $allowTypes, 'image/x-png', 'image/jpeg', 'application/octet-stream' );

        if ( !file_exists ( $upload_dir . '/gallery' ) ) {
            mkdir ( $upload_dir . '/gallery' );
            @chmod ( $upload_dir . '/gallery', octdec( '0777' ) );
        }
        if ( !file_exists ( $upload_dir . '/gallery/' . $_REQUEST[ 'uploadfolder' ] ) ) {
            mkdir ( $upload_dir . '/gallery/' . $_REQUEST[ 'uploadfolder' ] );
            @chmod ( $upload_dir . '/gallery/' . $_REQUEST[ 'uploadfolder' ], octdec( '0777') );
        }

        $imagepath = $upload_dir . '/gallery/' . $_REQUEST[ 'uploadfolder' ] . '/' . $filename;

        $success = copy ( $upload_file, $imagepath );
        // delete tempfile
        unlink ( $upload_file );

        if ( $success ) {
            @chmod( $imagepath, octdec( '0666' ) );
        } else {
            $errorMsg .= 'Imagecopy error';
        }
    } else {
        $errorMsg .= 'Uploaderror:' . ( !empty( $file_result[ 'error' ] ) ? $file_result[ 'error' ] : '' );
    }

    if ( $success ) {

        $return_value = array ();

        // Return image
        if ( file_exists ( $imagepath ) ) {
            $thumb_instance = Thumb::get_instance ();
            $thumb = $thumb_instance->get_thumb ( array (
                'path'   => $imagepath,
                'width'  => 150,
                'height' => 150,
                'scale'  => 0
            ) );
            $image_size = getimagesize ( $imagepath );
            $return_value[ 'path' ] = $imagepath;
            $return_value[ 'filename' ] = $filename;
            $return_value[ 'width' ] = $image_size[ 0 ];
            $return_value[ 'height' ] = $image_size[ 1 ];
            $return_value[ 'success' ] = true;
            $return_value[ 'thumb_url' ] = $thumb[ 'url' ];
        } else {
            $return_value[ 'success' ] = false;
            $return_value[ 'errorMsg' ] = "Imageupload failed! " . $errorMsg;
        }
    } else {
        $return_value[ 'success' ] = false;
        $return_value[ 'errorMsg' ] = "Image could not be moved! " . $errorMsg;
    }

    echo json_encode ( $return_value );

<?php
    use Thumb\Thumb;

    if ( !defined ( 'ABSPATH' ) ) exit;

    if ( !is_user_logged_in () ) {
        die( 'Login required!' );
    }
    require_once ( POSTGALLERY_DIR . '/includes/FineUploader.class.php' );

    $maxUploadSize = 8;
    $postSize = (int)( ini_get ( 'post_max_size' ) );
    $uploadSize = (int)( ini_get ( 'upload_max_filesize' ) );
    $maxUploadSize = min ( array ( $postSize, $uploadSize ) );
    $uploads = wp_upload_dir ();
    $uploadDir = $uploads[ 'basedir' ];
    $uploadUrl = $uploads[ 'baseurl' ];
    $uploadUrl = str_replace ( get_bloginfo ( 'wpurl' ), '', $uploadUrl );

    $fileHandler = new qqFileUploader( array ( 'JPG', 'PNG', 'GIF', 'JPEG' ), $maxUploadSize * 1024 * 1024 );
    $fileResult = $fileHandler->handleUpload ( $uploadDir . '/cache/' );

    $safemode = ini_get ( 'safe_mode' );
    if ( $safemode == 'on' || $safemode == 'yes' || $safemode == 'true' ) {
        set_time_limit ( 0 );
    }

    $errorMsg = '';
    $success = false;
    if ( !empty( $fileResult ) && empty( $fileResult[ 'error' ] ) && !empty( $_REQUEST[ 'uploadfolder' ] ) ) {
        $uploadFile = $uploadDir . '/cache/' . $fileHandler->getName ();

        $errorMsg = '';
        $filename = str_replace ( array ( 'http://', 'https://', '//:' ), '', esc_url ( $fileHandler->getName () ) ); // imagepath
        $filename = str_replace ( array ( '%20', ' ' ), '_', $filename );
        $filename = str_replace ( array ( 'ä', 'ö', 'ü' ), array ( 'ae', 'oe', 'ue' ), $filename );
        $filename = str_replace ( array ( '(', ')', '$', '&', '%', '<', '>', '[', ']', '{', '}', '?', '!', '*', '=', '+', '~' ), '', $filename );

        $imageTypes = array ( IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP, IMAGETYPE_WBMP );
        $allowTypes = array_map ( 'image_type_to_mime_type', $imageTypes );
        array_push ( $allowTypes, 'image/x-png', 'image/jpeg', 'application/octet-stream' );

        if ( !file_exists ( $uploadDir . '/gallery' ) ) {
            mkdir ( $uploadDir . '/gallery' );
            @chmod ( $uploadDir . '/gallery', octdec( '0777' ) );
        }
        if ( !file_exists ( $uploadDir . '/gallery/' . $_REQUEST[ 'uploadfolder' ] ) ) {
            mkdir ( $uploadDir . '/gallery/' . $_REQUEST[ 'uploadfolder' ] );
            @chmod ( $uploadDir . '/gallery/' . $_REQUEST[ 'uploadfolder' ], octdec( '0777') );
        }

        $imagepath = $uploadDir . '/gallery/' . $_REQUEST[ 'uploadfolder' ] . '/' . $filename;

        $success = copy ( $uploadFile, $imagepath );
        // delete tempfile
        unlink ( $uploadFile );

        if ( $success ) {
            @chmod( $imagepath, octdec( '0666' ) );
        } else {
            $errorMsg .= 'Imagecopy error';
        }
    } else {
        $errorMsg .= 'Uploaderror:' . ( !empty( $fileResult[ 'error' ] ) ? $fileResult[ 'error' ] : '' );
    }

    if ( $success ) {

        $returnValue = array ();

        // Return image
        if ( file_exists ( $imagepath ) ) {
            $thumbInstance = Thumb::getInstance ();
            $thumb = $thumbInstance->getThumb ( array (
                'path'   => $imagepath,
                'width'  => 150,
                'height' => 150,
                'scale'  => 0
            ) );
            $imageSize = getimagesize ( $imagepath );
            $returnValue[ 'path' ] = $imagepath;
            $returnValue[ 'filename' ] = $filename;
            $returnValue[ 'width' ] = $imageSize[ 0 ];
            $returnValue[ 'height' ] = $imageSize[ 1 ];
            $returnValue[ 'success' ] = true;
            $returnValue[ 'thumb_url' ] = $thumb[ 'url' ];
        } else {
            $returnValue[ 'success' ] = false;
            $returnValue[ 'errorMsg' ] = "Imageupload failed! " . $errorMsg;
        }
    } else {
        $returnValue[ 'success' ] = false;
        $returnValue[ 'errorMsg' ] = "Image could not be moved! " . $errorMsg;
    }

    echo json_encode ( $returnValue );

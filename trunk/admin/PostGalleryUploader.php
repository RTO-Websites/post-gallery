<?php

namespace Admin;

use Lib\PostGallery;
use Pub\PostGalleryPublic;
use Lib\Thumb;

if ( !defined( 'ABSPATH' ) ) exit;


class PostGalleryUploader {
    private $uploadedFile;
    private $uploadDir;
    private $uploadUrl;
    private $postid;
    private $uploadFolder;
    private $filename;
    private $filenameOnly;
    private $extension;
    private $fullPath;

    private $postGalleryPublic;

    public function __construct() {
        $uploads = wp_upload_dir();
        $this->postGalleryPublic = PostGalleryPublic::getInstance();
        $this->postid = filter_input( INPUT_POST, 'postid' );
        $this->uploadDir = $uploads['basedir'];
        $this->uploadUrl = str_replace( get_bloginfo( 'wpurl' ), '', $uploads['baseurl'] );
        $this->uploadFolder = filter_input( INPUT_POST, 'uploadFolder' );

        $this->uploadedFile = $_FILES['file'];

        $this->createFilename();
        $this->createFolders();
    }


    /**
     * Sanitize filename and check if already exists
     */
    private function createFilename() {
        $filename = isset( $_REQUEST["name"] ) ? $_REQUEST["name"] : $this->uploadedFile;
        $filename = str_replace( '&', '', $filename );
        $filename = sanitize_file_name( $filename );
        $filenameSplit = explode( '.', $filename );
        $this->extension = array_pop( $filenameSplit );
        $filenameOnly = implode( '.', $filenameSplit );
        $fullPath = $this->uploadDir . '/gallery/' . $this->uploadFolder . '/' . $filename;


        // temp vars to check if file exists
        $newFilename = $filename;
        $newFilenameOnly = $filenameOnly;
        $checkPath = $fullPath;
        $count = 1;

        // rename if already exists
        while ( file_exists( $checkPath ) ) {
            $newFilename = $filenameOnly . '-' . $count . '.' . $this->extension;
            $newFilenameOnly = $filenameOnly . '-' . $count;
            $checkPath = $this->uploadDir . '/gallery/' . $this->uploadFolder . '/' . $newFilename;
            $count += 1;
        }
        $this->fullPath = $checkPath;
        $this->filename = $newFilename;
        $this->filenameOnly = $newFilenameOnly;
    }

    /**
     * create folders if not exists
     */
    private function createFolders() {
        if ( !file_exists( $this->uploadDir . '/gallery' ) ) {
            mkdir( $this->uploadDir . '/gallery' );
            @chmod( $this->uploadDir . '/gallery', octdec( '0777' ) );
        }
        if ( !file_exists( $this->uploadDir . '/gallery/' . $this->uploadFolder ) ) {
            mkdir( $this->uploadDir . '/gallery/' . $this->uploadFolder );
            @chmod( $this->uploadDir . '/gallery/' . $this->uploadFolder, octdec( '0777' ) );
        }
    }

    public function handleUpload() {
        if ( empty( $this->uploadFolder ) ) {
            return [
                'success' => false,
                'msg' => 'Imageupload failed! No upload-folder!',
            ];
        }

        $chunkUpload = $this->handleChunkUpload();

        if ( !$chunkUpload['finished'] ) {
            return [
                'success' => true,
                'msg' => 'Chunk',
                'filename' => $this->filename,
                'fullpath' => $this->fullPath,
                'partname' => $chunkUpload['partname'],
            ];
        }

        $attachmentId = $this->createAttachmentPost();

        if ( !$attachmentId ) {
            unlink( $this->fullPath );
            return [
                'success' => false,
                'msg' => 'Imageupload failed! Attachment could not be created!',
            ];
        }

        $thumbInstance = Thumb::getInstance();
        $thumb = $thumbInstance->getThumb( [
            'path' => $this->fullPath,
            'width' => 150,
            'height' => 150,
            'scale' => 0,
        ] );


        $imageSize = getimagesize( $this->fullPath );

        // resize if bigger than max size
        $maxWidth = $this->postGalleryPublic->option( 'maxImageWidth' );
        $maxHeight = $this->postGalleryPublic->option( 'maxImageHeight' );
        if ( ( !empty( $maxWidth ) && $imageSize[0] > $maxWidth )
            || ( !empty( $maxHeight ) && $imageSize[1] > $maxHeight )
        ) {
            $this->resizeImage( $maxWidth, $maxHeight );
            $imageSize = getimagesize( $this->fullPath );
        }

        $itemHtml = $this->getItemHtml( $attachmentId, $thumb );


        return [
            'path' => $this->fullPath,
            'filename' => $this->filename,
            'width' => $imageSize[0],
            'height' => $imageSize[1],
            'success' => true,
            'thumb_url' => $thumb['url'],
            'itemHtml' => $itemHtml,
        ];
    }

    private function handleChunkUpload() {
        $chunk = isset( $_REQUEST["chunk"] ) ? intval( $_REQUEST["chunk"] ) : 0;
        $chunks = isset( $_REQUEST["chunks"] ) ? intval( $_REQUEST["chunks"] ) : 0;
        $tmpFilename = $this->fullPath . '.part';

        file_put_contents( $tmpFilename, file_get_contents( $this->uploadedFile['tmp_name'] ), FILE_APPEND );

        if ( !$chunks || $chunk === $chunks - 1 ) {
            // finished
            rename( $tmpFilename, $this->fullPath );
            return [
                'finished' => true,
                'partname' => $tmpFilename,
            ];
        }

        unlink( $this->uploadedFile['tmp_name'] );
        return [
            'finished' => false,
            'partname' => $tmpFilename,
        ];
    }

    /**
     * Resize image, if bigger than max size
     */
    private function resizeImage( $width, $height ) {
        $thumbInstance = Thumb::getInstance();
        $thumb = $thumbInstance->getThumb( [
            'path' => $this->fullPath,
            'width' => $width,
            'height' => $height,
            'scale' => 2,
        ] );

        unlink( $this->fullPath );
        rename( $thumb['path'], $this->fullPath );
    }

    /**
     * Creates attachment database entry
     *
     * @return int|string|WP_Error|null
     */
    private function createAttachmentPost() {
        $fullUrl = get_bloginfo( 'wpurl' ) . $this->uploadUrl . '/gallery/' . $this->uploadFolder . '/' . $this->filename;
        $attachmentId = \Lib\PostGallery::checkForAttachmentData( $fullUrl, $this->postid );

        return $attachmentId;
    }

    /**
     * Gets rendered item html
     *
     * @param $attachmentId
     * @param $thumb
     * @return string
     */
    private function getItemHtml( $attachmentId, $thumb ) {
        $tpl = new \Lib\Template( POSTGALLERY_DIR . '/admin/partials/uploaded-image-item.php', [
            'attachmentId' => $attachmentId,
            'fullFilename' => $this->filename,
            'filename' => $this->filenameOnly,
            'thumbUrl' => $thumb['url'],
        ] );

        return $tpl->getRendered();
    }
}
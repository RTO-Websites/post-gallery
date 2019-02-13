<?php

namespace Inc;

use Lib\PostGalleryFilesystem;
use Lib\PostGalleryHelper;
use Lib\PostGalleryImageList;
use Lib\PostGalleryImage;

/**
 * @deprecated
 * Use Lib\PostGallery
 *
 * Class PostGallery
 * @package Inc
 */
class PostGallery extends \Lib\PostGallery {

    /**
     * @Deprecated
     *
     * Sorting an image-array
     *
     * @param {array} $images
     * @return {array}
     */
    public static function sortImages( $images, $postid ) {
        return PostGalleryImageList::sort( $images, $postid );
    }

    /**
     * Return an image-array
     *
     * @param int $postid
     * @return array
     */
    public static function getImages( $postid = null ) {
        return PostGalleryImageList::get( $postid );
    }

    /**
     * @Deprecated
     *
     * Return an image-array with resized images
     *
     * @param int $postid
     * @param array $args
     * @return array
     */
    public static function getImagesResized( $postid = 0, $args = [] ) {
        return PostGalleryImageList::getResized( $postid, $args );
    }

    /**
     * @Deprecated
     *
     * Returns a comma seperated list with images
     *
     * @param {int} $postid
     * @param {array} $args (singlequotes, quotes)
     * @return {string}
     */
    public static function getImageString( $postid = null, $args = [] ) {
        return PostGalleryImageList::getImageString( $postid, $args );
    }

    /**
     * @Deprecated
     *
     * Get path to thumb.php
     *
     * @param string $filepath
     * @param array $args
     * @return string
     */
    static function getThumbUrl( $filepath, $args = [] ) {
        return PostGalleryImage::getThumbUrl( $filepath, $args );
    }

    /**
     * @Deprecated
     *
     * Get thumb (wrapper for Thumb->getThumb()
     *
     * @param string $filepath
     * @param array $args
     * @return array
     */
    static function getThumb( $filepath, $args = [] ) {
        return PostGalleryImage::getThumb( $filepath, $args );
    }

    /**
     * @Deprecated
     *
     * Generate thumb-path for an array of pics
     *
     * @param array $pics
     * @param array $args
     * @return array
     */
    public static function getPicsResized( $pics, $args ) {
        return PostGalleryImageList::resize( $pics, $args );
    }

    /**
     * @Deprecated
     *
     * Check if post has a thumb or a postgallery-image
     *
     * @param int $postid
     * @return int
     */
    public static function hasPostThumbnail( $postid = 0 ) {
        return PostGalleryHelper::hasPostThumbnail( $postid );
    }

    /**
     * Gets first image (for example to use as post_thumbnail)
     *
     * @param string $size
     * @param null|int $post_id
     * @return bool|array(width, height, size, url, orientation)
     * @throws \ImagickException
     */
    public static function getFirstImage( $size = 'post-thumbnail', $post_id = null ) {
        return PostGalleryImageList::getFirstImage( $size, $post_id );
    }

    /**
     * @Deprecated
     *
     * Get an attachment ID given a URL.
     *
     * @param string $url
     *
     * @return int Attachment ID on success, 0 on failure
     */
    public static function getAttachmentIdByUrl( $url ) {
        return PostGalleryImage::getAttachmentIdByUrl( $url );
    }

    /**
     * @Deprecated
     *
     * @param string $attachmentUrl
     * @return bool
     */
    public static function urlIsThumbnail( $attachmentUrl = '' ) {
        return PostGalleryImage::urlIsThumbnail( $attachmentUrl );
    }


    /**
     * @Deprecated
     *
     * Returns the foldername for the gallery
     *
     * @param object $wpost
     * @return string
     */
    public static function getImageDir( $wpost ) {
        return PostGalleryFilesystem::getImageDir( $wpost );
    }

    /**
     * @Deprecated
     *
     * Rename a folder
     *
     * @param $oldDir
     * @param $newDir
     * @return bool
     */
    public static function renameDir( $oldDir, $newDir ) {
        return PostGalleryFilesystem::renameDir( $oldDir, $newDir );
    }

    /**
     * @Deprecated
     *
     * Helper function, find value in mutlidimensonal array
     *
     * @param $array
     * @param $key
     * @return array
     */
    public static function arraySearch( $array, $key ) {
        return PostGalleryHelper::arraySearch( $array, $key );
    }

    /**
     * Returns a post in default language
     *
     * @param {int} $post_id
     * @return boolean|object
     */
    public static function getOrgPost( $currentPostId ) {
        return PostGalleryHelper::getOrgPost( $currentPostId );
    }
}
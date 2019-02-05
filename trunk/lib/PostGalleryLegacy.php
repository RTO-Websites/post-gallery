<?php
namespace Lib;

trait PostGalleryLegacy {
    /**
     * @Deprecated
     *
     * Sorting an image-array
     *
     * @param {array} $images
     * @return {array}
     */
    public static function sortImages( $images, $postid ) {
        return PostGalleryImages::sort( $images, $postid );
    }

    /**
     * Return an image-array
     *
     * @param int $postid
     * @return array
     */
    public static function getImages( $postid = null ) {
        return PostGalleryImages::get( $postid );
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
        return PostGalleryImages::getResized( $postid, $args );
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
        return PostGalleryImages::getImageString( $postid, $args );
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
        return PostGalleryImages::getThumbUrl( $filepath, $args );
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
        return PostGalleryImages::getThumb( $filepath, $args );
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
        PostGalleryImages::resize( $pics, $args );
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
        return PostGalleryImages::hasPostThumbnail( $postid );
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
        return PostGalleryImages::getFirstImage( $size, $post_id );
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
        return PostGalleryImages::getAttachmentIdByUrl( $url );
    }

    /**
     * @Deprecated
     *
     * @param string $attachmentUrl
     * @return bool
     */
    public static function urlIsThumbnail( $attachmentUrl = '' ) {
        return PostGalleryImages::urlIsThumbnail( $attachmentUrl );
    }
}
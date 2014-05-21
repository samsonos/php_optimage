<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 21.05.14 at 10:51
 */
 
/**
 * Class for interacting with SamsonPHP
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2014 SamsonOS
 * @version 1.0.0
 */
class OptImage extends \samson\core\ExternalModule
{
    public $supported = array('jpeg', 'jpg', 'png');

    /**
     * Prepare collection of web-application images grouped by image type
     *
     * @param array $supported Collection of supported images extensions
     * @return array Collection of web-application images grouped by image type
     */
    protected function getImagesList(array $supported)
    {
        $result = array();

        // Initialize array of extensions as keys
        foreach($supported as $extension) {
            $result[$extension] = array();
        }

        // Iterate gathered namespaces for their resources
        foreach (s()->load_stack as $ns => & $data) {
            // Iterate supported images types
            foreach ($supported as $extension) {
                // If necessary resources has been collected
                if (isset($data['resources'][ $extension ])) {
                    // Iterate resources and gather images
                    foreach ($data['resources'][ $extension ] as & $resource) {
                        $result[$extension][] = $resource;
                    }
                }
            }
        }

        // Remove empty keys
        return array_filter($result);
    }

    /** Module initialization */
    public function init(array $params = array())
    {
        // Get web-application supported images
        foreach ($this->getImagesList($this->supported) as $extension => $images) {

            // Iterate all images with this extension
            foreach ($images as $image) {

                // Generate hash string describing
                $cacheFile = md5($image).'.'.$extension;

                // Check if cache file has to be updated
                if ($this->cache_refresh($cacheFile, false)) {

                    elapsed('Lossless compressing image: '.$image);

                    // Dependently on image extension use specific tool for compression
                    switch ($extension) {
                        case 'jpg':
                        case 'jpeg': exec('jpegoptim '.$image); break;
                        case 'png': exec('optipng -o7 '.$image); break;
                        default: elapsed('Image:'.$image.' cannot be compressed, extension not supported');
                    }

                    // Store cached empty image version to avoid duplicate compressing
                    file_put_contents($cacheFile, '' );
                }
            }
        }
    }
}
 
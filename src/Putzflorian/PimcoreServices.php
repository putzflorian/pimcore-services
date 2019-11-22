<?php

namespace Putzflorian;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PimcoreServices {

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * PimcoreServices constructor.
     *
     * @param ParameterBagInterface $params
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function checkAssetFileName($parent, $filename, $duplicates = true){
        if($duplicates){
            return $this->checkAssetFile($parent, $filename);
        } else {
            return $filename;
        }

    }

    private function checkAssetFile($parent, $filename, $i = 1, $fragment = null){
        if($parentObject = $this->getParentAssetObject($parent)){
            $file = \Pimcore\Model\Asset::getByPath($parentObject->getFullPath() . '/' . $filename);
            if($file){
                $filedata = explode('.',$filename);
                if(!$fragment){
                    $fragment = $filedata[0];
                }

                if($fragment){
                    $tmpFilename = $fragment . '_' . $i . '.' . $filedata[1];
                } else {
                    $tmpFilename = $filedata[0] . '_' . $i . '.' . $filedata[1];
                }

                return $this->checkAssetFile($parent, $tmpFilename, ($i+1), $fragment);
            } else {
                return $filename;
            }
        }
    }


    public function getParentAssetObject($parent){

        $parentObject = null;

        if($parent instanceof \Pimcore\Model\Asset\Folder){
            $parentObject = $parent;
        } else if (\Pimcore\Model\Asset::getById($parent)) {
            $parentObject = \Pimcore\Model\Asset::getById($parent);
        }else if (\Pimcore\Model\Asset::getByPath($parent)) {
            $parentObject = \Pimcore\Model\Asset::getByPath($parent);
        }

        return $parentObject;
    }


    public function safeCrypt($string, $action = 'e'): string {

        $secret_key = '**secret_key**';
        $secret_iv = '**secret_iv**';

        if($this->params->has('putzflorian_pimcoreservices')){
            $p = $this->params->get('putzflorian_pimcoreservices');
            $secret_key = $p['safe_crypt']['secret_key'];
            $secret_iv = $p['safe_crypt']['secret_iv'];
        }

        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

        if( $action == 'e' ) {
            $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
        }
        else if( $action == 'd' ){
            $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
        }

        return $output;

    }

    public function getYoutubeId($url, $netcookieUrl = false){

        // Here is a sample of the URLs this regex matches: (there can be more content after the given URL that will be ignored)
        // http://youtu.be/dQw4w9WgXcQ
        // http://www.youtube.com/embed/dQw4w9WgXcQ
        // http://www.youtube.com/watch?v=dQw4w9WgXcQ
        // http://www.youtube.com/?v=dQw4w9WgXcQ
        // http://www.youtube.com/v/dQw4w9WgXcQ
        // http://www.youtube.com/e/dQw4w9WgXcQ
        // http://www.youtube.com/user/username#p/u/11/dQw4w9WgXcQ
        // http://www.youtube.com/sandalsResorts#p/c/54B8C800269D7C1B/0/dQw4w9WgXcQ
        // http://www.youtube.com/watch?feature=player_embedded&v=dQw4w9WgXcQ
        // http://www.youtube.com/?feature=player_embedded&v=dQw4w9WgXcQ
        // It also works on the youtube-nocookie.com URL with the same above options.
        // It will also pull the ID from the URL in an embed code (both iframe and object tags)
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);


        if(count($match) == 0 && strpos($url, 'youtu') === false){
            $id = $url;
        } else {
            $id = $match[1];
        }

        if($netcookieUrl){
            return 'https://www.youtube-nocookie.com/embed/' . $id;
        } else {
            return $id;
        }

    }

        /**
     * Check if a given ip is in a network
     * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
     * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
     * @return boolean true if the ip is in this range / false if not.
     */
    public function ipInRange( $ip, $range ) {
    	if ( strpos( $range, '/' ) == false ) {
    		$range .= '/32';
    	}
    	// $range is in IP/CIDR format eg 127.0.0.1/24
    	list( $range, $netmask ) = explode( '/', $range, 2 );
    	$range_decimal = ip2long( $range );
    	$ip_decimal = ip2long( $ip );
    	$wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
    	$netmask_decimal = ~ $wildcard_decimal;
    	return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
    }

    /**
     * Convert GPS Koordinates 47° 48' 34.164" N --> 47.809491
     * @param $pos
     *
     * @return bool|float|int|mixed
     */
    public function gpsKonverter($pos) {
        preg_match ('#(\d+)\s*°\s*(\d+)\s*\'\s*(\d+)(?:[,.](\d+))?\s*"#U' , $pos , $items);

        if (empty ($items)) return false;

        array_shift ($items);
        list ($deg , $min , $sec , $trail) = $items;

        return $deg  +  $min / 60  +  ($sec . '.' . $trail) / 3600;
    }



}

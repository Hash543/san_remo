<?php
/**
* NOTA SOBRE LA LICENCIA DE USO DEL SOFTWARE
* 
* El uso de este software está sujeto a las Condiciones de uso de software que
* se incluyen en el paquete en el documento "Aviso Legal.pdf". También puede
* obtener una copia en la siguiente url:
* http://www.redsys.es/wps/portal/redsys/publica/areadeserviciosweb/descargaDeDocumentacionYEjecutables
* 
* Redsys es titular de todos los derechos de propiedad intelectual e industrial
* del software.
* 
* Quedan expresamente prohibidas la reproducción, la distribución y la
* comunicación pública, incluida su modalidad de puesta a disposición con fines
* distintos a los descritos en las Condiciones de uso.
* 
* Redsys se reserva la posibilidad de ejercer las acciones legales que le
* correspondan para hacer valer sus derechos frente a cualquier infracción de
* los derechos de propiedad intelectual y/o industrial.
* 
* Redsys Servicios de Procesamiento, S.L., CIF B85955367
*/

namespace Redsys\Redsys\Helper;

class Hmac extends \Magento\Framework\App\Helper\AbstractHelper {
	
	private static function php_compat_hash($algo, $data, $raw_output = false)
	{
	    $algo = strtolower($algo);
	    switch ($algo) {
	        case 'md5':
	            $hash = md5($data);
	            break;
	            
	        case 'sha1':
	            $hash = sha1($data);
	            break;
	            
	        case 'sha256':
	            $hash = SHA256::hash($data);
	            break;
	
	        default:
	            user_error('hash(): Unknown hashing algorithm: ' . $algo, E_USER_WARNING);
	            return false;
	    }
	
	    if ($raw_output) {
	        return pack('H*', $hash);
	    } else {
	        return $hash;
	    }
	}
	
    private static function hash($algo, $data, $raw_output = false)
    {
        return Hmac::php_compat_hash($algo, $data, $raw_output);
    }
	
	private static function php_compat_hash_hmac($algo, $data, $key, $raw_output = false)
	{
		// Block size (byte) for MD5 and SHA-256.
		$blocksize = 64;
	
		$ipad = str_repeat("\x36", $blocksize);
		$opad = str_repeat("\x5c", $blocksize);
	
		if (strlen($key) > $blocksize) {
			$key = hash($algo, $key, true);
		} else {
			$key = str_pad($key, $blocksize, "\x00");
		}
	
		$ipad ^= $key;
		$opad ^= $key;
	
		return Hmac::hash($algo, $opad . hash($algo, $ipad . $data, true), $raw_output);
	}
	
	public static function hash_hmac($algo, $data, $key, $raw_output = false){
		return Hmac::php_compat_hash_hmac($algo, $data, $key, $raw_output);
	}
	
}
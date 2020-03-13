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

class SHA256Data extends \Magento\Framework\App\Helper\AbstractHelper {
	// buffer
	public $buf = array();
	// padded data
	public $chunks = null;

	public $hash = null;

	public function __construct($str){
		$M = strlen($str);  // number of bytes
		$L1 = ($M >> 28) & 0x0000000F;  // top order bits
		$L2 = $M << 3;  // number of bits
		$l = pack('N*', $L1, $L2);

		// 64 = 64 bits needed for the size mark. 1 = the 1 bit added to the
		// end. 511 = 511 bits to get the number to be at least large enough
		// to require one block. 512 is the block size.
		$k = $L2 + 64 + 1 + 511;
		$k -= $k % 512 + $L2 + 64 + 1;
		$k >>= 3;  // convert to byte count

		$str .= chr(0x80) . str_repeat(chr(0), $k) . $l;

		assert('strlen($str) % 64 == 0');

		// break the binary string into 512-bit blocks
		preg_match_all( '#.{64}#', $str, $this->chunks );
		$this->chunks = $this->chunks[0];

		// H(0)
		$this->hash = array(
				1779033703,	-1150833019,
				1013904242,	-1521486534,
				1359893119,	-1694144372,
				528734635,	1541459225,
		);
	}
}
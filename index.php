<?php

/** Generates 8 digit random barcode number via the Mersenne Twister Random Number Generator.
 * @return int Returns a randomly generated 8-digit integer.
 */
function generateBarcode() {
    return mt_rand(10000000, 99999999);
}

?>
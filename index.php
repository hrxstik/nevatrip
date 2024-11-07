<?php

/** Generates 8 digit random barcode number via the Mersenne Twister Random Number Generator.
 * @return int Returns a randomly generated 8-digit integer.
 */
function generateBarcode() {
    return mt_rand(10000000, 99999999);
}

/**
 * Simulates a request to the API to book a ticket.
 *
 * @param string $url The URL to send the request to. Must be equal to "https://api.site.com/book".
 * @param array $data An associative array containing the data to be sent with the request.
 *                                                   (Currently not used in this simulation.)
 *
 * @return array|null Returns an array with a message indicating successful booking or an error.
 *
 * @throws InvalidArgumentException If an invalid URL is passed (not matching expected).
 */
function requestBookApi($url, $data) {
    if ($url === "https://api.site.com/book") {
        return rand(0, 1) ? ['message' => 'order successfully booked'] : ['error' => 'barcode already exists'];
    }
    else {
        throw new InvalidArgumentException("Invalid url (must be https://api.site.com/book)");
    }
}

/**
 * Simulates a request to the API to approve booking.
 *
 * @param string $url The URL to send the request to. Must be equal to "https://api.site.com/approve".
 * @param array $data An associative array containing the data to be sent with the request.
 *                                                  (Currently not used in this simulation.)
 *
 * @return array Returns an array with a message indicating successful booking or an error/
 *
 * @throws InvalidArgumentException If an invalid URL is passed (not matching expected).
 */
function requestApproveApi($url, $data) {
    if ($url === "https://api.site.com/approve") {
        $responses = [
            ['message' => 'order successfully approved'],
            ['error' => 'event cancelled'],
            ['error' => 'no tickets'],
            ['error' => 'no seats'],
            ['error' => 'fan removed']
        ];
        return $responses[array_rand($responses)];
    }
    else {
        throw new InvalidArgumentException("Invalid url (must be https://api.site.com/approve)");
    }
}

?>
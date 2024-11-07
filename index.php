<?php

/** Generates 8 digit random barcode number via the Mersenne Twister Random Number Generator.
 * @return int Returns a randomly generated 8-digit integer.
 */
function generateBarcode(): int
{
    return mt_rand(10000000, 99999999);
}
enum TicketType: string {
    case Adult = "adult";
    case Kid = "kid";
    case Group = "group";
    case Preferential = "preferential";
}
/**
 * Simulates a request to the API to book a ticket.
 *
 * @param string $url The URL to send the request to. Must be equal to "https://api.site.com/book".
 * @param array $data An associative array containing the data to be sent with the request.
 *                                                   (Currently not used in this simulation.)
 *
 * @return array Returns an array with a message indicating successful booking or an error.
 *
 * @throws InvalidArgumentException If an invalid URL is passed (not matching expected).
 */
function requestBookApi(string $url, array $data): array
{
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
function requestApproveApi(string $url, array $data): array
{
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

/**  Adds ticket order to database.
 *
 * @param int $event_id The ID of the event for which tickets are being ordered.
 *
 * (before normalization)
 * @param string $event_date The date of the event in 'YYYY-MMMM-DDDD hh:mm:ss' format.
 * @param int $ticket_adult_price The price of an adult ticket.
 * @param int $ticket_adult_quantity The quantity of adult tickets to be ordered.
 * @param int $ticket_kid_price The price of a child ticket.
 * @param int $ticket_kid_quantity The quantity of child tickets to be ordered.
 *
 * (after normalization)
 * @param int $ticket_price The price of a ticket.
 * @param string $ticket_type The type of ticket (DOES NOT CONTAIN PRICE LOGIC)
 *
 * @return void This function does not return a value. It outputs messages indicating success or failure.
 */

function addOrder(int $event_id, string $ticket_type, int $ticket_price): void
{
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'nevatrip';

    $mysqli = new mysqli($host, $username, $password, $database);

    if ($mysqli->connect_error) {
        die("Ошибка подключения: " . $mysqli->connect_error);
    }

    $barcode = generateBarcode();

    $maxAttempts = 5;
    $attempts = 0;

    while ($attempts < $maxAttempts) {
        $bookingResponse = requestBookApi("https://api.site.com/book", [
            'event_id' => $event_id,
//            'event_date' => $event_date,
//            'ticket_adult_price' => $ticket_adult_price,
//            'ticket_adult_quantity' => $ticket_adult_quantity,
//            'ticket_kid_price' => $ticket_kid_price,
//            'ticket_kid_quantity' => $ticket_kid_quantity,
            'ticket_type' => $ticket_type,
            'ticket_price' => $ticket_price,
            'barcode' => $barcode
        ]);

        if (isset($bookingResponse['message'])) {

            $approvalResponse = requestApproveApi("https://api.site.com/approve", ['barcode' => $barcode]);
            if (isset($approvalResponse['message'])) {
                //$equal_price = ($ticket_adult_price * $ticket_adult_quantity) + ($ticket_kid_price * $ticket_kid_quantity);

                //$statement= $mysqli->prepare("INSERT INTO orders (event_id, event_date, ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity, barcode, equal_price, created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                //$statement->bind_param("issiiisi", $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode, $equal_price);

                $statement = $mysqli->prepare("INSERT INTO orders (barcode, event_id, event_date, ticket_type, ticket_price, created) VALUES (?, ?, NOW(), ?, ?, NOW())");
                $statement->bind_param("sisi", $barcode, $event_id, $ticket_type, $ticket_price);
                if ($statement->execute()) {
                    echo "Заказ добавлен успешно. Штрих-код: " . $barcode;
                }
                else {
                    echo "Ошибка при добавлении заказа: " . $statement->error;
                }
                $statement->close();
                break;
            }
            else {
                echo "Ошибка подтверждения: " . $approvalResponse['error'];
                break;
            }
        } else {
            if ($bookingResponse['error'] === 'barcode already exists') {
                $barcode = generateBarcode();
                $attempts++;
            }
            else {
                echo "Неизвестная ошибка: " . $bookingResponse['error'];
                break;
            }
        }
    }
    $mysqli->close();
}

addOrder(3, TicketType::Kid->value, 200);

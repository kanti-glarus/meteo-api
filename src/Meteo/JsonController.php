<?php

namespace App\Meteo;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class JsonController
{
    private $possibleTypes = [
        'air_temperature' => [
            'db_name' => 'lufttemp',
            'unit' => '°C',
        ],
        'air_humidity' => [
            'db_name' => 'luftfeucht',
            'unit' => '%',
        ],
        'air_pressure' => [
            'db_name' => 'luftdruck',
            'unit' => 'hPa',
        ],
        'sunlight' => [
            'db_name' => 'einstrahlung',
            'unit' => 'W/m²',
        ],
        'sun_uv' => [
            'db_name' => 'uv',
            'unit' => '',
        ],
        'wind_speed' => [
            'db_name' => 'windgeschw',
            'unit' => 'm/s',
        ],
        'wind_speed_max' => [
            'db_name' => 'maxwindgeschw',
            'unit' => 'm/s',
        ],
        'wind_direction' => [
            'db_name' => 'windrichtung',
            'unit' => 'º',
        ],
        'wind_deviation' => [
            'db_name' => 'windabweichung',
            'unit' => '',
        ],
        'rainfall' => [
            'db_name' => 'niederschlag',
            'unit' => 'mm',
        ],
        'snow' => [
            'db_name' => 'schnee',
            'unit' => 'm',
        ],
    ];

    public function single(Request $request, Response $response, $args)
    {
        $type = $args['type'];

        // Check if type is prepared.
        if (!in_array($type, array_keys($this->possibleTypes))) {
            $data = [
                'error' => '400',
                'message' => "Type '$type' not found."
            ];
        }

        // Go and get the data.
        else {
            $data = $this->loadCurrentData($this->possibleTypes[$type]['db_name'], $type);
            $data['unit'] = $this->possibleTypes[$type]['unit'];
        }

        // Return single Element.
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Private functions.
     */

    /**
     * Load current data from the database, load one single element.
     *
     * @param $name
     * @param $returned_name
     * @return array|bool|\mysqli_result
     */
    private function loadCurrentData($name, $returned_name)
    {
        $databaseConnection = new DatabaseConnection();
        $result = $databaseConnection->loadSingleData($name, $returned_name);
        return $result;
    }
}
<?php

namespace App\Meteo;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class JsonController
{
    private $possibleTypes = [
        'air_temperature' => [
            'db_name' => 'lufttemp',
            'unit' => 'ºC',
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
     * Load multiple data as JSON
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function multiple(Request $request, Response $response, $args)
    {
        $data = $this->loadMultipleData();

        // Return the elements.
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Load multiple data as CSV
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function multiple_csv(Request $request, Response $response, $args)
    {
        $data = $this->loadMultipleData();
        $keys = array_keys($data[0]);

        $out = fopen('php://temp', 'w');
        fputcsv($out, $keys);
        foreach ($data as $row) {
            fputcsv($out, array_values($row));
        }
        rewind($out);
        $csvData = stream_get_contents($out);
        fclose($out);

        // Return the elements.
        $response->getBody()->rewind();
        $response->getBody()->write($csvData);
        return $response->withHeader('Content-Type', 'application/csv');
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

    private function loadMultipleData() {
        if (!isset($_GET['start']) || !isset($_GET['stop'])) {
            return [
                'error' => '400',
                'message' => "Start or Stop parameter was not found."
            ];
        }

        $start = $_GET['start'];
        $stop = $_GET['stop'];
        $delta_raw = "10";
        if (isset($_GET['delta'])) {
            $delta_raw = $_GET['delta'];
        }

        $types = [];

        foreach ($this->possibleTypes as $key => $type) {
            $types[$key] = $type['db_name'];
        }

        $delta = $delta_raw / 10;
        return $this->loadDataOverTime($start, $stop, $delta, $types);
    }

    /**
     * Load current data from the database, load one single element.
     *
     * @param $start
     * @param $stop
     * @param $delta
     * @param $types
     * @return array|bool|\mysqli_result
     */
    private function loadDataOverTime($start, $stop, $delta, $types)
    {
        $databaseConnection = new DatabaseConnection();
        $result = $databaseConnection->loadDataOverTime($start, $stop, $delta, $types);
        return $result;
    }
}

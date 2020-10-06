<?php

namespace App\Meteo;

use mysqli;
use DateTime;

class DatabaseConnection
{
    private $connection;

    public function __construct()
    {
        $this->connection = new mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD'] === "null" ? null : $_ENV['DB_PASSWORD'],
            $_ENV['DB_NAME'],
            $_ENV['DB_PORT']
        );
    }

    public function loadSingleData($name, $returned_name) {
        if ($error = $this->checkConnectionError()) {
            return $error;
        }

        $query = "SELECT $name, `id`, `datum`, `zeit` FROM `wetterdata` ORDER BY `id` DESC LIMIT 1";
        $result = $this->connection->query($query);
        $this->connection->close();

        if ($result->num_rows === 1) {
            $assoc = $result->fetch_assoc();
            $date_raw = $assoc['datum'] . ' ' . $assoc['zeit'];
            $date_raw = str_replace('`', '', $date_raw);
            $date = DateTime::createFromFormat('d.m.Y H:i', $date_raw);


            return [
                $returned_name => $assoc[$name],
                'id' => $assoc['id'],
                'date' => $date->format('Y.m.d H:i')
            ];
        }

        return $result;
    }

    private function checkConnectionError() {
        if ($this->connection->connect_error) {
            return [
                'error' => 'mysql',
                'message' => $this->connection->connect_error,
            ];
        }
        return false;
    }
}
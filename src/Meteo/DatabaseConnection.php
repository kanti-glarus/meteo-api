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

    public function loadDataOverTime($start, $stop, $delta, $types) {
        if ($error = $this->checkConnectionError()) {
            return $error;
        }

        $name = "";
        foreach ($types as $key => $type) {
            $name .= " `$type`, ";
        }

        $start_date = $this->getDateForDatabase($start);
        $stop_date = $this->getDateForDatabase($stop);
        $start_time = $this->getTimeForDatabase($start);
        $stop_time = $this->getTimeForDatabase($stop);

        $query = "WITH ordering AS (
            SELECT ROW_NUMBER() OVER (ORDER BY `id` DESC) AS n, `wetterdata`.* 
            FROM `wetterdata`
            WHERE `id` >= (SELECT `id` FROM `wetterdata` WHERE `zeit` = '$start_time' AND `datum` = '$start_date')
            AND `id` <= (SELECT `id` FROM `wetterdata` WHERE `zeit` = '$stop_time' AND `datum` = '$stop_date')
            ORDER BY `id` DESC
        )
        SELECT * FROM ordering WHERE MOD(n, $delta) = 0;";

        $result = $this->connection->query($query);
        $this->connection->close();

        if ($result->num_rows) {
            $all = $result->fetch_all(MYSQLI_ASSOC);
            $entries = [];

            foreach ($all as $raw) {
                $date_raw = $raw['datum'] . ' ' . $raw['zeit'];
                $date_raw = str_replace('`', '', $date_raw);
                $date = DateTime::createFromFormat('d.m.Y H:i', $date_raw);

                $entry = [
                    'id' => $raw['id'],
                    'date' => $date->format('Y.m.d H:i'),
                ];

                foreach ($types as $key => $name) {
                    $entry[$key] = $raw[$name];
                }

                $entries[] = $entry;
            }

            return $entries;
        }

        return $result;
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

    private function getTimeForDatabase($timestamp) {
        $hours = date('H', $timestamp);
        $minutes = date('i', $timestamp);
        $minutes_rounded = round(intval($minutes) / 10) * 10;

        if ($minutes_rounded === 0.0) {
            $minutes_rounded = '00';
        }

        $time = $hours . ':' . $minutes_rounded;

        if ($time === '00:00') {
            $time = '24:00';
        }

        return '`' . $time . '`';
    }

    private function getDateForDatabase($timestamp) {
        return date('`d.m.Y`', $timestamp);
    }
}
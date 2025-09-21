<?php

namespace App\Services\Import;

class Csv {

    public static function parseCsv($filename, $delimiter = ','): array
    {
        $header = null;
        $data = [];

        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if(!$header) {
                    // remove BOM only from the first line CSV, one time
                    $row = preg_replace('/^\x{FEFF}/u', '', $row);
                    $header = self::lower($row);
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    private static function lower(array $arr) :array
    {
        $tmp = [];

        foreach ($arr as $item) {
            $tmp[] = strtolower($item);
        }

        return $tmp;
    }
}
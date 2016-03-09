<?php

require_once __DIR__ . '/Helper.php';
require_once __DIR__ . '/Collections.php';

/**
 * A class that abstracts the details of performing CRUD operations
 * on CSV files in a folder.
 */
class Storage {

    /**
     * The CSV separator.
     * @type string
     */
    const CSV_SEPARATOR = '|';

    /**
     * Path to the folder where the stores are stored.
     * @type string
     */
    private $folder;

    /**
     * Constructor for Storage
     * @param string $folder Path to the folder where all the CSV files will be stored.
     */
    public function __construct($folder) {
        $this->folder = $folder;
    }

    /**
     * Defines a store. Can be called multiple times with the same arguments safely.
     * @param string $name The name of the store.
     * @param array $attributes The meta data for the store.
     */
    public function defineStore($name, $attributes) {
        if (!file_exists($this->folder))
            mkdir($this->folder);

        $re = "/^[a-z_]{1,250}$/";
        preg_match($re, $name, $matches);

        if (count($matches) == 0)
            throw new Exception("The name of a store must match the following regex: $re");

        if (!file_exists($this->fname($name))) {
            $metaDataRow = $this->arrayToColumns(array_merge(['id'], $attributes));
            file_put_contents($this->fname($name), $metaDataRow);
        }
    }

    /**
     * Deletes a store.
     * @param string $name The name of the store.
     */
    public function deleteStore($name) {
        unlink($this->fname($name));
    }

    /**
     * Inserts an entry into the store.
     * @param string $name The name of the store.
     * @param array $values The values to insert into the store.
     */
    public function insertIntoStore($name, $values) {
        $metaDataColumns = $this->getMetaDataForStore($name);

        if (array_count_values($metaDataColumns) != array_count_values(array_keys(array_merge(['id' => -1], $values)))) {
            throw new Exception("Not all the attributes were populated.");
        }

        $entries = $this->allInStore($name);

        if (!is_array($entries)) {
            $entries = [];
        }

        $ids = Collections::map($entries, function($entry) {
            return $entry['id'];
        });

        $id = count($ids) > 0 ? max($ids) + 1: 1;

        $operationalDataColumns = [
            'id' => $id,
        ];

        foreach ($metaDataColumns as $metaDataColumn) {
            if ($metaDataColumn == 'id')
                continue;
            $operationalDataColumns[$metaDataColumn] = $values[$metaDataColumn];
        }

        $operationalDataRow = $this->arrayToColumns($operationalDataColumns);

        file_put_contents($this->fname($name), "\r\n$operationalDataRow", FILE_APPEND);
    }

    /**
     * Searches the store.
     * @param string $name The name of the store.
     * @return array Array entries.
     */
    public function allInStore($name) {
        return $this->searchInStore($name, function($entry) {
            return true;
        });
    }

    /**
     * Searches the store.
     * @param string $name The name of the store.
     * @param function $cb A callback function whose first parameter will be a given entry in the store.
     * @return array Array of matched entries.
     */
    public function searchInStore($name, $cb) {
        $metaDataColumns = $this->getMetaDataForStore($name);

        try {
            $operationalDataRows = $this->getOperationalDataForStore($name);

            $makeArg = function($operationalDataColumns) use($metaDataColumns) {
                $argument = [];
                foreach ($metaDataColumns as $index => $metaDataColumn) {
                    $argument[$metaDataColumn] = $operationalDataColumns[$index];
                }
                return $argument;
            };

            $args = Collections::map($operationalDataRows, $makeArg);

            $matchingOperationalDataRows = Collections::filter($args, function($argument) use($cb) {
                return $cb($argument);
            });

            return $matchingOperationalDataRows;
        } catch (Exception $ex) {
            error_log($ex);
        }
    }

    /**
     * Updates elements in the store.
     * @param string $name The name of the store.
     * @param array $values The values to update in the store.
     * @param function $cb A callback function whose first parameter will be a given entry in the store.
     */
    public function updateInStore($name, $values, $cb) {
        $metaDataColumns = $this->getMetaDataForStore($name);

        try {
            $operationalDataRows = $this->getOperationalDataForStore($name);

            if (count($operationalDataRows) == 0)
                return;

            $makeArg = function($operationalDataColumns) use($metaDataColumns) {
                $argument = [];
                foreach ($metaDataColumns as $index => $metaDataColumn) {
                    $argument[$metaDataColumn] = $operationalDataColumns[$index];
                }
                return $argument;
            };

            $args = Collections::map($operationalDataRows, $makeArg);

            $updatedOperationalDataRows = Collections::map($args, function($argument) use($cb, $values) {
                if ($cb($argument)) {
                    foreach (array_keys($values) as $key) {
                        if (array_key_exists($key, $argument)) {
                            $argument[$key] = $values[$key];
                        }
                    }
                }
                return $argument;
            });

            $updatedFileContents = $this->arrayToColumns($metaDataColumns);

            if (count($updatedOperationalDataRows) > 0) {
                $updatedFileContents .= "\r\n";

                foreach ($updatedOperationalDataRows as $index => $updatedOperationalDataRow) {
                    $updatedFileContents .= $this->arrayToColumns($updatedOperationalDataRow);

                    if ($index != count($updatedOperationalDataRows) - 1)
                        $updatedFileContents .= "\r\n";
                }
            }

            file_put_contents($this->fname($name), $updatedFileContents);
        } catch (Exception $ex) {
            error_log($ex);
        }
    }

    /**
     * Removes an entry/entries from the store.
     * @param string $name The name of the store.
     * @param function $cb A callback function whose first parameter will be a given entry in the store.
     */
    public function removeFromStore($name, $cb) {
        $metaDataColumns = $this->getMetaDataForStore($name);

        try {
            $operationalDataRows = $this->getOperationalDataForStore($name);

            if (count($operationalDataRows) == 0)
                return;

            $updatedOperationalDataRows = Collections::filter($operationalDataRows,
                    function($operationalDataColumns) use($metaDataColumns, $cb) {
                $argument = [];
                foreach ($metaDataColumns as $index => $metaDataColumn) {
                    $argument[$metaDataColumn] = $operationalDataColumns[$index];
                }
                return !$cb($argument);
            });

            $updatedFileContents = $this->arrayToColumns($metaDataColumns);

            if (count($updatedOperationalDataRows) > 0) {
                $updatedFileContents .= "\r\n";

                foreach ($updatedOperationalDataRows as $index => $updatedOperationalDataRow) {
                    $updatedFileContents .= $this->arrayToColumns($updatedOperationalDataRow);

                    if ($index != count($updatedOperationalDataRows) - 1)
                        $updatedFileContents .= "\r\n";
                }
            }

            file_put_contents($this->fname($name), $updatedFileContents);
        } catch (Exception $ex) {
            error_log($ex);
        }
    }

    /**
     * Returns the top row of the CSV file which specifies the meta data of the store.
     * @param string $name The name of the store.
     * @return array An array of columns (strings)
     */
    private function getMetaDataForStore($name) {
        return $this->columnsToArray(trim(file($this->fname($name))[0]));
    }

    /**
     * Returns the operational data rows for the store.
     * @param string $name The name of the store.
     * @return array Operational data rows
     */
    private function getOperationalDataForStore($name) {
        $lines = file($this->fname($name));

        if (count($lines) < 2)
            throw new Exception("No operational data in the store.");

        return Collections::map(array_slice($lines, 1), function($columnsString) {
            return $this->columnsToArray(trim($columnsString));
        });
    }

    /**
     * Converts an array into a CSV row (columns) in the form of a string.
     * @param array $array
     * @return string
     */
    private function arrayToColumns($array) {
        foreach ($array as $elem) {
            // check if $elem contains the CSV separator
            if (mb_strpos($elem, self::CSV_SEPARATOR) !== false) {
                throw new Exception(self::CSV_SEPARATOR . ' is not permitted.');
            }
        }
        return implode(self::CSV_SEPARATOR, $array);
    }

    /**
     * Takes a CSV string (1 row) and converts it to an array
     * @param string $columnString
     * @return array
     */
    private function columnsToArray($columnsString) {
        return explode(self::CSV_SEPARATOR, $columnsString);
    }

    /**
     * Get the file name for the name of the store.
     * @return string Path to the file.
     */
    private function fname($name) {
        return "$this->folder/$name.csv";
    }
}

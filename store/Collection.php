<?php
namespace Store;

require_once __DIR__ . '/../misc/functions.php';

/**
 * A collection is a class that makes it easy
 * to read from and write to CSV files.
 */
class Collection {

    /**
     * @type string
     */
    const CSV_SEPARATOR = ',';

    /**
     * @type \Store\Store
     */
    private $store;
    /**
     * Name of the store.
     * @type string
     */
    private $name;
    /**
     * A list of the columns at the top of the CSV file.
     * @type array
     */
    private $metaRow;
    /**
     * A list of rows in the CSV file.
     * @type array (2 dimensions)
     */
    private $operationalRows;

    /**
     * Collection constructor.
     * @param \Store\Store $store
     * @param string $name
     */
    public function __construct($store, $name) {
        $this->store = $store;
        $this->name = $name;

        if (!file_exists($this->getFilePath())) {
            // If the file doesn't exist start off with empty properties.
            $this->metaRow = [];
            $this->operationalRows = [];
        } else {
            // If the file does exist, read the contents of it into the properties.

            $f = fopen($this->getFilePath(), 'r');

            $lines = [];

            while (!feof($f)) {
                $line = fgetcsv($f, 0, self::CSV_SEPARATOR);
                if ($line) {
                    $lines[] = $line;
                }
            }

            fclose($f);

            if (count($lines) == 0) {
                $this->metaRow = [];
                $this->operationalRows = [];
            } else {
                $this->metaRow = $lines[0];
                $this->operationalRows = [];

                $rows = array_slice($lines, 1);

                foreach ($rows as $columns) {
                    $args = [];
                    foreach ($this->metaRow as $index => $metaColumn) {
                        if ($columns[$index] == 'null')
                            $columns[$index] = null;

                        $args[$metaColumn] = $columns[$index];
                    }
                    $this->operationalRows[] = $args;
                }
            }
        }
    }

    /**
     * Sets the columns at the top of the CSV file.
     * @param array $attributes
     */
    public function setAttributes($attributes) {
        $this->metaRow = $attributes;
    }

    /**
     * Gets the columns at the top of the CSV file.
     * @return array
     */
    public function getAttributes() {
        return $this->metaRow;
    }

    /**
     * Appends a row of data to the end of the CSV file.
     * @param array $row
     */
    public function addRow($row) {
        if (!is_array($row))
            throw InvalidArgumentException('$row must be an array');
        if (count($row) != count($this->metaRow))
            throw InvalidArgumentException('Invalid number of columns');

        $this->operationalRows[] = $row;
    }

    /**
     * Searches for rows that match a given query.
     * @param function $cb Callback. A row is passed to it as its first parameter.
     * @return array
     */
    public function searchRows($cb) {
        return filter($this->operationalRows, function($operationalRow) use($cb) {
            return $cb($operationalRow);
        });
    }

    /**
     * Same as #searchRows but returns one result instead of a list of results.
     * If no result is found, null is returned.
     * @param function $cb Callback. A row is passed to it as its first parameter.
     * @return array
     */
    public function searchRow($cb) {
        $rows = $this->searchRows($cb);

        if (count($rows) == 0) {
            return null;
        }

        return $rows[0];
    }

    /**
     * Updates rows in the store.
     * @param array $values Values that overwrite the current values in the collection.
     * @param function $cb Callback. A row is passed to it as its first parameter.
     */
    public function updateRows($values, $cb) {
        $this->operationalRows = map($this->operationalRows, function($operationalRow) use($values, $cb) {
            if ($cb($operationalRow)) {
                foreach (array_keys($values) as $key) {
                    if (array_key_exists($key, $operationalRow)) {
                        $operationalRow[$key] = $values[$key];
                    }
                }
            }
            return $operationalRow;
        });
    }

    /**
     * Removes rows in the store.
     * @param array $values Values that overwrite the current values in the collection.
     * @param function $cb Callback. A row is passed to it as its first parameter.
     */
    public function removeRows($cb) {
        $this->operationalRows = filter($this->operationalRows, function($operationalRow) use($cb) {
            return !$cb($operationalRow);
        });
    }

    /**
     * Gets all the operational data rows in the store. Use #searchRows() to get
     * more specific results.
     * @return array
     */
    public function getRows() {
        return $this->operationalRows;
    }

    /**
     * Writes changes to the CSV file.
     */
    public function save() {
        $f = fopen($this->getFilePath(), 'w');

        $rows = map(array_values($this->operationalRows), function($row) {
            foreach ($row as &$column) {
                if (is_bool($column))
                    $column = $column ? '1' : '0';
                if (is_null($column))
                    $column = 'null';
            }
            return $row;
        });

        $lines = array_merge([$this->metaRow], $rows);

        foreach ($lines as $line) {
            fputcsv($f, $line, self::CSV_SEPARATOR);
        }

        fclose($f);
    }

    /**
     * Gets the path to the CSV file.
     * @return string
     */
    public function getFilePath() {
        return $this->store->getFolderPath() . "/$this->name.csv";
    }
}

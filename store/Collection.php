<?php
namespace Store;

require_once __DIR__ . '/../misc/functions.php';

class Collection {

    const CSV_SEPARATOR = ',';

    private $store;
    private $name;
    private $metaRow;
    private $operationalRows;

    public function __construct($store, $name) {
        $this->store = $store;
        $this->name = $name;

        if (!file_exists($this->getFilePath())) {
            $this->metaRow = [];
            $this->operationalRows = [];
        } else {
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

    public function setAttributes($attributes) {
        $this->metaRow = $attributes;
    }

    public function getAttributes() {
        return $this->metaRow;
    }

    public function addRow($row) {
        if (!is_array($row))
            throw InvalidArgumentException('$row must be an array');
        if (count($row) != count($this->metaRow))
            throw InvalidArgumentException('Invalid number of columns');

        $this->operationalRows[] = $row;
    }

    public function searchRows($cb) {
        return filter($this->operationalRows, function($operationalRow) use($cb) {
            return $cb($operationalRow);
        });
    }

    public function searchRow($cb) {
        $rows = $this->searchRows($cb);

        if (count($rows) == 0) {
            error_log('Collection::searchRow() found no matching rows.');
            return null;
        }

        return $rows[0];
    }

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

    public function removeRows($cb) {
        $this->operationalRows = filter($this->operationalRows, function($operationalRow) use($cb) {
            return !$cb($operationalRow);
        });
    }

    public function getRows() {
        return $this->operationalRows;
    }

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

    public function getFilePath() {
        return $this->store->getFolderPath() . "/$this->name.csv";
    }
}

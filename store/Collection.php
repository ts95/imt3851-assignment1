<?php
namespace Store;

require_once __DIR__ . '/../misc/functions.php';

class Collection {

    const CSV_SEPARATOR = ',';

    private $store;
    private $name;
    private $metaRow;
    private $operationalRows;

    /**
     * Join two collections on a given attribute in each collection and return
     * the result.
     *
     * @param string $rows1 First collection
     * @param string $rows2 Second collection
     * @param string $attr1 First attribute name
     * @param string $attr2 Second attribute name
     * @param string $newAttr Name of the join attribute that will be returned
     * @return array
     */
    public static function join($rows1, $rows2, $attr1, $attr2, $newAttr = null) {
        if (is_null($newAttr))
            $newAttr = $attr1;

        $joinedRows = [];

        foreach ($rows1 as $row1) {
            foreach ($rows2 as $row2) {
                if (!isset($row1[$attr1]))
                    continue;
                if (!isset($row2[$attr2]))
                    continue;

                $col1 = $row1[$attr1];
                $col2 = $row2[$attr2];

                if ($col1 == $col2) {
                    unset($row1[$attr1]);
                    unset($row2[$attr2]);

                    $joinedRows[] = array_merge($row1, $row2, [
                        $newAttr => $col1,
                    ]);
                }
            }
        }

        return $joinedRows;
    }

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

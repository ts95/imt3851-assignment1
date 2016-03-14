<?php
namespace Store;

require_once __DIR__ . '/../misc/Helper.php';
require_once __DIR__ . '/Collection.php';

/**
 * A store represents the folder in which the CSV files
 * are stored. The CSV files are represented by the
 * collection class.
 */
class Store {

    /**
     * A path to the folder.
     * @type string
     */
    private $folderPath;

    /**
     * Store constructor
     * @param string $folderPath
     */
    public function __construct($folderPath) {
        $this->folderPath = $folderPath;
    }

    /**
     * @return string
     */
    public function getFolderPath() {
        return $this->folderPath;
    }

    /**
     * Checks if a given collection (CSV file) exists.
     * @param string $name
     * @return bool
     */
    public function collectionExists($name) {
        return file_exists("$this->folderPath/$name.csv");
    }

    /**
     * Creates a collection.
     * NB: The collection is not saved. That must be done manually by calling
     * #save() on the returned object.
     * @param string $name
     * @param array $attributes
     * @return \Store\Collection
     */
    public function makeCollection($name, $attributes) {
        $collection = $this->getCollection($name);
        $collection->setAttributes($attributes);
        return $collection;
    }

    /**
     * Gets a collection.
     * @param string $name
     * @return \Store\Collection
     */
    public function getCollection($name) {
        if (!file_exists($this->folderPath))
            mkdir($this->folderPath);

        $re = "/^[a-z_]{1,250}$/";
        preg_match($re, $name, $matches);
        if (count($matches) == 0)
            throw new Exception("The name of a collection must match the following regex: $re");

        return new \Store\Collection($this, $name);
    }
}

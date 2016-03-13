<?php
namespace Store;

require_once __DIR__ . '/../misc/Helper.php';
require_once __DIR__ . '/Collection.php';

class Store {

    private $folderPath;

    public function __construct($folderPath) {
        $this->folderPath = $folderPath;
    }

    public function getFolderPath() {
        return $this->folderPath;
    }

    public function collectionExists($name) {
        return file_exists("$this->folderPath/$name.csv");
    }

    public function makeCollection($name, $attributes) {
        $collection = $this->getCollection($name);
        $collection->setAttributes($attributes);
        return $collection;
    }

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

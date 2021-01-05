<?php

require_once './vendor/autoload.php';

$db = new Medoo\Medoo(database());

$db->create('category_links',[
    "id" => ["INT", "NOT NULL", "AUTO_INCREMENT", "PRIMARY KEY"],
    "link" => ["VARCHAR(255)", "NOT NULL"],
	"scanned" => ["TINYINT(1)", "NOT NULL"],
]);

$db->create('product_links',[
    "id" => ["INT", "NOT NULL", "AUTO_INCREMENT", "PRIMARY KEY"],
    "category_id" => ["INT", "NOT NULL"],
    "name" => ["VARCHAR(255)", "NOT NULL"],
    "link" => ["VARCHAR(255)", "NOT NULL"],
    "scanned" => ["TINYINT(1)", "NOT NULL"],
]);

echo "DONE...";
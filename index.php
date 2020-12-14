<?php

require_once './vendor/autoload.php';

$domain = 'https://www.erosklad.com';

$dbSettings = [
    'database_type' => 'mysql',
    'database_name' => 'ero_parser',
    'server' => 'localhost',
    'username' => 'root',
    'password' => 'root'
];

$db = new Medoo\Medoo($dbSettings);

if ($db->count('categories') == 0) {
    $db->insert('categories', App\Helpers\EroskladParser::factory()->getCategories());
    die;
}

$category = $db->get('categories', ['id', 'link'], ['scanned' => 0]);

if ($category) {
    $productsLinks = App\Helpers\EroskladParser::factory()
        ->setUrl($domain.$category['link'])
        ->getProducts();
    $db->insert('product_links', $productsLinks);
    $db->update('categories', ['scanned' => 1], ['id' => $category['id']]);
    die;
}

$productsLinks = $db->select('product_links', ['id', 'link'], [
    'scanned' => 0,
    'LIMIT' => 10,
]);

if ($productsLinks) {
    foreach ($productsLinks as $item) {
        $product = App\Helpers\EroskladParser::factory()->setUrl($item['link'])->getProduct();
        $product['parameters'] = json_encode($product['parameters']);
        $db->update('product_links', ['scanned' => 1], ['id' => $item['id']]);
        if (!$db->has('products', ['mark' => $product['mark']])) {
            $db->insert('products', $product);
        }
    }
    die;
}

echo "Done...";
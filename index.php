<?php

require_once './vendor/autoload.php';

use App\Helpers\{EroskladParser, FileSystem};

$domain = 'https://www.erosklad.com';

//прописать полный путь к папке с изображениями, иначе с крона работать не будет!

$imagePath = 'C:/openserver/OpenServer/domains/sex-shop.loc/images/';

$db = new Medoo\Medoo(database());

if ($db->count('category_links') == 0) {
    $categories = EroskladParser::factory()->getCategories();
    foreach ($categories as  $category) {
        $db->insert('categories', ['name' => $category['name']]);
        $db->insert('category_links', [
            'link' => $category['link'],
            'scanned' => 0,
        ]);
    }
}

$category = $db->get('category_links', ['id', 'link'], ['scanned' => 0]);

if ($category) {
    $productsLinks = EroskladParser::factory()
        ->setUrl($domain.$category['link'])
        ->getProducts();
    foreach ($productsLinks as $key => $productsLink) {
        $productsLinks[$key]['category_id'] = $category['id'];
    }
    $db->insert('product_links', $productsLinks);
    $db->update('category_links', ['scanned' => 1], ['id' => $category['id']]);
    die;
}

$productsLinks = $db->select('product_links', ['id', 'link', 'category_id'], [
    'scanned' => 0,
    'LIMIT' => 10,
]);

if ($productsLinks) {
    foreach ($productsLinks as $item) {

        $db->update('product_links', ['scanned' => 1], ['id' => $item['id']]);

        $product = EroskladParser::factory()->setUrl($item['link'])->getProduct();

        if (!$db->has('products', ['mark' => $product['mark']])) {
            $product['mainInfo']['category_id'] = $item['category_id'];
            $db->insert('products', $product['mainInfo']);
            $productId = $db->id();

            $product['photos'] = array_merge(
                [['photo' => $product['coverPhoto']]],
                $product['photos']
            );

            foreach ($product['photos'] as $key => $photoObject) {
                $type = explode('.', $photoObject['photo']);
                $imageName = str_replace('/', '_', $product['mainInfo']['mark']).'_'.$key.'.'.$type[1];
                $localImage = $imagePath.$imageName;
                FileSystem::saveFile($domain.$photoObject['photo'], $localImage);
                $db->insert('product_photos',[
                    'product_id' => $productId,
                    'name' => $imageName,
                    'cover' => ($key === 0) ? 1 : 0,
                ]);
            }

            if (is_array($product['parameters'])) {
                foreach ($product['parameters'] as $key => $parameter) {
                    $existingParam = $db->get('parameters', ['id'],  ['name' => $parameter['attribute']]);
                    if (!$existingParam) {
                        $db->insert('parameters', [
                            'name' => $parameter['attribute'],
                            'type' => 1,
                            'unit' => '',
                        ]);
                        $paramId = $db->id();
                    } else {
                        $paramId = $existingParam['id'];
                    }
                    $db->insert('product_parameters', [
                        'product_id' => $productId,
                        'parameter_id' => $paramId,
                        'value' => $parameter['value']
                    ]);
                }
            }
        }
    }
}

echo "Done...";
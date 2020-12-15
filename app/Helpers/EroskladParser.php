<?php

namespace App\Helpers;

use Symfony\Component\DomCrawler\Crawler;

class EroskladParser extends WebTraveler
{
    public function getCategories()
    {
        $pageUrl = 'https://www.erosklad.com/catalog/';
        $page = $this->setUrl($pageUrl)->request();
        $crawler = new Crawler($page);
        $categories = $crawler->filter('.left-nav__menu-body li a')->each(
            function (Crawler $node, $i) {
                if (!preg_match('~vendor*~', $node->attr('href'), $matches)) {
                    return [
                        'name' => $node->html(),
                        'link' => $node->attr('href').'?wp=1',
                    ];
                }
            }
        );
        return array_diff($categories, ['']);
    }

    public function getProducts()
    {
        $crawler = new Crawler($this->request());
        return $crawler->filter('.catalogue__list-item-title a')->each(
            function (Crawler $node, $i) {
                return [
                    'name' => trim($node->html()),
                    'link' => $node->attr('href'),
                ];
            }
        );
    }

    public function getProduct()
    {
        $crawler = new Crawler($this->request());
        $mark = $crawler->filter('.item-card__short-info-article')->text();
        $clearMark = trim(explode(':', $mark)[1]);
        $parameters = $crawler->filter('.item-card__info-characteristic-table tr')->each(
            function (Crawler $node, $i) {
                return [
                    'attribute' => $node->filter('.title')->text(),
                    'value' => $node->filter('td')->eq(1)->text(),
                ];
            }
        );
        $photo = $crawler->filter('.item-card__image-box a')->attr('href');
        return [
            'mark' => $clearMark,
            'name' => $crawler->filter('.item-card__title')->html(),
            'description' => $crawler
                ->filter('div[data-descriptionswitch=description]')->html(),
            'parameters' => $parameters,
            'photo' => $photo,
            'price' => 0,
        ];
    }
}
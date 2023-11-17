<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DiDom\Document;
use DiDom\Query;

class Parcer extends Controller
{
    private $headers, $options, $page, $headersPost, $prod;

    public function __construct()
    {
        $this->headers = [
            ':authority: www.electronictoolbox.com',
            ':method: GET',
            ':scheme: https',
            'Accept: */*',
            'Accept-Encoding: ',
            'Accept-Language: ru,en;q=0.9',
            'Cookie: xid57=10a930e282f911ee83b50242c0a8e002; _gcl_au=1.1.1069288428.1699971673; _ga=GA1.1.156370943.1699971673; cf_clearance=gPT.GZ9yGJtv.7puyLhLwPhRmYoyMAru3SvT.Zulf60-1700073115-0-1-3a2736df.d582778.3959c15b-0.2.1700073115; _ga_MVTMKKCKT6=GS1.1.1700050493.2.1.1700073703.60.0.0; _ga_RL1YCHPY0L=GS1.1.1700067774.5.1.1700073703.60.0.0',
            'Referer: https:/www.electronictoolbox.com/category/new',
            'Sec-Ch-Ua: "Chromium";v="116", "Not)A;Brand";v="24", "YaBrowser";v="23"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "Windows"',
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Site: same-origin',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.5845.837 YaBrowser/23.9.4.837 Yowser/2.5 Safari/537.36',
            'X-Requested-With: XMLHttpRequest',
        ];

        $this->headersPost = [
            ':authority: www.electronictoolbox.com',
            ':method: POST',
            ':path: /api-client/product/get',
            ':scheme: https',
            'Accept: application/json, text/plain, */*',
            'Accept-Encoding: ',
            'Accept-Language: ru,en;q=0.9',
            'Content-Length: 22',
            'Content-Type: application/json',
            'Cookie: xid57=10a930e282f911ee83b50242c0a8e002; _gcl_au=1.1.1069288428.1699971673; _ga=GA1.1.156370943.1699971673; cf_clearance=eZAKJxY_.YlHKcoMrfPdAFD48QSIzLZqh_4nokN8f2o-1700165288-0-1-3a2736df.d582778.3959c15b-0.2.1700165288; _ga_MVTMKKCKT6=GS1.1.1700152585.4.1.1700166667.59.0.0; _ga_RL1YCHPY0L=GS1.1.1700159378.10.1.1700166667.59.0.0',
            'Sec-Ch-Ua: "Chromium";v="116", "Not)A;Brand";v="24", "YaBrowser";v="23"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "Windows"',
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Site: same-origin',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.5845.837 YaBrowser/23.9.4.837 Yowser/2.5 Safari/537.36',
            'X-Requested-With: XMLHttpRequest',
        ];

        $this->options = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER => false
        ];
        $this->page = 0;
        $this->prod = 0;
    }
    public function parcingLinks ($num): mixed
    {
        $products = [];

        while (isset($num) ? $this->page < $num : true) {
            $i = 1;
            set_time_limit(300);
            $mh = curl_multi_init();
            while (isset($num) ? $i <= $num : $i <= 5)
            {
                $chs[] = ($ch = curl_init());
                $this->page++;
                curl_setopt($ch, CURLOPT_URL, "https://www.electronictoolbox.com/api/category/new?page=" . $this->page . "&isCatalogPage=1");
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
                curl_setopt_array($ch, $this->options);
                curl_multi_add_handle($mh,$ch);
                $i++;
            }

            $pervRunning = $running = null;

            do {
                curl_multi_exec($mh, $running);
                if ($running != $pervRunning) {
                    $info = curl_multi_info_read($mh);
                    if (is_array($info) && $ch = $info['handle'])
                    {
                        $result = curl_multi_getcontent($ch);
                        if (!empty($result)) {
                            $result = json_decode($result, true);

                            foreach ($result['items'] as $key => $item) {
                                if (array_key_exists('url', $item))
                                {
                                    if (!in_array($item['url'], $products) && !in_array($item['name'], $products)) {
                                        $products[] = [
                                            'name' => $item['name'],
                                            'id' => $item['productid'],
                                            'url' => $item['url'],
                                            'sku' => $item['productcode'],
                                            'brand' => $item['brand'],
                                            'fullPrice' => $item['listPrice']['formatted'],
                                            'discountPrice' => $item['price']['formatted'],
                                            'inStock' => $item['inStock'] == 1 ? 'true' : 'false',

                                        ];
                                        echo 'Количество оригинальных товаров прочитано: ' . count($products) . PHP_EOL;
                                    }
                                }
                            }
                        } else {
                            break;
                        }

                    }
                    $pervRunning = $running;
                }

            } while ($running > 0);
            foreach ($chs as $ch) {
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
            curl_multi_close($mh);

        }

        $finalResults = $this->parsingImagesAndDescr($products);
        $finalResults = json_encode($finalResults);
        print_r( $finalResults);
        file_put_contents('feed.json' , $finalResults);
    }

    public function parsingImagesAndDescr($products): array
    {
        $prod = 0;
        foreach ($products as $key => $item)
        {
            set_time_limit(300);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.electronictoolbox.com/api-client/product/get");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['productId' => $item['id']] ));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headersPost);
            curl_setopt_array($ch, $this->options);
            $result = curl_exec($ch);
            $result = json_decode($result, true);

            foreach ($result['product']['images'] as $images)
            {
                $products[$prod]['images'][] = 'https://i1.s3stores.com/'.$images['image']['path'];
            }
                $products[$prod]['descrAndOpt'][] = strip_tags($result['product']['descr']);
                $products[$prod]['descrAndOpt'][] = strip_tags($result['product']['fulldescr']);
                $prod++;
            echo 'Количество оригинальных товаров обработано: ' . $prod . PHP_EOL;
        }
        return $products;

    }
}

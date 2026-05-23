<?php

namespace Controllers;

use Models\Property;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ApiSyncController
{
    private Property $property;
    private const API_BASE = 'https://trial.craig.mtcserver15.com';
    private const API_KEY = '2S7rhsaq9X1cnfkMCPHX64YsWYyfe1he';
    public function __construct(Property $property)
    {
        $this->property = $property;
    }
    public function sync(Request $request, Response $response): Response
    {
        set_time_limit(0);
        $perPage  = 100;
        $imported = 0;
        $errors   = 0;
        $batchSize = 30;
        try {
            $first = $this->fetchPages([1], $perPage)[1];
            if ($first === null) {
                throw new \Exception('Failed to fetch first page');
            }
            $lastPage = $first['last_page'] ?? 1;
            $remaining = range(2, $lastPage);
            $batches   = array_chunk($remaining, $batchSize);
            $processPage = function (array $result) use (&$imported, &$errors) {
                foreach ($result['data'] as $item) {
                    try {
                        $this->property->upsert([
                            'api_uuid'             => $item['uuid'],
                            'county'               => $item['county'] ?? '',
                            'country'              => $item['country'] ?? '',
                            'town'                 => $item['town'] ?? '',
                            'description'          => $item['description'] ?? '',
                            'displayable_address'  => $item['address'] ?? '',
                            'image_url'            => $item['image_full'] ?? '',
                            'thumbnail_url'        => $item['image_thumbnail'] ?? '',
                            'latitude'             => $item['latitude'] ?? null,
                            'longitude'            => $item['longitude'] ?? null,
                            'num_bedrooms'         => $item['num_bedrooms'] !== null ? (int)$item['num_bedrooms'] : null,
                            'num_bathrooms'        => $item['num_bathrooms'] !== null ? (int)$item['num_bathrooms'] : null,
                            'price'                => $item['price'] !== null ? (float)$item['price'] : null,
                            'property_type'        => $item['property_type']['title'] ?? '',
                            'property_type_id'     => $item['property_type_id'] ?? null,
                            'for_sale'             => ($item['type'] ?? 'sale') === 'sale' ? 1 : 0,
                        ]);
                        $imported++;
                    } catch (\Exception $e) {
                        $errors++;
                    }
                }
            };
            $processPage($first);
            foreach ($batches as $batch) {
                $results = $this->fetchPages($batch, $perPage);
                foreach ($results as $result) {
                    if ($result === null) { $errors++; continue; }
                    $processPage($result);
                }
            }
            $_SESSION['flash_message'] = "Sync complete. Imported: {$imported}, Errors: {$errors}.";
            $_SESSION['flash_type']    = 'success';
        } catch (\Exception $e) {
            $_SESSION['flash_message'] = 'Sync failed: ' . $e->getMessage();
            $_SESSION['flash_type']    = 'danger';
        }
        return $response->withHeader('Location', '/admin')->withStatus(302);
    }
    private function fetchPages(array $pages, int $perPage): array
    {
        $mh = curl_multi_init();
        $handles = [];
        foreach ($pages as $page) {
            $url = self::API_BASE . '/api/properties?' . http_build_query([
                'api_key'      => self::API_KEY,
                'page[number]' => $page,
                'page[size]'   => $perPage,
            ]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[$page] = $ch;
        }
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
        } while ($running > 0);
        $results = [];
        foreach ($handles as $page => $ch) {
            $body = curl_multi_getcontent($ch);
            $results[$page] = json_decode($body, true);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);
        return $results;
    }
}

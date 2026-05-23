<?php

namespace Models;

use PDO;

class Property
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(array $filters = [], string $orderBy = 'created_at', string $direction = 'DESC', int $page = 1, int $perPage = 20): array
    {
        $where = [];
        $params = [];
        if (isset($filters['search']) && $filters['search'] !== '') {
            $where[] = 'displayable_address LIKE :search';
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (isset($filters['bedrooms']) && $filters['bedrooms'] !== '') {
            if ((int)$filters['bedrooms'] >= 5) {
                $where[] = 'num_bedrooms >= :bedrooms';
            } else {
                $where[] = 'num_bedrooms = :bedrooms';
            }
            $params[':bedrooms'] = (int)$filters['bedrooms'];
        }
        if (isset($filters['bathrooms']) && $filters['bathrooms'] !== '') {
            if ((int)$filters['bathrooms'] >= 5) {
                $where[] = 'num_bathrooms >= :bathrooms';
            } else {
                $where[] = 'num_bathrooms = :bathrooms';
            }
            $params[':bathrooms'] = (int)$filters['bathrooms'];
        }
        if (isset($filters['price_min']) && $filters['price_min'] !== '') {
            $where[] = 'price >= :price_min';
            $params[':price_min'] = (float)$filters['price_min'];
        }
        if (isset($filters['price_max']) && $filters['price_max'] !== '') {
            $where[] = 'price <= :price_max';
            $params[':price_max'] = (float)$filters['price_max'];
        }
        if (isset($filters['type']) && $filters['type'] !== '') {
            $where[] = 'for_sale = :type';
            $params[':type'] = $filters['type'] === 'sale' ? 1 : 0;
        }
        $allowedOrders = ['id', 'created_at', 'price', 'num_bedrooms', 'num_bathrooms', 'town', 'county'];
        if (!in_array($orderBy, $allowedOrders)) {
            $orderBy = 'created_at';
        }
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $whereClause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $offset = ($page - 1) * $perPage;
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM properties{$whereClause}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare(
            "SELECT * FROM properties{$whereClause} ORDER BY {$orderBy} {$direction} LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $properties = $stmt->fetchAll();
        return [
            'data' => $properties,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int)ceil($total / $perPage),
        ];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM properties WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findByUuid(string $uuid): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM properties WHERE api_uuid = :uuid');
        $stmt->execute([':uuid' => $uuid]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data, string $source = 'admin'): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO properties (county, country, town, description, displayable_address, image_url, thumbnail_url, latitude, longitude, num_bedrooms, num_bathrooms, price, property_type, property_type_id, property_description, for_sale, source, api_uuid)
            VALUES (:county, :country, :town, :description, :displayable_address, :image_url, :thumbnail_url, :latitude, :longitude, :num_bedrooms, :num_bathrooms, :price, :property_type, :property_type_id, :property_description, :for_sale, :source, :api_uuid)'
        );
        $stmt->execute([
            ':county' => $data['county'] ?? null,
            ':country' => $data['country'] ?? null,
            ':town' => $data['town'] ?? null,
            ':description' => $data['description'] ?? null,
            ':displayable_address' => $data['displayable_address'] ?? null,
            ':image_url' => $data['image_url'] ?? null,
            ':thumbnail_url' => $data['thumbnail_url'] ?? null,
            ':latitude' => $data['latitude'] ?? null,
            ':longitude' => $data['longitude'] ?? null,
            ':num_bedrooms' => $data['num_bedrooms'] ?? null,
            ':num_bathrooms' => $data['num_bathrooms'] ?? null,
            ':price' => $data['price'] ?? null,
            ':property_type' => $data['property_type'] ?? null,
            ':property_type_id' => $data['property_type_id'] ?? null,
            ':property_description' => $data['property_description'] ?? null,
            ':for_sale' => $data['for_sale'] ?? 1,
            ':source' => $source,
            ':api_uuid' => $data['api_uuid'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $fields = [];
        $params = [':id' => $id];
        $allowed = ['county', 'country', 'town', 'description', 'displayable_address', 'image_url', 'thumbnail_url', 'latitude', 'longitude', 'num_bedrooms', 'num_bathrooms', 'price', 'property_type', 'property_type_id', 'property_description', 'for_sale'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }
        if (empty($fields)) {
            return;
        }
        $stmt = $this->db->prepare('UPDATE properties SET ' . implode(', ', $fields) . ' WHERE id = :id');
        $stmt->execute($params);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM properties WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function autocomplete(string $query): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT displayable_address FROM properties WHERE displayable_address LIKE :query LIMIT 10'
        );
        $stmt->execute([':query' => '%' . $query . '%']);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    public function upsert(array $data): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO properties (
                api_uuid, county, country, town, description,
                displayable_address, image_url, thumbnail_url,
                latitude, longitude, num_bedrooms, num_bathrooms,
                price, property_type, property_type_id, property_description, for_sale, source
            ) VALUES (
                :api_uuid, :county, :country, :town, :description,
                :displayable_address, :image_url, :thumbnail_url,
                :latitude, :longitude, :num_bedrooms, :num_bathrooms,
                :price, :property_type, :property_type_id, :property_description, :for_sale, :source
            )
            ON DUPLICATE KEY UPDATE
                county = VALUES(county), country = VALUES(country),
                town = VALUES(town), description = VALUES(description),
                displayable_address = VALUES(displayable_address),
                image_url = VALUES(image_url), thumbnail_url = VALUES(thumbnail_url),
                latitude = VALUES(latitude), longitude = VALUES(longitude),
                num_bedrooms = VALUES(num_bedrooms), num_bathrooms = VALUES(num_bathrooms),
                price = VALUES(price), property_type = VALUES(property_type),
                property_type_id = VALUES(property_type_id), property_description = VALUES(property_description), for_sale = VALUES(for_sale)
        ');

        $stmt->execute([
            ':api_uuid'            => $data['api_uuid'],
            ':county'              => $data['county'] ?? null,
            ':country'             => $data['country'] ?? null,
            ':town'                => $data['town'] ?? null,
            ':description'         => $data['description'] ?? null,
            ':displayable_address' => $data['displayable_address'] ?? null,
            ':image_url'           => $data['image_url'] ?? null,
            ':thumbnail_url'       => $data['thumbnail_url'] ?? null,
            ':latitude'            => $data['latitude'] ?? null,
            ':longitude'           => $data['longitude'] ?? null,
            ':num_bedrooms'        => $data['num_bedrooms'] ?? null,
            ':num_bathrooms'       => $data['num_bathrooms'] ?? null,
            ':price'               => $data['price'] ?? null,
            ':property_type'       => $data['property_type'] ?? null,
            ':property_type_id'    => $data['property_type_id'] ?? null,
            ':property_description' => $data['property_description'] ?? null,
            ':for_sale'            => $data['for_sale'] ?? 1,
            ':source'              => 'api',
        ]);
    }
}

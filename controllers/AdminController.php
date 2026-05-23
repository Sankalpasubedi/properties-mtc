<?php

namespace Controllers;

use Models\Property;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Routing\RouteContext;

class AdminController
{
    private Property $property;
    private ViewController $view;

    public function __construct(Property $property, ViewController $view)
    {
        $this->property = $property;
        $this->view = $view;
    }

    public function dashboard(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = 20;
        $filters = [];
        if (!empty($params['search'])) {
            $filters['search'] = $params['search'];
        }
        $result = $this->property->all($filters, 'id', 'DESC', $page, $perPage);
        $flash_message = $_SESSION['flash_message'] ?? null;
        $flash_type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $this->view->renderWithLayout($response, 'admin/dashboard.php', [
            'title' => 'Admin - Properties',
            'properties' => $result['data'],
            'total' => $result['total'],
            'page' => $result['page'],
            'last_page' => $result['last_page'],
            'per_page' => $perPage,
            'search' => $params['search'] ?? '',
            'csrf_token' => $this->getCsrfToken(),
            'flash_message' => $flash_message,
            'flash_type' => $flash_type,
        ]);
    }

    public function addForm(Request $request, Response $response): Response
    {
        return $this->view->renderWithLayout($response, 'admin/property_form.php', [
            'title' => 'Add Property',
            'property' => null,
            'csrf_token' => $this->getCsrfToken(),
        ]);
    }

    public function add(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if (!$this->validateCsrfToken($data['csrf_token'] ?? '')) {
            return $this->view->renderWithLayout($response, 'admin/property_form.php', [
                'title' => 'Add Property',
                'property' => null,
                'errors' => ['Invalid CSRF token.'],
                'csrf_token' => $this->getCsrfToken(),
            ]);
        }
        $errors = $this->validate($data, $request->getUploadedFiles());
        if (!empty($errors)) {
            return $this->view->renderWithLayout($response, 'admin/property_form.php', [
                'title' => 'Add Property',
                'property' => $data,
                'errors' => $errors,
                'csrf_token' => $this->getCsrfToken(),
            ]);
        }
        $uploadedFiles = $request->getUploadedFiles();
        $imageUrl = $this->handleUpload($uploadedFiles['image'] ?? null);
        $thumbUrl = $this->handleUpload($uploadedFiles['thumbnail'] ?? null);
        $this->property->create([
            'county' => $data['county'] ?? '',
            'country' => $data['country'] ?? '',
            'town' => $data['town'] ?? '',
            'description' => $data['description'] ?? '',
            'displayable_address' => $data['displayable_address'] ?? '',
            'image_url' => $imageUrl,
            'thumbnail_url' => $thumbUrl,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'num_bedrooms' => $data['num_bedrooms'] !== '' ? (int)$data['num_bedrooms'] : null,
            'num_bathrooms' => $data['num_bathrooms'] !== '' ? (int)$data['num_bathrooms'] : null,
            'price' => $data['price'] !== '' ? (float)$data['price'] : null,
            'property_type' => $data['property_type'] ?? '',
            'property_type_id' => null,
            'property_description' => $data['property_description'] ?? '',
            'for_sale' => ($data['for_sale'] ?? 'sale') === 'sale' ? 1 : 0,
        ], 'admin');
        $_SESSION['flash_message'] = 'Property created successfully.';
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', '/admin')->withStatus(302);
    }

    public function editForm(Request $request, Response $response): Response
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        $id = (int)$route->getArgument('id');
        $property = $this->property->find($id);
        if (!$property) {
            $_SESSION['flash_message'] = 'Property not found.';
            $_SESSION['flash_type'] = 'danger';
            return $response->withHeader('Location', '/admin')->withStatus(302);
        }
        return $this->view->renderWithLayout($response, 'admin/property_form.php', [
            'title' => 'Edit Property',
            'property' => $property,
            'csrf_token' => $this->getCsrfToken(),
        ]);
    }

    public function delete(Request $request, Response $response): Response
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        $id = (int)$route->getArgument('id');
        $property = $this->property->find($id);
        if ($property) {
            $this->property->delete($id);
            $_SESSION['flash_message'] = 'Property deleted successfully.';
            $_SESSION['flash_type'] = 'success';
        }
        return $response->withHeader('Location', '/admin')->withStatus(302);
    }

    public function edit(Request $request, Response $response): Response
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        $id = (int)$route->getArgument('id');
        $property = $this->property->find($id);

        if (!$property) {
            return $response->withHeader('Location', '/admin')->withStatus(302);
        }
        $data = $request->getParsedBody();
        if (!$this->validateCsrfToken($data['csrf_token'] ?? '')) {
            return $this->view->renderWithLayout($response, 'admin/property_form.php', [
                'title' => 'Edit Property',
                'property' => array_merge($property, $data),
                'errors' => ['Invalid CSRF token.'],
                'csrf_token' => $this->getCsrfToken(),
            ]);
        }
        $errors = $this->validate($data, $request->getUploadedFiles(), true);
        if (!empty($errors)) {
            return $this->view->renderWithLayout($response, 'admin/property_form.php', [
                'title' => 'Edit Property',
                'property' => array_merge($property, $data),
                'errors' => $errors,
                'csrf_token' => $this->getCsrfToken(),
            ]);
        }
        $uploadedFiles = $request->getUploadedFiles();
        $imageUrl = $this->handleUpload($uploadedFiles['image'] ?? null);
        $thumbUrl = $this->handleUpload($uploadedFiles['thumbnail'] ?? null);
        $updateData = [
            'county' => $data['county'] ?? '',
            'country' => $data['country'] ?? '',
            'town' => $data['town'] ?? '',
            'description' => $data['description'] ?? '',
            'displayable_address' => $data['displayable_address'] ?? '',
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'num_bedrooms' => $data['num_bedrooms'] !== '' ? (int)$data['num_bedrooms'] : null,
            'num_bathrooms' => $data['num_bathrooms'] !== '' ? (int)$data['num_bathrooms'] : null,
            'price' => $data['price'] !== '' ? (float)$data['price'] : null,
            'property_type' => $data['property_type'] ?? '',
            'property_description' => $data['property_description'] ?? '',
            'for_sale' => ($data['for_sale'] ?? 'sale') === 'sale' ? 1 : 0,
        ];
        if ($imageUrl !== null) {
            $updateData['image_url'] = $imageUrl;
        }
        if ($thumbUrl !== null) {
            $updateData['thumbnail_url'] = $thumbUrl;
        }
        $this->property->update($id, $updateData);
        $_SESSION['flash_message'] = 'Property updated successfully.';
        $_SESSION['flash_type'] = 'success';
        return $response->withHeader('Location', '/admin')->withStatus(302);
    }

    private function handleUpload(?UploadedFileInterface $file): ?string
    {
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(16));
        $filename = $basename . '.' . $extension;
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $file->moveTo($uploadDir . $filename);
        return '/uploads/' . $filename;
    }

    private function validate(array $data, array $files, bool $isEdit = false): array
    {
        $errors = [];
        if (empty(trim($data['county'] ?? ''))) {
            $errors[] = 'County is required.';
        }
        if (empty(trim($data['country'] ?? ''))) {
            $errors[] = 'Country is required.';
        }
        if (empty(trim($data['town'] ?? ''))) {
            $errors[] = 'Town is required.';
        }
        if (empty(trim($data['displayable_address'] ?? ''))) {
            $errors[] = 'Displayable address is required.';
        }
        if (empty(trim($data['description'] ?? ''))) {
            $errors[] = 'Description is required.';
        }
        if (empty(trim($data['price'] ?? '')) || !is_numeric($data['price'])) {
            $errors[] = 'Valid price is required.';
        }
        if (!in_array(($data['for_sale'] ?? ''), ['sale', 'rent'])) {
            $errors[] = 'Please select For Either Sale or For Rent.';
        }

        if (!empty($data['latitude']) && !is_numeric($data['latitude'])) {
            $errors[] = 'Latitude must be a number.';
        }
        if (!empty($data['longitude']) && !is_numeric($data['longitude'])) {
            $errors[] = 'Longitude must be a number.';
        }
        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            $lat = (float)$data['latitude'];
            $lng = (float)$data['longitude'];
            if ($lat < -90 || $lat > 90) {
                $errors[] = 'Latitude must be between -90 and 90.';
            }
            if ($lng < -180 || $lng > 180) {
                $errors[] = 'Longitude must be between -180 and 180.';
            }
        }
        $image = $files['image'] ?? null;
        $thumbnail = $files['thumbnail'] ?? null;
        if (!$isEdit) {
            if ($image && $image->getError() === UPLOAD_ERR_NO_FILE) {
                $errors[] = 'Image file is required.';
            }
            if ($thumbnail && $thumbnail->getError() === UPLOAD_ERR_NO_FILE) {
                $errors[] = 'Thumbnail file is required.';
            }
        }
        if ($image && $image->getError() === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($image->getClientMediaType(), $allowedTypes)) {
                $errors[] = 'Image must be JPEG, JPG, or PNG.';
            }
            if ($image->getSize() > 5 * 1024 * 1024) {
                $errors[] = 'Image must be less than 5MB.';
            }
        }
        if ($thumbnail && $thumbnail->getError() === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($thumbnail->getClientMediaType(), $allowedTypes)) {
                $errors[] = 'Thumbnail must be JPEG, JPG, or PNG.';
            }
            if ($thumbnail->getSize() > 2 * 1024 * 1024) {
                $errors[] = 'Thumbnail must be less than 2MB.';
            }
        }
        return $errors;
    }

    private function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    private function validateCsrfToken(string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || $token === '') {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

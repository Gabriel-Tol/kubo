<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class CatalogService
{
    public function __construct(private readonly BaseConnection $db)
    {
    }

    /** @return array{products: list<array<string, mixed>>, count: int} */
    public function search(array $filters = []): array
    {
        $query = $this->db->table('products p')
            ->select('p.*, c.name AS category_name, c.slug AS category_slug')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->where('p.is_active', 1)
            ->where('p.archived_at', null);

        $keyword = trim((string) ($filters['q'] ?? $filters['search'] ?? ''));
        if ($keyword !== '') {
            $query->like('p.name', $keyword);
        }

        $category = trim((string) ($filters['category'] ?? ''));
        if ($category !== '') {
            if (ctype_digit($category)) {
                $query->where('p.category_id', (int) $category);
            } else {
                $query->where('c.slug', $category);
            }
        }

        $variants = $this->db->table('product_variants')
            ->select('product_id')
            ->where('is_active', 1);
        $minPrice = filter_var($filters['min_price'] ?? null, FILTER_VALIDATE_INT);
        $maxPrice = filter_var($filters['max_price'] ?? null, FILTER_VALIDATE_INT);
        if ($minPrice !== false && $minPrice !== null && $minPrice >= 0) {
            $variants->where('price >=', $minPrice);
        }
        if ($maxPrice !== false && $maxPrice !== null && $maxPrice >= 0) {
            $variants->where('price <=', $maxPrice);
        }
        $query->whereIn('p.id', $variants);

        $sort = (string) ($filters['sort'] ?? 'name_asc');
        match ($sort) {
            'price_asc' => $query->orderBy('(SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.id AND v.is_active = 1)', 'ASC', false)->orderBy('p.name', 'ASC'),
            'price_desc' => $query->orderBy('(SELECT MIN(v.price) FROM product_variants v WHERE v.product_id = p.id AND v.is_active = 1)', 'DESC', false)->orderBy('p.name', 'ASC'),
            'name_desc' => $query->orderBy('p.name', 'DESC'),
            default => $query->orderBy('p.name', 'ASC'),
        };

        $products = $query->get()->getResultArray();
        $this->attachCatalogDetails($products);

        return ['products' => $products, 'count' => count($products)];
    }

    /** @return array<string, mixed>|null */
    public function getBySlug(string $slug): ?array
    {
        $product = $this->db->table('products p')
            ->select('p.*, c.name AS category_name, c.slug AS category_slug')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->where('p.slug', $slug)
            ->where('p.is_active', 1)
            ->where('p.archived_at', null)
            ->get()
            ->getRowArray();

        if ($product === null) {
            return null;
        }

        $products = [$product];
        $this->attachCatalogDetails($products);

        return $products[0];
    }

    private function attachCatalogDetails(array &$products): void
    {
        if ($products === []) {
            return;
        }

        $ids = array_column($products, 'id');
        $variants = $this->db->table('product_variants')
            ->whereIn('product_id', $ids)
            ->where('is_active', 1)
            ->orderBy('price', 'ASC')
            ->get()
            ->getResultArray();
        $byProduct = [];
        foreach ($variants as $variant) {
            $byProduct[$variant['product_id']][] = $variant;
        }

        $images = $this->db->table('product_images')
            ->whereIn('product_id', $ids)
            ->orderBy('is_primary', 'DESC')
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();
        $primaryImages = [];
        foreach ($images as $image) {
            $primaryImages[$image['product_id']] ??= $image;
        }

        foreach ($products as &$product) {
            $product['variants'] = $byProduct[$product['id']] ?? [];
            $product['primary_image'] = $primaryImages[$product['id']] ?? null;
            $product['min_price'] = $product['variants'] === [] ? null : min(array_column($product['variants'], 'price'));
            $product['max_price'] = $product['variants'] === [] ? null : max(array_column($product['variants'], 'price'));
            $product['available_stock'] = array_sum(array_column($product['variants'], 'stock'));
        }
        unset($product);
    }
}
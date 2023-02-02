<?php

namespace App\Http\Controllers\Api\Catalog;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CatalogController
{
    private const ITEMS_PER_PAGE = 20;
    private Connection $connection;

    public function __construct(DatabaseManager $manager)
    {
        $this->connection = $manager->connection();
    }

    public function index(CatalogRequest $request): CatalogResponse
    {
        $records = $this->connection->table('adverts')
            ->where('category_id', '=', $request->getCategoryId())
            ->limit(self::ITEMS_PER_PAGE)
            ->offset($request->getPage() * self::ITEMS_PER_PAGE)
            ->orderBy('id', 'DESC')
            ->get()
            ->all();

        return new CatalogResponse($records);
    }

    public function advert(GetAdvertRequest $request): GetAdvertResponse
    {
        $advert = $this->connection->table('adverts AS a')
            ->join('categories AS c', 'c.id', '=', 'a.category_id')
            ->where('a.id', '=', $request->getId())
            ->get(['a.*', 'c.title AS category'])
            ->first();

        if (!$advert) {
            throw new NotFoundHttpException();
        }

        return new GetAdvertResponse($advert);
    }
}

<?php

namespace FriendsOfRedaxo\SearchIt\Api\RoutePackage;

use Exception;
use FriendsOfRedaxo\Api\Auth\BearerAuth;
use FriendsOfRedaxo\Api\RouteCollection;
use FriendsOfRedaxo\Api\RoutePackage;
use rex_addon;
use rex_clang;
use rex;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

use const JSON_PRETTY_PRINT;

class SearchIt extends RoutePackage
{
    public function loadRoutes()
    {
        RouteCollection::registerRoute(
            'search_it/public/search',
            new Route(
                'search_it/public/search',
                [
                    '_controller' => 'FriendsOfRedaxo\\SearchIt\\Api\\RoutePackage\\SearchIt::handlePublicSearch',
                    'query' => [
                        'q' => [
                            'type' => 'string',
                            'required' => true,
                        ],
                        'clang' => [
                            'type' => 'int',
                            'required' => false,
                            'default' => 0,
                        ],
                        'limit_start' => [
                            'type' => 'int',
                            'required' => false,
                            'default' => 0,
                        ],
                        'limit_count' => [
                            'type' => 'int',
                            'required' => false,
                            'default' => 10,
                        ],
                    ],
                ],
                [],
                [],
                '',
                [],
                ['GET']
            ),
            'Execute a public search_it query without bearer token',
            null,
            null,
            ['search_it']
        );

        RouteCollection::registerRoute(
            'search_it/capabilities',
            new Route(
                'search_it/capabilities',
                [
                    '_controller' => 'FriendsOfRedaxo\\SearchIt\\Api\\RoutePackage\\SearchIt::handleCapabilities',
                ],
                [],
                [],
                '',
                [],
                ['GET']
            ),
            'Get search_it capabilities and addon configuration snapshot',
            null,
            new BearerAuth(),
            ['search_it']
        );

        RouteCollection::registerRoute(
            'search_it/search',
            new Route(
                'search_it/search',
                [
                    '_controller' => 'FriendsOfRedaxo\\SearchIt\\Api\\RoutePackage\\SearchIt::handleSearch',
                    'query' => [
                        'q' => [
                            'type' => 'string',
                            'required' => true,
                        ],
                        'clang' => [
                            'type' => 'int',
                            'required' => false,
                            'default' => 0,
                        ],
                        'limit_start' => [
                            'type' => 'int',
                            'required' => false,
                            'default' => 0,
                        ],
                        'limit_count' => [
                            'type' => 'int',
                            'required' => false,
                            'default' => 10,
                        ],
                    ],
                ],
                [],
                [],
                '',
                [],
                ['GET']
            ),
            'Execute a search_it fulltext query',
            null,
            new BearerAuth(),
            ['search_it']
        );

        RouteCollection::registerRoute(
            'search_it/reindex',
            new Route(
                'search_it/reindex',
                [
                    '_controller' => 'FriendsOfRedaxo\\SearchIt\\Api\\RoutePackage\\SearchIt::handleReindex',
                    'Body' => [
                        'article_id' => [
                            'type' => 'int',
                            'required' => false,
                            'default' => 0,
                        ],
                        'clang' => [
                            'type' => 'int',
                            'required' => false,
                            'default' => -1,
                        ],
                        'clear_cache' => [
                            'type' => 'bool',
                            'required' => false,
                            'default' => true,
                        ],
                    ],
                ],
                [],
                [],
                '',
                [],
                ['POST']
            ),
            'Reindex search_it data (full or per article)',
            null,
            new BearerAuth(),
            ['search_it']
        );
    }

    /** @api */
    public static function handleCapabilities($parameter, array $route = []): Response
    {
        $addon = rex_addon::get('search_it');

        $payload = [
            'addon' => 'search_it',
            'version' => $addon->getVersion(),
            'config' => [
                'searchmode' => $addon->getConfig('searchmode'),
                'logicalmode' => $addon->getConfig('logicalmode'),
                'highlight' => $addon->getConfig('highlight'),
                'limit' => $addon->getConfig('limit'),
                'indexoffline' => (bool) $addon->getConfig('indexoffline'),
                'index_url_addon' => (bool) $addon->getConfig('index_url_addon'),
                'indexmediapool' => (bool) $addon->getConfig('indexmediapool'),
            ],
        ];

        return new Response(json_encode($payload, JSON_PRETTY_PRINT));
    }

    /** @api */
    public static function handleSearch($parameter, array $route = []): Response
    {
        try {
            $query = RouteCollection::getQuerySet($_REQUEST, $parameter['query']);
        } catch (Exception $e) {
            return new Response(json_encode(['error' => 'query field: ' . $e->getMessage() . ' is required']), 400);
        }

        $searchTerm = trim((string) ($query['q'] ?? ''));
        if ('' === $searchTerm) {
            return new Response(json_encode(['error' => 'q must not be empty']), 400);
        }

        $clang = (int) ($query['clang'] ?? 0);
        if ($clang <= 0) {
            $clang = rex_clang::getCurrentId();
        }

        $limitStart = max(0, (int) ($query['limit_start'] ?? 0));
        $limitCount = max(1, (int) ($query['limit_count'] ?? 10));

        $search = new \search_it($clang);
        $search->setLimit([$limitStart, $limitCount]);
        $result = $search->search($searchTerm);

        $payload = [
            'query' => $searchTerm,
            'clang' => $clang,
            'limit' => [$limitStart, $limitCount],
            'result' => $result,
        ];

        return new Response(json_encode($payload, JSON_PRETTY_PRINT));
    }

    /** @api */
    public static function handlePublicSearch($parameter, array $route = []): Response
    {
        try {
            $query = RouteCollection::getQuerySet($_REQUEST, $parameter['query']);
        } catch (Exception $e) {
            return new Response(json_encode(['error' => 'query field: ' . $e->getMessage() . ' is required']), 400);
        }

        $searchTerm = trim((string) ($query['q'] ?? ''));
        if (mb_strlen($searchTerm, 'UTF-8') < 2) {
            return new Response(json_encode(['error' => 'q must be at least 2 characters']), 400);
        }

        $clang = (int) ($query['clang'] ?? 0);
        if ($clang <= 0) {
            $clang = rex_clang::getCurrentId();
        }

        $limitStart = max(0, (int) ($query['limit_start'] ?? 0));
        $limitCount = max(1, min(20, (int) ($query['limit_count'] ?? 10)));

        $search = new \search_it($clang);
        $search->setLimit([$limitStart, $limitCount]);
        $result = $search->search($searchTerm);

        $payload = [
            'query' => $searchTerm,
            'clang' => $clang,
            'limit' => [$limitStart, $limitCount],
            'public' => true,
            'result' => $result,
        ];

        return new Response(json_encode($payload, JSON_PRETTY_PRINT));
    }

    /** @api */
    public static function handleReindex($parameter, array $route = []): Response
    {
        $data = json_decode((string) rex::getRequest()->getContent(), true);
        if (!is_array($data)) {
            $data = [];
        }

        try {
            $body = RouteCollection::getQuerySet($data, $parameter['Body']);
        } catch (Exception $e) {
            return new Response(json_encode(['error' => 'Body field: `' . $e->getMessage() . '` is required']), 400);
        }

        $search = new \search_it();
        $articleId = (int) ($body['article_id'] ?? 0);
        $clang = (int) ($body['clang'] ?? -1);
        $clearCache = (bool) ($body['clear_cache'] ?? true);

        if ($articleId > 0) {
            $indexResult = $clang >= 0 ? $search->indexArticle($articleId, $clang) : $search->indexArticle($articleId);
            if ($clearCache) {
                $search->deleteCache();
            }

            return new Response(json_encode([
                'mode' => 'article',
                'article_id' => $articleId,
                'clang' => $clang,
                'result' => $indexResult,
            ], JSON_PRETTY_PRINT));
        }

        $warnings = $search->generateIndex();

        return new Response(json_encode([
            'mode' => 'full',
            'warnings' => $warnings,
        ], JSON_PRETTY_PRINT));
    }
}

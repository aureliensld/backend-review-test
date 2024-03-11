<?php

namespace App\Controller;

use App\Dto\SearchInput;
use App\Entity\EventType;
use App\Repository\ReadEventRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

class SearchController
{
    public function __construct(private readonly ReadEventRepository $repository)
    {
    }

    #[Route(path: '/api/search', name: 'api_search', methods: ['GET'])]
    public function searchCommits(#[MapQueryString] SearchInput $searchInput = new SearchInput()): JsonResponse
    {
        $date = $searchInput->getDate();
        $keyword = $searchInput->keyword;

        $countByType = $this->repository->countByType($date, $keyword);

        $data = [
            'meta' => [
                'totalEvents' => $this->repository->countAll($date, $keyword),
                'totalPullRequests' => $countByType[EventType::PULL_REQUEST] ?? 0,
                'totalCommits' => $countByType[EventType::COMMIT] ?? 0,
                'totalComments' => $countByType[EventType::COMMENT] ?? 0,
            ],
            'data' => [
                'events' => $this->repository->getLatest($date, $keyword),
                'stats' => $this->repository->statsByTypePerHour($date, $keyword),
            ],
        ];

        return new JsonResponse($data);
    }
}

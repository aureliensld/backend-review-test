<?php

namespace App\Controller;

use App\Dto\SearchInput;
use App\Entity\EventType;
use App\Repository\ReadEventRepository;
use App\Util\Pagination\CursorPaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchController extends AbstractController
{
    public function __construct(private readonly ReadEventRepository $repository)
    {
    }

    #[Route(path: '/api/search', name: 'api_search', methods: ['GET'])]
    public function searchCommits(Request $request, #[MapQueryString] SearchInput $searchInput = new SearchInput()): JsonResponse
    {
        $date = $searchInput->getDate();

        $countByType = $this->repository->countByType($date, $searchInput->keyword);
        $events = $this->repository->getLatest($date, $searchInput->keyword, $searchInput->cursor, $searchInput->resultsPerPage);
        $nextCursor = null;
        if ($events instanceof CursorPaginatorInterface) {
            $nextCursor = $this->generateUrl('api_search', ['cursor' => $events->getNextCursor()] + $request->query->all(), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $data = [
            'meta' => [
                'totalEvents' => $this->repository->countAll($date, $searchInput->keyword),
                'totalPullRequests' => $countByType[EventType::PULL_REQUEST] ?? 0,
                'totalCommits' => $countByType[EventType::COMMIT] ?? 0,
                'totalComments' => $countByType[EventType::COMMENT] ?? 0,
            ],
            'data' => [
                'events' => $events,
                'stats' => $this->repository->statsByTypePerHour($date, $searchInput->keyword),
            ],
            'next_cursor' => $nextCursor,
        ];

        return new JsonResponse($data);
    }
}

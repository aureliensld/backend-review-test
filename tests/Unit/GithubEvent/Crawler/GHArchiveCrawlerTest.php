<?php

namespace App\Tests\Unit\GithubEvent\Crawler;

use App\GithubEvent\Crawler\GHArchiveCrawler;
use App\GithubEvent\Exception\NoEventFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class GHArchiveCrawlerTest extends TestCase
{
    private GHArchiveCrawler $crawler;

    protected function setUp(): void
    {
        parent::setUp();

        $expectedRequests = [
            function ($method, $url): MockResponse {
                $fileName = __DIR__.'/'.trim(parse_url($url, \PHP_URL_PATH), '/');
                if (!is_file($fileName)) {
                    return new MockResponse('', ['http_code' => 404]);
                }

                return new MockResponse(file_get_contents($fileName));
            },
        ];
        $httpClient = new MockHttpClient($expectedRequests);

        $this->crawler = new GHArchiveCrawler($httpClient);
    }

    /**
     * @dataProvider dateProvider
     */
    public function testRun(\DateTimeInterface $dateTime): void
    {
        $counter = 0;
        foreach ($this->crawler->run($dateTime) as $event) {
            ++$counter;

            $this->assertIsArray($event);
            $this->assertArrayHasKey('id', $event);
            $this->assertArrayHasKey('type', $event);
            $this->assertArrayHasKey('actor', $event);
            $this->assertArrayHasKey('repo', $event);
            $this->assertArrayHasKey('payload', $event);
            $this->assertArrayHasKey('created_at', $event);
        }

        $this->assertGreaterThan(0, $counter);
    }

    /**
     * @dataProvider invalidDateProvider
     */
    public function testParseShouldThrowNoEventFoundException(\DateTimeInterface $dateTime): void
    {
        $this->expectException(NoEventFoundException::class);
        foreach ($this->crawler->run($dateTime) as $event) {
        }
    }

    /**
     * @return iterable<string, \DateTimeInterface[]>
     */
    public function dateProvider(): iterable
    {
        yield '2024-03-11-1' => [\DateTimeImmutable::createFromFormat('Y-m-d-G', '2024-03-11-1')];
    }

    /**
     * @return iterable<string, \DateTimeInterface[]>
     */
    public function invalidDateProvider(): iterable
    {
        yield '2024-03-11-10' => [\DateTimeImmutable::createFromFormat('Y-m-d-G', '2024-03-11-10')];
        yield '2024-03-11-11' => [\DateTimeImmutable::createFromFormat('Y-m-d-G', '2024-03-11-11')];
    }
}

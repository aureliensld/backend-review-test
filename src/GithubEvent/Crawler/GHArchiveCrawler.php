<?php

namespace App\GithubEvent\Crawler;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GHArchiveCrawler implements CrawlerInterface
{
    private const BASE_URI = 'https://data.gharchive.org';

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function run(\DateTimeInterface $dateTime): iterable
    {
        $url = sprintf('%s/%s.json.gz', self::BASE_URI, $dateTime->format('Y-m-d-G'));

        $response = $this->httpClient->request('GET', $url);

        $inflateCtx = inflate_init(\ZLIB_ENCODING_GZIP);
        $buffer = '';

        foreach ($this->httpClient->stream($response) as $chunk) {
            if (null !== $chunk->getError()) {
                throw new \RuntimeException($chunk->getError());
            }

            if ($chunk->isFirst() && 404 === $response->getStatusCode()) {
                throw new \RuntimeException(sprintf('No events found for date: "%s"', $dateTime->format('Y-m-d-G')));
            }

            $rawContent = $chunk->getContent();
            $content = '' === $rawContent ? '' : inflate_add($inflateCtx, $rawContent);
            if (false === $content) {
                throw new \RuntimeException('Cannot inflate chunk.');
            }

            $buffer .= $content;

            $pos = strpos($buffer, PHP_EOL);
            while (false !== $pos) {
                try {
                    $event = json_decode(substr($buffer, 0, $pos), true, flags: \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    throw new \RuntimeException('Invalid JSON.', $e->getCode(), $e);
                }

                yield $event;

                $buffer = substr($buffer, $pos + 1);
                $pos = strpos($buffer, PHP_EOL);
            }
        }

        if ('' !== $buffer) {
            throw new \RuntimeException('Buffer is not empty.');
        }
    }
}

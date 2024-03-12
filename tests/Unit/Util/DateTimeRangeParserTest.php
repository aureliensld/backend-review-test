<?php

namespace App\Tests\Unit\Util;

use App\Util\DateTimeRangeParser;
use PHPUnit\Framework\TestCase;

class DateTimeRangeParserTest extends TestCase
{
    private DateTimeRangeParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new DateTimeRangeParser();
    }

    /**
     * @dataProvider invalidDateTimeRangeProvider
     */
    public function testParseShouldThrowInvalidArgumentException(string $dateTimeRange): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->parser->parse($dateTimeRange);
    }

    /**
     * @param string[] $expectedResults
     *
     * @dataProvider dateTimeRangeProvider
     */
    public function testParse(string $dateTimeRange, array $expectedResults): void
    {
        $results = array_map(fn (\DateTimeInterface $dateTime) => $dateTime->format('Y-m-d-G'), $this->parser->parse($dateTimeRange));

        $this->assertEquals($expectedResults, $results);
    }

    /**
     * @return iterable<string, string[]>
     */
    public function invalidDateTimeRangeProvider(): iterable
    {
        yield 'Random input' => [uniqid()];
        yield 'Malformed date without range' => ['2024-13-01'];
        yield 'Malformed date with hour' => ['2024-13-01-1'];
        yield 'Malformed date with hour range' => ['2024-13-01-{0..1}'];
        yield 'Malformed hour range' => ['2024-01-01-{0...1}'];
        yield 'Out of bounds hour' => ['2024-01-01-30'];
        yield 'Out of bounds hour range' => ['2024-01-01-{0..24}'];
        yield 'Start hour greater than end hour' => ['2024-01-01-{2..0}'];
    }

    /**
     * @return iterable<string, string|string[]>
     */
    public function dateTimeRangeProvider(): iterable
    {
        yield 'Simple date' => ['2024-01-01', array_map(fn (int $hour, string $date) => sprintf('%s-%u', $date, $hour), range(0, 23), array_fill(0, 24, '2024-01-01'))];
        yield 'Date with hour #1' => ['2024-01-01-1', ['2024-01-01-1']];
        yield 'Date with hour #2' => ['2024-01-01-0', ['2024-01-01-0']];
        yield 'Date with hour #3' => ['2024-1-01-1', ['2024-01-01-1']];
        yield 'Date with hour #4' => ['2024-01-1-1', ['2024-01-01-1']];
        yield 'Date with hour #5' => ['2024-01-01-12', ['2024-01-01-12']];
        yield 'Date with hour range #1' => ['2024-01-01-{0..3}', ['2024-01-01-0', '2024-01-01-1', '2024-01-01-2', '2024-01-01-3']];
        yield 'Date with hour range #2' => ['2024-01-01-{9..11}', ['2024-01-01-9', '2024-01-01-10', '2024-01-01-11']];
        yield 'Date with hour range #3' => ['2024-01-01-{0..23}', array_map(fn (int $hour, string $date) => sprintf('%s-%u', $date, $hour), range(0, 23), array_fill(0, 24, '2024-01-01'))];
    }
}

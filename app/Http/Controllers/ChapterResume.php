<?php

namespace App\Http\Controllers;

use App\Actions\TranslateChapterAction;
use Stichoza\GoogleTranslate\Exceptions\LargeTextException;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;
use Stichoza\GoogleTranslate\Exceptions\TranslationRequestException;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Symfony\Component\DomCrawler\Crawler;

class ChapterResume
{
    public const NOVELFULL_BASE_URL = 'https://novelfull.com';
    public const NOVELFULL_BASE_URL2 = 'https://novelfull.net';

    public function __construct(
        public readonly string $title,
        public readonly string $nextChapterUrl,
        public readonly string $chapter,
        public readonly string $content,
    ) {
    }

    /**
     * @throws LargeTextException
     * @throws RateLimitException
     * @throws TranslationRequestException
     */
    public static function loadByUrl(string $url): ChapterResume
    {
        $html = file_get_contents($url);
        $tr = new GoogleTranslate('pt');
        $tr->setSource('en');
        $crawler = new Crawler($html);

        $title = $crawler->filter('a.truyen-title')->text();
        $chapterTitle = $crawler->filter('.chapter-text')->text();
        $nextChapterEndpoint = $crawler->filter('#next_chap')->attr('href');
        $baseUrl = str_contains($url, self::NOVELFULL_BASE_URL) ? self::NOVELFULL_BASE_URL : self::NOVELFULL_BASE_URL2;
        $nextChapterUrl = $baseUrl . $nextChapterEndpoint;

        $chapterContent = $crawler
            ->filter('#chapter-content')
            ->filter('p')
            ->each(function (Crawler $node, $i) {
                if ($node->text() === '') {
                    return "‎";
                }
                return $node->text();
            });

        return new self (
            title: $title,
            nextChapterUrl: $nextChapterUrl,
            chapter: $tr->translate($chapterTitle),
            content: (new TranslateChapterAction())->execute(implode("\n", $chapterContent)),
        );
    }
}

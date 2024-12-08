<?php

namespace App\Http\Controllers;

use Stichoza\GoogleTranslate\Exceptions\LargeTextException;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;
use Stichoza\GoogleTranslate\Exceptions\TranslationRequestException;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Symfony\Component\DomCrawler\Crawler;

class ChapterResume
{
    public const NOVELFULL_BASE_URL = 'https://novelfull.com';

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
        $nextChapterUrl = self::NOVELFULL_BASE_URL . $nextChapterEndpoint;

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
            content: $tr->translate(implode("\n", $chapterContent))
        );
    }
}

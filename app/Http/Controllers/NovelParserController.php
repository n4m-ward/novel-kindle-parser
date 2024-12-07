<?php

namespace App\Http\Controllers;

use App\Epub\Ebook;
use App\Epub\Epub;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NovelParserController
{
    /**
     * @throws \Exception
     */
    public function parse(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $url = $request->get('url');
        $quantity = $request->get('quantity', 1);

        if(!str_contains($url, ChapterResume::NOVELFULL_BASE_URL)) {
            throw new \Exception("Novel precisa pertencer ao site novelfull.com");
        }

        $chapterList = $this->parseMultiple($url, $quantity);
        /** @var ChapterResume $firstChapter */
        $firstChapter = $chapterList[0];
        $ebookTitle = $firstChapter->title . " ". $firstChapter->chapter;
        if($quantity > 1) {
            $ebookTitle .= " + ". $quantity - 1 ." caps";
        }
        $epub = new Epub();
        $epub->generate($ebookTitle, $chapterList);

        return response()->json();
    }

    private function parseMultiple(string $url, int $quantity): array
    {
        $output = [];
        $last = ChapterResume::loadByUrl($url);
        $output[] = $last;

        if($quantity === 1) {
            return $output;
        }

        foreach (range(1, $quantity) as $i) {
            $last = ChapterResume::loadByUrl($last->nextChapterUrl);
            $output[] = $last;
        }

        return $output;
    }
}

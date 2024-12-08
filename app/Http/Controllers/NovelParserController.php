<?php

namespace App\Http\Controllers;

use App\Epub\Epub;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NovelParserController
{
    /**
     * @throws \Exception
     */
    public function parse(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
            'email' => 'required|email',
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
            $ebookTitle .= " mais ". $quantity - 1 ." capitulos";
        }
        $epub = new Epub();
        $epubPath = $epub->generate($ebookTitle, $chapterList);
        $this->sendEpub($epubPath, $ebookTitle, $request->email);
        unlink($epubPath);

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

    private function sendEpub(string $path, string $title, string $email) {
        Mail::raw($title, function ($message) use ($path, $title, $email) {
            $message->to($email)
                ->subject($title)
                ->attach($path, [
                    'as' => str_replace(' ', '-', $title).'.epub',
                    'mime' => 'application/epub+zip',
                ]);
        });
    }
}

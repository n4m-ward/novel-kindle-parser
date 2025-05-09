<?php

namespace App\Actions;

use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateChapterAction
{
    private GoogleTranslate $tr;

    public function __construct()
    {
        $this->tr = new GoogleTranslate('pt');
    }

    public function execute(string $chapterContent): string
    {
        $chapterContentSplited = explode('.', $chapterContent);
        $chapterContentSplited = $this->joinTextWithLessThan2000Characters($chapterContentSplited);
        $chapterContentTranslated = array_map(fn(string $section) => $this->tr->translate($section), $chapterContentSplited);

        return implode('.', $chapterContentTranslated);
    }

    /**
     * @param string[] $chapterContentSplited
     * @return string[]
     */
    private function joinTextWithLessThan2000Characters(array $chapterContentSplited): array
    {
        $output = [];

        $intermediaryText = '';
        foreach ($chapterContentSplited as $section) {
            if ($intermediaryText === '') {
                $intermediaryText .= $section;
                continue;
            }
            if (strlen($intermediaryText) + strlen($section) < 2000) {
                $intermediaryText .= ". $section";
                continue;
            }
            $output[] = $intermediaryText;
            $intermediaryText = $section;
        }

        return $output;
    }
}

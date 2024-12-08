<?php

namespace App\Epub;

use App\Http\Controllers\ChapterResume;

class Epub
{
    private string $tempDir;

    /**
     * @param ChapterResume[] $chapters
     */
    public function __construct(
        private readonly string $bookName,
        private readonly array  $chapters = [],
    ) {
        $this->tempDir = storage_path("app/".uniqid('epub_'));
    }

    /**
     * @throws \Exception
     */
    public function generate(): string
    {
        $this->prepareDirectories();

        $this->createMimetype();
        $this->createContainerXML();
        $this->createContentOPF($this->bookName, $this->chapters);
        $this->createChapters($this->chapters);

        $epubFile = $this->generateEPUBFile($this->bookName, $this->chapters);

        $this->cleanTemporaryFiles();

        return $epubFile;
    }

    private function prepareDirectories(): void
    {
        mkdir($this->tempDir);
        mkdir($this->tempDir . '/META-INF');
    }

    private function createMimetype(): void
    {
        file_put_contents($this->tempDir . '/mimetype', 'application/epub+zip');
    }

    private function createContainerXML(): void
    {
        $containerXML = '<?xml version="1.0"?>
<container xmlns="urn:oasis:names:tc:opendocument:xmlns:container" version="1.0">
    <rootfiles>
        <rootfile full-path="content.opf" media-type="application/oebps-package+xml"/>
    </rootfiles>
</container>';
        file_put_contents($this->tempDir . '/META-INF/container.xml', $containerXML);
    }

    /**
     * @param string $bookName
     * @param ChapterResume[] $chapters
     * @return void
     */
    private function createContentOPF(string $bookName, array $chapters): void
    {
        $manifestItems = '';
        $spineItems = '';

        foreach ($chapters as $index => $chapter) {
            $chapterId = 'chapter' . ($index + 1);
            $manifestItems .= '<item id="' . $chapterId . '" href="' . $chapterId . '.xhtml" media-type="application/xhtml+xml"/>' . "\n";
            $spineItems .= '<itemref idref="' . $chapterId . '"/>' . "\n";
        }

        $contentOPF = '<?xml version="1.0" encoding="UTF-8"?>
<package xmlns="http://www.idpf.org/2007/opf" unique-identifier="BookID" version="3.0">
    <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
        <dc:title>' . htmlspecialchars($bookName) . '</dc:title>
        <dc:language>en</dc:language>
        <dc:identifier id="BookID">urn:uuid:' . uniqid() . '</dc:identifier>
    </metadata>
    <manifest>
        ' . $manifestItems . '
    </manifest>
    <spine>
        ' . $spineItems . '
    </spine>
</package>';
        file_put_contents($this->tempDir . '/content.opf', $contentOPF);
    }

    /**
     * @param ChapterResume[] $chapters
     * @return void
     */
    private function createChapters(array $chapters): void
    {
        foreach ($chapters as $index => $chapter) {
            $chapterContent = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>' . htmlspecialchars($chapter->chapter) . '</title>
    </head>
    <body>
        ' . nl2br(htmlspecialchars($chapter->chapter)) . '
        ' . nl2br(htmlspecialchars($chapter->content)) . '
    </body>
</html>';
            file_put_contents($this->tempDir . '/chapter' . ($index + 1) . '.xhtml', $chapterContent);
        }
    }

    private function generateEPUBFile(string $bookName, array $chapters): string
    {
        $epubFile = storage_path('app/' . preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($bookName)) . '.epub');
        $zip = new \ZipArchive();

        if ($zip->open($epubFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFile("$this->tempDir/mimetype", 'mimetype');

            $zip->addEmptyDir('META-INF');
            $zip->addFile("$this->tempDir/META-INF/container.xml", 'META-INF/container.xml');
            $zip->addFile("$this->tempDir/content.opf", 'content.opf');

            foreach ($chapters as $index => $chapter) {
                $chapterFile = 'chapter' . ($index + 1) . '.xhtml';
                $zip->addFile($this->tempDir . '/' . $chapterFile, $chapterFile);
            }

            $zip->close();
        } else {
            throw new \Exception("Erro ao criar o arquivo EPUB.");
        }

        return $epubFile;
    }

    private function cleanTemporaryFiles(): void
    {
        unlink("$this->tempDir/META-INF/container.xml");
        rmdir("$this->tempDir/META-INF");
        $files = glob($this->tempDir . '/*');
        foreach ($files as $file) {
            unlink($file);
        }
        rmdir($this->tempDir);
    }
}

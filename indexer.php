<?php

require_once __DIR__ . '/vendor/autoload.php';

use ZendSearch\Lucene\Lucene;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Document\Field;

define('INDEX_PATH',    __DIR__ . '/my_index');
define('DOCS_PATH',     __DIR__ . '/documents');


function buildIndex(): void
{
    if (is_dir(INDEX_PATH)) {
        array_map('unlink', glob(INDEX_PATH . '/*'));
        rmdir(INDEX_PATH);
    }

    $index = Lucene::create(INDEX_PATH);

    $files = glob(DOCS_PATH . '/*.txt');
    if (empty($files)) {
        echo "ERROR: No .txt files found in the /documents folder\n";
        return;
    }

    echo "=== Indexing Documents ===\n";
    foreach ($files as $file) {
        $content  = file_get_contents($file);
        $filename = basename($file);

        $doc = new Document();
        $doc->addField(Field::keyword('filename', $filename));
        $doc->addField(Field::text('content', $content));
        $doc->addField(Field::unIndexed('filepath', $file));

        $index->addDocument($doc);
        echo "  Indexed: $filename (" . str_word_count($content) . " words)\n";
    }

    $index->commit();
    echo "\nTotal indexed: " . count($files) . " documents.\n";
    echo "Index saved to: " . INDEX_PATH . "\n";
}

buildIndex();

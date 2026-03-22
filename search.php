<?php

require_once __DIR__ . '/vendor/autoload.php';

use ZendSearch\Lucene\Lucene;
use ZendSearch\Lucene\Search\QueryParser;
use ZendSearch\Lucene\Search\Query\Boolean as BooleanQuery;
use ZendSearch\Lucene\Search\Query\Term as TermQuery;
use ZendSearch\Lucene\Index\Term as IndexTerm;

define('INDEX_PATH', __DIR__ . '/my_index');

function openIndex()
{
    if (!is_dir(INDEX_PATH)) {
        die("ERROR: Index does not exist. Please run first: php indexer.php\n");
    }
    return Lucene::open(INDEX_PATH);
}

function countWordInText(string $word, string $text): int
{
    $text  = mb_strtolower($text, 'UTF-8');
    $word  = mb_strtolower($word, 'UTF-8');

    $pattern = '/\b' . preg_quote($word, '/') . '\b/u';
    preg_match_all($pattern, $text, $matches);
    return count($matches[0]);
}

function calculateTF(string $word, string $text): float
{
    $totalWords = str_word_count($text);
    if ($totalWords === 0) return 0.0;

    $count = countWordInText($word, $text);
    return round($count / $totalWords, 4);
}

function calculateIDF(int $totalDocs, int $docsWithWord): float
{
    if ($docsWithWord === 0) return 0.0;
    return round(log($totalDocs / $docsWithWord, 10), 4);
}


function cosineSimilarity(array $vecA, array $vecB): float
{
    $dot   = 0.0;
    $normA = 0.0;
    $normB = 0.0;

    $allKeys = array_unique(array_merge(array_keys($vecA), array_keys($vecB)));

    foreach ($allKeys as $key) {
        $a      = $vecA[$key] ?? 0.0;
        $b      = $vecB[$key] ?? 0.0;
        $dot   += $a * $b;
        $normA += $a * $a;
        $normB += $b * $b;
    }

    if ($normA === 0.0 || $normB === 0.0) return 0.0;
    return round($dot / (sqrt($normA) * sqrt($normB)), 4);
}

function getSnippet(string $word, string $text, int $chars = 60): string
{
    $pos = mb_stripos($text, $word);
    if ($pos === false) return '';

    $start   = max(0, $pos - 30);
    $snippet = mb_substr($text, $start, $chars + mb_strlen($word), 'UTF-8');
    $snippet = trim(preg_replace('/\s+/', ' ', $snippet));
    return $snippet;
}

$searchWord = $argv[1] ?? null;

if (!$searchWord) {
    echo "Usage:   php search.php <search_word>\n";
    echo "Example: php search.php PHP\n\n";

    echo "Enter a word to search: ";
    $searchWord = trim(fgets(STDIN));
}

if (empty($searchWord)) {
    die("Search word cannot be empty.\n");
}

$allFiles = glob(__DIR__ . '/documents/*.txt');
$allDocs  = [];
foreach ($allFiles as $file) {
    $allDocs[basename($file)] = file_get_contents($file);
}

$docsContainingWord = 0;
foreach ($allDocs as $content) {
    if (countWordInText($searchWord, $content) > 0) {
        $docsContainingWord++;
    }
}

$idf     = calculateIDF(count($allDocs), $docsContainingWord);
$results = [];

foreach ($allDocs as $filename => $content) {
    $count = countWordInText($searchWord, $content);
    $tf    = calculateTF($searchWord, $content);
    $tfidf = round($tf * $idf, 4);

  
    $docVec   = [$searchWord => $tfidf];
    $queryVec = [$searchWord => $idf > 0 ? 1.0 : 0.0]; 
    $cosine   = cosineSimilarity($queryVec, $docVec);

    $results[] = [
        'filename' => $filename,
        'count'    => $count,
        'tf'       => $tf,
        'idf'      => $idf,
        'tfidf'    => $tfidf,
        'cosine'   => $cosine,
        'snippet'  => getSnippet($searchWord, $content),
    ];
}

usort($results, fn($a, $b) => $b['tfidf'] <=> $a['tfidf']);

$line = str_repeat('─', 65);

echo "\n";
echo "╔" . str_repeat('═', 63) . "╗\n";
echo "║" . str_pad("  VSM SEARCH — word: \"$searchWord\"", 63) . "║\n";
echo "╚" . str_repeat('═', 63) . "╝\n\n";

echo "Statistics:\n";
echo "Total documents: ".count($allDocs)."\n";
echo "Documents with word:  $docsContainingWord\n";
echo "IDF(\"$searchWord\"):  $idf\n";
echo "\n$line\n\n";

echo "Results per document (sorted by TF-IDF descending):\n\n";

foreach ($results as $i => $r) {
    $rank = $i + 1;
    $star = $r['count'] > 0 ? '⭐' : '○';
    echo "$star Rank $rank: {$r['filename']}\n";
    echo "   Occurrences (count): {$r['count']}\n";
    echo "   TF:                  {$r['tf']}\n";
    echo "   IDF:                 {$r['idf']}\n";
    echo "   TF-IDF:              {$r['tfidf']}\n";
    echo "   Cosine Similarity:   {$r['cosine']}\n";
    if ($r['snippet']) {
        echo "   Context:             ...{$r['snippet']}...\n";
    }
    echo "\n";
}

echo "$line\n";
echo "Search completed!\n\n";

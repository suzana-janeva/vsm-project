<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vector Space Model — Search</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #5540ae00 0%, #5540ae55 100%);
            min-height: 100vh;
            padding: 30px 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
            color: #5540ae;
        }

        header h1 { font-size: 2.2rem; margin-bottom: 8px; }
        header p  { font-size: 1rem; opacity: 0.85; }

        .search-box {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            margin-bottom: 24px;
        }

        .search-row {
            display: flex;
            gap: 12px;
        }

        input[type="text"] {
            flex: 1;
            padding: 14px 18px;
            font-size: 1.1rem;
            border: 2px solid #c0d4e8;
            border-radius: 8px;
            outline: none;
            transition: border-color 0.2s;
        }

        input[type="text"]:focus { border-color: #2e5f8a; }

        button {
            padding: 14px 28px;
            background: #5540ae;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover { background: #2e5f8a; }

        .stats-bar {
            background: #c8c2e0;
            border-left: 4px solid #2e5f8a;
            padding: 12px 18px;
            border-radius: 0 8px 8px 0;
            margin-top: 16px;
            font-size: 0.92rem;
            color: #333;
        }

        .results-section { margin-top: 8px; }

        .result-card {
            background: white;
            border-radius: 10px;
            padding: 20px 24px;
            margin-bottom: 16px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.1);
            border-left: 5px solid #2e5f8a;
            transition: transform 0.15s;
        }

        .result-card:hover { transform: translateX(4px); }

        .result-card.no-match {
            border-left-color: #aaa;
            opacity: 0.6;
        }

        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .filename {
            font-weight: bold;
            font-size: 1.05rem;
            color: #5540ae;
        }

        .rank-badge {
            background: #5540ae;
            color: white;
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 0.82rem;
            font-weight: bold;
        }

        .metrics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 10px;
        }

        .metric {
            text-align: center;
            background: #c8c2e0;
            border-radius: 8px;
            padding: 10px 6px;
        }

        .metric-value {
            font-size: 1.3rem;
            font-weight: bold;
            color: #1a3a5c;
        }

        .metric-label {
            font-size: 0.72rem;
            color: #666;
            margin-top: 2px;
        }

        .snippet {
            background: #fff8e6;
            border: 1px solid #ffe0a0;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 0.9rem;
            color: #555;
            margin-top: 10px;
        }

        .highlight { background: #ffe066; border-radius: 3px; padding: 0 2px; font-weight: bold; }

        .no-results {
            background: white;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            color: #888;
            box-shadow: 0 3px 12px rgba(0,0,0,0.1);
        }

        .theory-box {
            background: white;
            border-radius: 12px;
            padding: 24px 28px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }

        .theory-box h3 { color: #5540ae; margin-bottom: 14px; }
        .formula {
            background: #f0f4f8;
            border-radius: 6px;
            padding: 10px 16px;
            font-family: 'Courier New', monospace;
            font-size: 0.95rem;
            color: #5540ae;
            margin: 8px 0;
        }
    </style>
</head>
<body>
<div class="container">

    <header>
        <h1>Vector Space Model</h1>
        <p>Word frequency and ranking in documents</p>
        <p style="font-size:0.85rem; margin-top:6px; opacity:0.7;">PHP + ZendSearch Lucene</p>
    </header>

    <div class="theory-box">
        <h3>Vector Space Model</h3>
        <div class="formula">TF(t, d)  = (count of t in d) / (total words in d)</div>
        <div class="formula">IDF(t)    = log₁₀( N / df(t) )</div>
        <div class="formula">TF-IDF   = TF × IDF</div>
        <div class="formula">cos(q, d) = (q · d) / (|q| × |d|)</div>
    </div>

    <div class="search-box">
        <form method="POST" action="">
            <div class="search-row">
                <input type="text"
                       name="word"
                       placeholder="Enter a word to search (e.g. PHP, database, retrieval)..."
                       value="<?= htmlspecialchars($_POST['word'] ?? '') ?>"
                       autofocus>
                <button type="submit">Search</button>
            </div>
        </form>

        <?php

        $searchWord = trim($_POST['word'] ?? '');
        $results    = [];
        $stats      = [];

        if ($searchWord !== '') {
            $allDocs = [];
            foreach (glob(__DIR__ . '/documents/*.txt') as $file) {
                $allDocs[basename($file)] = file_get_contents($file);
            }

            $totalDocs          = count($allDocs);
            $docsContainingWord = 0;

          foreach ($allDocs as $content) {
    $pattern = '/\b' . preg_quote($searchWord, '/') . '\b/ui';  
    if (preg_match($pattern, $content)) {
        $docsContainingWord++;
    }
}

            $idf = $docsContainingWord > 0
                ? round(log10($totalDocs / $docsContainingWord), 4)
                : 0;

            foreach ($allDocs as $filename => $content) {
                $totalWords = str_word_count($content);
                $pattern    = '/\b' . preg_quote($searchWord, '/') . '\b/ui';
                preg_match_all($pattern, $content, $m);
                $count      = count($m[0]);
                $tf         = $totalWords > 0 ? round($count / $totalWords, 4) : 0;
                $tfidf      = round($tf * $idf, 4);

                $pos     = mb_stripos($content, $searchWord);
                $snippet = '';
                if ($pos !== false) {
                    $start   = max(0, $pos - 40);
                    $raw     = mb_substr($content, $start, 120, 'UTF-8');
                    $snippet = trim(preg_replace('/\s+/', ' ', $raw));
                    $snippet = preg_replace(
                        '/(' . preg_quote(htmlspecialchars($searchWord), '/') . ')/iu',
                        '<span class="highlight">$1</span>',
                        htmlspecialchars($snippet)
                    );
                }

                $results[] = compact('filename', 'count', 'tf', 'idf', 'tfidf', 'snippet');
            }

            usort($results, fn($a, $b) => $b['tfidf'] <=> $a['tfidf']);

            $stats = [
                'total'    => $totalDocs,
                'matching' => $docsContainingWord,
                'idf'      => $idf,
            ];
        }
        ?>

        <?php if ($searchWord !== ''): ?>
        <div class="stats-bar">
            Total documents: <strong><?= $stats['total'] ?></strong>
            &nbsp;|&nbsp; Documents with word: <strong><?= $stats['matching'] ?></strong>
            &nbsp;|&nbsp; IDF("<?= htmlspecialchars($searchWord) ?>"): <strong><?= $stats['idf'] ?></strong>
        </div>
        <?php endif; ?>
        <?php if ($searchWord === '' && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="stats-bar" style="border-left-color: #e67e22; background: #fff3e0;">
            ⚠️ Please enter a word before searching.
        </div>
        <?php endif; ?>
    </div>

    <?php if ($searchWord !== ''): ?>
    <div class="results-section">
        <?php if (empty($results) || $stats['matching'] === 0): ?>
            <div class="no-results">
                <p style="font-size:2rem;">🔍</p>
                <p>The word "<strong><?= htmlspecialchars($searchWord) ?></strong>" was not found in any document.</p>
            </div>
        <?php else: ?>
            <?php foreach ($results as $i => $r): ?>
            <div class="result-card <?= $r['count'] === 0 ? 'no-match' : '' ?>">
                <div class="result-header">
                    <span class="filename">📄 <?= htmlspecialchars($r['filename']) ?></span>
                    <span class="rank-badge">Rank <?= $i + 1 ?></span>
                </div>
                <div class="metrics">
                    <div class="metric">
                        <div class="metric-value"><?= $r['count'] ?></div>
                        <div class="metric-label">Occurrences</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value"><?= $r['tf'] ?></div>
                        <div class="metric-label">TF</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value"><?= $r['idf'] ?></div>
                        <div class="metric-label">IDF</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value" style="color:#e67e22;"><?= $r['tfidf'] ?></div>
                        <div class="metric-label">TF-IDF</div>
                    </div>
                </div>
                <?php if ($r['snippet']): ?>
                <div class="snippet">...<?= $r['snippet'] ?>...</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
</body>
</html>

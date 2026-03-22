# Vector Space Model — PHP Search Engine

A web-based search engine that implements the **Vector Space Model (VSM)** for document retrieval and ranking. Built with PHP and ZendSearch Lucene.

🌐 **Live Demo:** [https://vsm.suzanajaneva.com](https://vsm.suzanajaneva.com)

---

## What is the Vector Space Model?

The Vector Space Model (VSM) is a mathematical model used in information retrieval. It represents documents and queries as vectors in a multi-dimensional space. The relevance of a document to a search query is determined by calculating the similarity between their vectors.

This project demonstrates how VSM works by allowing users to search a single word across multiple text documents and see exactly how TF, IDF, TF-IDF, and Cosine Similarity are calculated in real time.

---

## Features

-  Real-time word search across multiple documents
-  Displays TF, IDF, TF-IDF, and Cosine Similarity for each document
-  Shows a highlighted text snippet where the word was found
-  Documents are ranked by TF-IDF score (most relevant first)
-  User-friendly warning when search input is empty
-  Case-insensitive search (PHP = php = Php)
-  Whole-word matching only (searching `dat` will not match `database`)

---

## VSM Metrics

| Metric | Formula | Description |
|--------|---------|-------------|
| **TF** | `f(t,d) / N(d)` | How often the word appears in the document, normalized by total words |
| **IDF** | `log₁₀(N / df(t))` | How rare the word is across all documents |
| **TF-IDF** | `TF × IDF` | Overall importance of the word in a specific document |
| **Cosine** | `(q·d) / (│q│×│d│)` | Similarity between the search query and the document |

---

## Project Structure

```
vsm_project/
├── index.php          ← Web interface (main page)
├── indexer.php        ← CLI script for building the Lucene index
├── search.php         ← CLI script for searching via terminal
├── composer.json      ← Project dependencies
├── .gitignore         ← Excludes vendor/ and my_index/
├── documents/         ← Text documents to search through
│   ├── doc1.txt       ← PHP and Web Development
│   ├── doc2.txt       ← Information Retrieval and Search Engines
│   └── doc3.txt       ← Databases and Data Management
└── my_index/          ← Lucene index (auto-generated, not in Git)
```

---

## Requirements

- PHP >= 7.4
- Composer
- ZendSearch Lucene (`zendframework/zendsearch`)

---

## Installation

### Step 1 — Clone the repository
```bash
git clone https://github.com/suzana-janeva/vsm-project.git
cd vsm-project
```

### Step 2 — Install dependencies
```bash
composer install
```

### Step 3 — Add documents
Place `.txt` files inside the `documents/` folder.

### Step 4 — Build the Lucene index
```bash
php indexer.php
```

### Step 5 — Start the web server
```bash
php -S localhost:8000
```

Open `http://localhost:8000` in browser.

---

## CLI Usage

You can also search directly from the terminal without the web interface:

```bash
php search.php PHP
php search.php database
php search.php retrieval
```

---

## How It Works

1. All `.txt` files in the `documents/` folder are loaded into memory
2. The search word is matched against each document using whole-word regex matching
3. **TF** is calculated as the number of occurrences divided by total words in the document
4. **IDF** is calculated as log₁₀ of total documents divided by documents containing the word
5. **TF-IDF** is calculated by multiplying TF × IDF
6. Documents are sorted by TF-IDF score from highest to lowest
7. A highlighted snippet is shown for each document where the word was found

---

## .gitignore

The following folders are excluded from version control:

```
vendor/
my_index/
```

Run `composer install` after cloning to restore the `vendor/` folder.

---

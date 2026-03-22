# VSM Word Counter — PHP + ZendSearch Lucene

**Семинарска работа: Модел на векторски простор (VSM)**

---

## 📁 Структура на проектот

```
vsm_php_project/
├── index.php          ← Веб интерфejс (главна страна)
├── indexer.php        ← CLI скрипт за индексирање
├── search.php         ← CLI скрипт за пребарување
├── composer.json      ← Зависности (ZendSearch Lucene)
├── documents/         ← Папка со текстуални документи
│   ├── doc1.txt
│   ├── doc2.txt
│   └── doc3.txt
└── my_index/          ← Lucene индекс (се генерира автоматски)
```

---

## 🚀 Инсталација

### Чекор 1: Инсталирај Composer зависности
```bash
composer install
```

### Чекор 2: Додади документи
Стави `.txt` датотеки во папката `documents/`.

### Чекор 3: Изгради индекс
```bash
php indexer.php
```

---

## 🔍 Употреба

### CLI (командна линија):
```bash
php search.php PHP
php search.php Lucene
php search.php "база"
```

### Веб интерфejс:
```bash
php -S localhost:8000
# Отвори http://localhost:8000 во прелистувачот
```

---

## 📐 Метрики

| Метрика | Формула | Опис |
|---------|---------|------|
| **TF** | `f(t,d) / N(d)` | Нормализирана фреквенција на терминот |
| **IDF** | `log₁₀(N / df(t))` | Обратна фреквенција на документот |
| **TF-IDF** | `TF × IDF` | Тежина на терминот во документот |
| **Косинус** | `(q·d) / (\|q\|×\|d\|)` | Сличност меѓу барање и документ |

---

## 🛠️ Барања

- PHP >= 7.4
- Composer
- ZendSearch Lucene (`zendframework/zendsearch`)

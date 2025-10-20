<?php

namespace app\components;

class WordpressDumpParser
{
    /**
     * Parse WordPress SQL dump and extract post titles and contents.
     * Detects any table prefix automatically and ignores non-posts tables.
     * Cleans HTML by removing <a> links and <img> tags.
     *
     * @param string $filePath
     * @return array
     */
    public static function parsePosts(string $filePath): array
    {
        if (!is_file($filePath)) {
            return [];
        }

        $sql = file_get_contents($filePath);

        // Detect table prefix dynamically
        if (!preg_match('/CREATE\s+TABLE\s+[`"]?([a-zA-Z0-9_]+)_posts[`"]?/i', $sql, $m)) {
            if (preg_match('/INSERT\s+INTO\s+[`"]?([a-zA-Z0-9_]+)_posts[`"]?/i', $sql, $m)) {
                $prefix = $m[1];
            } else {
                $prefix = 'wp'; // fallback default
            }
        } else {
            $prefix = $m[1];
        }

        // Define known WordPress tables
        $wpTables = ['_posts', '_postmeta', '_terms', '_options'];

        // Build regex patterns (only _posts will be used for extraction)
        $patterns = [];
        foreach ($wpTables as $suffix) {
            $patterns[$suffix] = "/INSERT\s+INTO\s+[`\"]?{$prefix}{$suffix}[`\"]?\s*\([^)]+\)\s*VALUES\s*(.+?);/is";
        }

        // Match INSERT statements for posts table
        if (!preg_match_all($patterns['_posts'], $sql, $matches)) {
            return [];
        }

        $posts = [];
        foreach ($matches[1] as $valuesBlock) {
            preg_match_all('/\((.*?)\)/s', $valuesBlock, $rows);
            foreach ($rows[1] as $row) {
                // Safely split CSV row into columns
                $columns = str_getcsv($row, ',', "'", "\\");

                // WordPress wp_posts order:
                // ID, post_author, post_date, post_date_gmt, post_content, post_title, ...
                $content = $columns[4] ?? '';
                $title   = $columns[5] ?? '';

                if (empty($title)) {
                    continue;
                }

                // --- Clean HTML content ---
                $content = self::cleanHtmlContent($content);

                $posts[] = [
                    'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5),
                    'content' => html_entity_decode($content, ENT_QUOTES | ENT_HTML5),
                ];
            }
        }

        return $posts;
    }

    /**
     * Remove <a> tags but keep inner text; remove <img> tags completely.
     */
    private static function cleanHtmlContent(string $html): string
    {
        // Remove all <img> tags (entirely)
        $html = preg_replace('/<img\b[^>]*>/i', '', $html);

        // Remove <a> tags but keep their inner text
        $html = preg_replace('/<a\b[^>]*>(.*?)<\/a>/is', '$1', $html);

        // Optional: Trim extra whitespace
        $html = trim(preg_replace('/\s+/', ' ', $html));

        return $html;
    }
}

<?php

namespace app\components;

class CmsDetector
{
    /**
     * Detect CMS name from SQL dump by scanning table names.
     *
     * @param string $filePath  Path to SQL dump
     * @return string  Detected CMS name or "Unknown"
     */
    public static function detect($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("SQL dump file not found: {$filePath}");
        }

        $sql = file_get_contents($filePath);

        $patterns = [
            'WordPress' => ['_posts', '_options', '_users'],
            'Joomla'    => ['#__content', 'joomla_users', 'jos_users', 'extensions'],
            'Drupal'    => ['node', 'taxonomy_term_data', 'field_data_body'],
            'Magento'   => ['catalog_product_entity', 'sales_order', 'core_config_data'],
            'OpenCart'  => ['oc_product', 'oc_category', 'oc_order'],
        ];

        foreach ($patterns as $cms => $keywords) {
            foreach ($keywords as $word) {
                if (stripos($sql, $word) !== false) {
                    return $cms;
                }
            }
        }

        return 'Unknown';
    }
}

<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\components\WordpressDumpParser;

class WpParseController extends Controller
{
    /**
     * Detect CMS type by DB dump file.
     * Run with CLI 'php yii wp-parse/run'
     *
     * @return void
     */
    public function actionRun()
    {
        $filePath = Yii::getAlias('@app/sql_dumps/db1.sql');

        // Detect CMS
        $cms = \app\components\CmsDetector::detect($filePath);
        echo "🧩 Detected CMS: {$cms}\n";

        // Parse Wordpress posts
        $posts = WordpressDumpParser::parsePosts($filePath);
        foreach ($posts as $post) {
            echo "📰 {$post['title']} ({$post['date']})\n";
        }

        echo "\nTotal posts parsed: " . count($posts) . "\n";
    }
}

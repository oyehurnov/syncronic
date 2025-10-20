<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class DumpController extends Controller
{
    public $dumpPath;

    /**
     * @return void
     * @throws \yii\base\Exception
     */
    public function init()
    {
        parent::init();
        $this->dumpPath = Yii::getAlias('@app/sql_dumps');
        FileHelper::createDirectory($this->dumpPath);
    }

    /**
     * @return array
     */
    protected function getFileList()
    {
        $files = glob($this->dumpPath . '/*.sql');
        return array_map('basename', $files);
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'files' => $this->getFileList(),
        ]);
    }

    // --- AJAX partial refresh ---

    /**
     * @return string
     */
    public function actionList()
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        return $this->renderPartial('_fileList', [
            'files' => $this->getFileList(),
        ]);
    }

    /**
     * @return array|true[]
     */
    public function actionUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $file = UploadedFile::getInstanceByName('dumpFile');
        if ($file) {
            $path = $this->dumpPath . '/' . $file->baseName . '.' . $file->extension;
            $file->saveAs($path);
            return ['success' => true];
        }
        return ['success' => false, 'error' => 'No file uploaded'];
    }

    /**
     * @return array|true[]
     */
    public function actionDelete()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $name = Yii::$app->request->post('name'); // ✅ use POST param
        if (!$name) {
            return ['success' => false, 'error' => 'No filename provided'];
        }

        $path = $this->dumpPath . '/' . basename($name);

        if (file_exists($path)) {
            if (@unlink($path)) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Could not delete file (permissions?)'];
            }
        }
        return ['success' => false, 'error' => 'File not found'];
    }

    /**
     * @return array
     * @throws \DOMException
     */
    public function actionParse()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $selected = Yii::$app->request->post('files', []);
        if (empty($selected)) {
            return ['success' => false, 'error' => 'No files selected'];
        }

        $downloadPath = Yii::getAlias('@webroot/downloads');
        if (!is_dir($downloadPath)) {
            if (!mkdir($downloadPath, 0777, true) && !is_dir($downloadPath)) {
                return ['success' => false, 'error' => 'Failed to create downloads folder'];
            }
        }

        $xmlPath = $downloadPath . '/parsed_news.xml';
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        $root = $doc->createElement('posts');
        $doc->appendChild($root);

        foreach ($selected as $file) {
            $filePath = $this->dumpPath . '/' . basename($file);
            $posts = \app\components\WordpressDumpParser::parsePosts($filePath);

            foreach ($posts as $p) {
                $postNode = $doc->createElement('post');

                $titleNode = $doc->createElement('title');
                $titleNode->appendChild($doc->createTextNode($p['title']));
                $postNode->appendChild($titleNode);

                $contentNode = $doc->createElement('content');
                $contentNode->appendChild($doc->createCDATASection($p['content']));
                $postNode->appendChild($contentNode);

                $root->appendChild($postNode);
            }
        }

        $doc->save($xmlPath);

        $xmlUrl = Yii::$app->request->baseUrl . '/downloads/parsed_news.xml';

        return [
            'success' => true,
            'count' => $root->childNodes->length,
            'xmlUrl' => $xmlUrl,
        ];
    }

    public function actionDownloadXml()
    {
        $xmlPath = Yii::getAlias('@webroot/downloads/parsed_news.xml');
        if (!file_exists($xmlPath)) {
            throw new \yii\web\NotFoundHttpException('File not found.');
        }

        return Yii::$app->response->sendFile($xmlPath, 'parsed_news.xml', [
            'mimeType' => 'application/xml',
            'inline' => false,
        ]);
    }
}

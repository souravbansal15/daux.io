<?php
namespace Todaymade\Daux\Format\Template;

use org\bovigo\vfs\vfsStream;
use Todaymade\Daux\Config;
use Todaymade\Daux\ConfigBuilder;
use Todaymade\Daux\Daux;
use Todaymade\Daux\DauxHelper;
use Todaymade\Daux\Format\HTML\Template;
use Todaymade\Daux\Tree\Builder;
use Todaymade\Daux\Tree\Entry;
use Todaymade\Daux\Tree\Root;
use PHPUnit\Framework\TestCase;

/**
 * Class TranslateTest
 *
 * @package Todaymade\Daux\Format\Template
 */
class TranslateTest extends TestCase
{
    protected function getTree(Config $config)
    {
        $structure = [
            'en' => [
                'Page.md' => 'some text content',
            ],
            'it' => [
                'Page.md' => 'another page',
            ],
        ];
        $root = vfsStream::setup('root', null, $structure);

        $config = ConfigBuilder::withMode()
            ->withDocumentationDirectory($root->url())
            ->withValidContentExtensions(['md'])
            ->build();


        $tree = new Root($config);
        Builder::build($tree, []);

        return $tree;
    }

    public function translateDataProvider()
    {
        return [
            ['Previous', 'en'],
            ['Pagina precedente', 'it'],
        ];
    }

    /**
     * @dataProvider translateDataProvider
     *
     * @param $expectedTranslation
     * @param $language
     */
    public function testTranslate($expectedTranslation, $language)
    {
        $current = $language . '/Page.html';
        $entry = $this->prophesize(Entry::class);

        $config = new Config();
        $config->setTree($this->getTree($config));
        $config->merge([
            'title' => '',
            'index' => $entry->reveal(),
            'language' => $language,
            'base_url' => '',
            'templates' => '',
            'page' => [
                'language' => $language,
            ],
            'html' => [
                'search'           => '',
                'toggle_code'      => false,
                'piwik_analytics'  => '',
                'google_analytics' => '',
            ],
            'theme' => [
                'js'        => [''],
                'css'       => [''],
                'fonts'     => [''],
                'favicon'   => '',
                'templates' => 'name',
            ],
            'strings' => [
                'en' => ['Link_previous' => 'Previous',],
                'it' => ['Link_previous' => 'Pagina precedente',],
            ]
        ]);

        $config->setCurrentPage(DauxHelper::getFile($config->getTree(), $current));

        $template = new Template($config);
        $value = $template->getEngine($config)->getFunction('translate')->call(null, ['Link_previous']);

        $this->assertEquals($expectedTranslation, $value);
    }
}

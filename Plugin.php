<?php
namespace TypechoPlugin\Meta;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget;
use Typecho\Widget\Helper\Form;
use Widget\Archive;
use Widget\Options;
use Widget\Upload;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * Meta Plugin
 * @package Meta
 * @author Kyne Wang
 * @version 1.0.1
 * @link https://kyne.wang
 */
class Plugin implements PluginInterface
{

    /**
     * 激活方法
     */
    public static function activate(): string
    {
        \Typecho\Plugin::factory("Widget_Archive")->header = [ static::class, 'render' ];

        return _t('Meta 插件啟動成功');
    }

    /**
     * 取消激活
     */
    public static function deactivate()
    {
        return _t('Meta 插件已關閉');
    }

    /**
     * 取消激活
     */
    public static function config(Form $form)
    {

        $layout = new Widget\Helper\Layout("div");


        $title = new Widget\Helper\Layout("h2");
        $title->html("Twitter 分享設置");

        $layout->addItem($title);
        $form->addItem($layout);

        // 網站 Username
        $site = new Form\Element\Text('site', NULL, "", _t('網站關聯賬戶'), _t('設置網站關聯賬戶，以 @ 開始'));
        $form->addInput($site);

        // 創作者 Username
        $creator = new Form\Element\Text('creator',
            NULL, "", _t('Twitter 創作者賬戶'), _t('請設置 Twitter 創作者賬戶，以 @ 開始'));
        $form->addInput($creator);

        // 默認封面圖
        $cover = new Form\Element\Text('cover',
            NULL, "", _t('默認封面圖'), _t('請設置默認封面圖 URL，以 http(s):// 開始，將會在未找到封面圖時展示'));
        $form->addInput($cover);

        $choices = [
            NULL => '默认自动适配',
            'summary_large_image' => 'summary_large_image 大图',
            'summary' => 'summary 概要'
        ];

        $card = new Form\Element\Select("card", $choices, "", _t('設置默認 Card 類型'), _t('請選擇默認的 Card 類型，將會在分享時展示'));

        $form->addInput($card);
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form): void {}

    public static function getCover(Archive $archive): string
    {
        $attachment = $archive->attachments()->attachment;
        return empty($attachment) ?: $attachment->url;
    }

    public static function render($header, Archive $archive)
    {

        $options = Options::alloc();
        $meta = $options->plugin('Meta');

        $is_index = $archive->is('index');

        $card = $meta->card;
        $image = $is_index ? $meta->cover : self::getCover($archive);

        if ( empty($card) && $image ) {
            $card = 'summary_large_image';
        }


        $site = $meta->site;
        $creator = $meta->creator;
        $title = $is_index ? $options->title : $archive->title;
        $description = $is_index ? $options->description : $archive->getDescription();

        $allows = [
            'name' => [
                'twitter:card'      => $card,
                'twitter:creator'   => $creator,
                'twitter:site'      => $site
            ],
            'property' => [
                'og:type'           => '',
                'og:title'          => $title,
                'og:image'          => $image,
                'og:description'    => $description
            ]
        ];

        $header = "";

        foreach ($allows as $key => $value) {
            if ( ! is_array($value) ) {
                break;
            }

            foreach ($value as $name => $content) {
                if ( $content ) {
                    $header .= sprintf('<meta %s="%s" content="%s"/>', $key, $name, $content) . "\n";
                }
            }
        }

        echo $header;
    }
}

<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\ServicesProvider;

use League\CommonMark\ConverterInterface;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\MarkdownConverter;
use PomoDocs\CommonMark\Alert\AlertExtension;
use UserFrosting\ServicesProvider\ServicesProviderInterface;

/**
 * Custom Markdown service with extra extensions.
 *
 * @see https://commonmark.thephpleague.com
 */
class MarkdownService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            ConverterInterface::class => function () {
                $config = [
                    'heading_permalink' => [
                        'id_prefix'           => '',
                        'apply_id_to_heading' => true,
                        'fragment_prefix'     => '',
                        'insert'              => 'after',
                        'symbol'              => '#',
                    ],
                    'alert' => [
                        'class_name' => 'uk-alert',
                        'colors'     => [
                            'note'      => 'primary',
                            'tip'       => 'success',
                            'important' => 'warning',
                            'warning'   => 'danger',
                            'caution'   => 'danger',
                        ],
                        'icons' => [
                            'active'  => true,
                            'names'   => [
                                'note'      => 'fa-solid fa-circle-info uk-icon',
                                'tip'       => 'fa-regular fa-lightbulb uk-icon',
                                'important' => 'fa-solid fa-circle-exclamation uk-icon',
                                'warning'   => 'fa-solid fa-triangle-exclamation uk-icon',
                                'caution'   => 'fa-solid fa-radiation uk-icon'
                            ],
                        ]
                    ]
                ];

                $environment = new Environment($config);
                $environment->addExtension(new CommonMarkCoreExtension());
                $environment->addExtension(new FrontMatterExtension());
                $environment->addExtension(new GithubFlavoredMarkdownExtension());
                $environment->addExtension(new HeadingPermalinkExtension());
                $environment->addExtension(new TableOfContentsExtension());
                $environment->addExtension(new FootnoteExtension());
                $environment->addExtension(new AlertExtension());

                // Instantiate the converter engine and start converting some Markdown!
                $converter = new MarkdownConverter($environment);

                return $converter;
            },
        ];
    }
}

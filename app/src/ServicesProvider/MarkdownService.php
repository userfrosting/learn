<?php

declare(strict_types=1);

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\ServicesProvider;

use League\CommonMark\ConverterInterface;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkRenderer;
use League\CommonMark\MarkdownConverter;
use UserFrosting\Config\Config;
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
                        'id_prefix' => '',
                        'apply_id_to_heading' => true,
                        'fragment_prefix' => '',
                        'insert' => 'after',
                        'symbol' => '#',
                    ],
                ];
                
                $environment = new Environment($config);
                $environment->addExtension(new CommonMarkCoreExtension());
                $environment->addExtension(new FrontMatterExtension());
                $environment->addExtension(new GithubFlavoredMarkdownExtension());
                $environment->addExtension(new HeadingPermalinkExtension());

                // Instantiate the converter engine and start converting some Markdown!
                $converter = new MarkdownConverter($environment);

                return $converter;
            },
        ];
    }
}

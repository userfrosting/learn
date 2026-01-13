<?php

declare(strict_types=1);

/*
 * UserFrosting Learn (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/Learn
 * @copyright Copyright (c) 2025 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/Learn/blob/main/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Bakery;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Learn\Search\SearchIndex;
use UserFrosting\Sprinkle\Core\Bakery\BaseCommand;

/**
 * Bakery command to rebuild the search index for documentation.
 */
class SearchIndexCommand extends BaseCommand
{
    /**
     * @param SearchIndex $searchIndex
     */
    public function __construct(
        protected SearchIndex $searchIndex,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('search:index')
            ->setDescription('Build or rebuild the search index for documentation')
            ->addOption(
                'version',
                null,
                InputOption::VALUE_OPTIONAL,
                'Documentation version to index (omit to index all versions)'
            )
            ->addOption(
                'clear',
                null,
                InputOption::VALUE_NONE,
                'Clear the search index before rebuilding'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Documentation Search Index');

        /** @var string|null $version */
        $version = $input->getOption('version');
        $clear = $input->getOption('clear');

        // Clear index if requested
        if ($clear) {
            $this->io->writeln('Clearing search index...');
            $this->searchIndex->clearIndex($version);
            $this->io->success('Search index cleared.');
        }

        // Build index
        $versionText = $version !== null ? "version {$version}" : 'all versions';
        $this->io->writeln("Building search index for {$versionText}...");

        try {
            $count = $this->searchIndex->buildIndex($version);
            $this->io->success("Search index built successfully. Indexed {$count} pages.");
        } catch (\Exception $e) {
            $this->io->error("Failed to build search index: {$e->getMessage()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

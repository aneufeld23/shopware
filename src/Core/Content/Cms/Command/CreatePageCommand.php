<?php declare(strict_types=1);

namespace Shopware\Core\Content\Cms\Command;

use Faker\Factory;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('cms:page:create')]
#[Package('buyers-experience')]
class CreatePageCommand extends Command
{
    /**
     * @var array<string>
     */
    private array $products;

    /**
     * @var array<string>
     */
    private array $categories;

    /**
     * @var array<string>
     */
    private array $media;

    public function __construct(
        private readonly EntityRepository $cmsPageRepository,
        private readonly EntityRepository $productRepository,
        private readonly EntityRepository $categoryRepository,
        private readonly EntityRepository $mediaRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('reset', null, InputOption::VALUE_NONE, 'Reset all pages');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();

        if ($input->getOption('reset')) {
            $this->resetPages($context);
        }

        $faker = Factory::create();

        $page = [
            'id' => Uuid::randomHex(),
            'name' => $faker->company,
            'type' => 'landing_page',
            'blocks' => [
                [
                    'type' => 'image-text',
                    'slots' => [
                        ['type' => 'product-box', 'slot' => 'left', 'config' => ['productId' => $this->getRandomProductId($context)]],
                        ['type' => 'image', 'slot' => 'right', 'config' => ['url' => $this->getRandomImageUrl()]],
                    ],
                ],
                [
                    'type' => 'image-text',
                    'slots' => [
                        ['type' => 'text', 'slot' => 'left', 'config' => ['content' => $faker->realText()]],
                        ['type' => 'product-box', 'slot' => 'right', 'config' => ['productId' => $this->getRandomProductId($context)]],
                    ],
                ],
                [
                    'type' => 'image-text',
                    'slots' => [
                        ['type' => 'text', 'slot' => 'right', 'config' => ['content' => $faker->realText()]],
                        ['type' => 'image', 'slot' => 'left', 'config' => ['mediaId' => $this->getRandomMediaId($context)]],
                    ],
                ],
                [
                    'type' => 'listing',
                    'slots' => [
                        ['type' => 'product-listing', 'slot' => 'listing', 'config' => ['categoryId' => $this->getRandomCategoryId($context)]],
                    ],
                ],
            ],
        ];

        $this->cmsPageRepository->create([$page], $context);

        $output->writeln('ID: ' . $page['id']);

        return self::SUCCESS;
    }

    private function resetPages(Context $context): void
    {
        $criteria = new Criteria();
        $criteria->setLimit(999);

        $pages = $this->cmsPageRepository->searchIds($criteria, $context);

        if ($pages->getTotal() === 0) {
            return;
        }

        $keys = array_map(fn ($id) => ['id' => $id], $pages->getIds());

        $this->cmsPageRepository->delete($keys, $context);
    }

    private function getRandomImageUrl(): string
    {
        return 'https://source.unsplash.com/random?t=' . random_int(1, 9999);
    }

    private function getRandomProductId(Context $context): string
    {
        if (empty($this->products)) {
            $criteria = new Criteria();
            $criteria->setLimit(100);

            /** @var list<string> $productIds */
            $productIds = $this->productRepository->searchIds($criteria, $context)->getIds();
            $this->products = $productIds;
        }

        return $this->products[array_rand($this->products)];
    }

    private function getRandomCategoryId(Context $context): string
    {
        if (empty($this->categories)) {
            $criteria = new Criteria();
            $criteria->setLimit(100);

            /** @var list<string> $categoryIds */
            $categoryIds = $this->categoryRepository->searchIds($criteria, $context)->getIds();
            $this->categories = $categoryIds;
        }

        return $this->categories[array_rand($this->categories)];
    }

    private function getRandomMediaId(Context $context): string
    {
        if (empty($this->media)) {
            $criteria = new Criteria();
            $criteria->setLimit(100);

            /** @var list<string> $mediaIds */
            $mediaIds = $this->mediaRepository->searchIds($criteria, $context)->getIds();
            $this->media = $mediaIds;
        }

        return $this->media[array_rand($this->media)];
    }
}

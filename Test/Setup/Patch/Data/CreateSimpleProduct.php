<?php

declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;

class CreateSimpleProduct implements DataPatchInterface
{
    protected ModuleDataSetupInterface $setup;
    protected ProductInterfaceFactory $productInterfaceFactory;
    protected ProductRepositoryInterface $productRepository;
    protected State $appState;
    protected EavSetup $eavSetup;
    protected StoreManagerInterface $storeManager;
    protected CategoryLinkManagementInterface $categoryLink;

    public function __construct(
        ModuleDataSetupInterface $setup,
        ProductInterfaceFactory $productInterfaceFactory,
        ProductRepositoryInterface $productRepository,
        State $appState,
        StoreManagerInterface $storeManager,
        EavSetup $eavSetup,
        CategoryLinkManagementInterface $categoryLink
    ) {
        $this->appState = $appState;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->productRepository = $productRepository;
        $this->setup = $setup;
        $this->eavSetup = $eavSetup;
        $this->storeManager = $storeManager;
        $this->categoryLink = $categoryLink;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): void
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    public function execute(): void
    {
        $product = $this->productInterfaceFactory->create();

        // Check if the product already exists
        if ($product->getIdBySku('funky-sku')) {
            return;
        }

        // Get the attribute set ID for 'Default'
        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');

        // Set product data
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId($attributeSetId)
            ->setName('Tesla X')
            ->setSku('funny-sku')
            ->setUrlKey('the-url-should-be-here-somewhere')
            ->setPrice(9.99)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED);

        // Save the product
        $product = $this->productRepository->save($product);

        // Assign product to categories
        $this->categoryLink->assignProductToCategories($product->getSku(), [2]);
    }
}

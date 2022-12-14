<?php

declare (strict_types = 1);

namespace Verideal\VeridealNet\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreatePIDProductAttribute implements DataPatchInterface
{
    const PROD_ATTR_PID = 'pid';

    /**
     * ModuleDataSetupInterface
     *
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * EavSetupFactory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @return CreatePIDProductAttribute|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply() {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute('catalog_product', self::PROD_ATTR_PID, [
            'type' => 'text',
            'backend' => '',
            'frontend' => '',
            'label' => 'PID',
            'input' => 'text',
            'class' => '',
            'source' => '',
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => false,
            'unique' => false,
            'apply_to' => '',
        ]);
    }

    /**
     * @return array
     */
    public static function getDependencies(): array {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases(): array {
        return [];
    }
}

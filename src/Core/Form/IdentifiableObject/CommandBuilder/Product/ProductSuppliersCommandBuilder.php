<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Form\IdentifiableObject\CommandBuilder\Product;

use PrestaShop\PrestaShop\Core\Domain\Product\Supplier\Command\RemoveAllAssociatedProductSuppliersCommand;
use PrestaShop\PrestaShop\Core\Domain\Product\Supplier\Command\SetProductSuppliersCommand;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;

final class ProductSuppliersCommandBuilder implements ProductCommandBuilderInterface
{
    /**
     * {@inheritDoc}
     */
    public function buildCommand(ProductId $productId, array $formData): array
    {
        if (!isset($formData['suppliers'])) {
            return [];
        }

        $suppliers = $formData['suppliers'];

        if (isset($suppliers['supplier_references']) && empty($suppliers['supplier_references'])) {
            return [new RemoveAllAssociatedProductSuppliersCommand($productId->getValue())];
        }

        $productSuppliers = [];
        foreach ($suppliers['supplier_references'] as $supplierId => $supplierReferenceForm) {
            foreach ($supplierReferenceForm['product_supplier_collection'] as $productSupplierData) {
                $productSuppliers[] = $this->formatProductSupplier($productId->getValue(), $supplierId, $productSupplierData);
            }
        }

        return [
            new SetProductSuppliersCommand(
                $productId->getValue(),
                $productSuppliers,
                $suppliers['default_supplier_id']
            ),
        ];
    }

    /**
     * @param int $productId
     * @param int $supplierId
     * @param array $productSupplierData
     *
     * @return array<string, mixed>
     */
    private function formatProductSupplier(int $productId, int $supplierId, array $productSupplierData): array
    {
        return [
            'product_id' => $productId,
            'supplier_id' => $supplierId,
            'currency_id' => (int) $productSupplierData['currency_id'],
            'reference' => $productSupplierData['supplier_reference'],
            'price_tax_excluded' => (string) $productSupplierData['supplier_price_tax_excluded'],
            'combination_id' => (int) $productSupplierData['combination_id'],
            'product_supplier_id' => (int) $productSupplierData['product_supplier_id'],
        ];
    }
}

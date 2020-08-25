<?php
namespace Digiwallet\client\src\InvoiceLine;

use Digiwallet\client\src\InvoiceLine\InvoiceLineInterface as InvoiceLine;
/**
 * Interface FactoryInterface
 * @package Digiwallet\client\src\InvoiceLine
 */
interface FactoryInterface
{
    /**
     * @param string $productCode
     * @param string $productDescription
     * @param int $quantity
     * @param int $price
     * @param string $taxCategory
     * @return InvoiceLineInterface
     */
    public function create(
        string $productCode,
        string $productDescription,
        int $quantity,
        int $price,
        string $taxCategory
    ): InvoiceLine;
}

<?php

namespace Digiwallet\client\src\Response;

/**
 * Interface TransactionResponse
 * @package Digiwallet\client\src\Response
 */
interface CreateTransactionInterface
{
    /**
     * @return int
     */
    public function status(): int;

    /**
     * @return string
     */
    public function message(): string;

    /**
     * @return int
     */
    public function transactionId(): int;

    /**
     * @return string
     */
    public function launchUrl(): string;

    /**
     * @return string
     */
    public function response(): array;

    /**
     * @return string
     */
    public function transactionKey(): string;

}

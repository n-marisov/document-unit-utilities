<?php

namespace Maris\DocumentNumber\Utility\Model;

use Maris\DocumentNumber\Utility\CheckDigit\InnCheckDigit;
use Maris\Interfaces\Document\Model\InnNumberInterface;
use RuntimeException;

abstract class AbstractInnNumber implements InnNumberInterface
{
    /**
     * Вызывается в конструкторе для проверки валидности ИНН.
     * @param string $inn
     */
    protected function __construct( string $inn )
    {
        $check = new InnCheckDigit( $inn );

        if($check->invalidInput)
            throw new RuntimeException("Строка \"$inn\" не может быть ИНН.");

        if(!$check->valid)
            throw new RuntimeException("Не сошлась контрольная сумма.");
    }
}
<?php

namespace Maris\DocumentNumber\Utility\CheckDigit;

use RuntimeException;
use Throwable;

/***
 * Калькулятор для вычисления контрольной суммы ИНН.
 * @property-read string $inn Переданная строка.
 * @property-read array $check Массив с контрольными цифрами.
 * @property-read bool $invalidInput TRUE если переданная строка не корректна.
 * @property-read bool $valid TRUE если переданная строка корректна и сошлась контрольная сумма.
 */
class InnCheckDigit
{
    /***
     * Минимальный ряд для расчета контрольной суммы.
     */
    private const CHECK_ROW = [2, 4, 10, 3, 5, 9, 4, 6, 8];

    /***
     * Инн для расчета.
     * @var string
     */
    public readonly string $inn;

    /**
     * Массив с контрольными суммами.
     * @var array
     */
    public readonly array $check;

    /**
     * Является ли переданный ИНН валидным.
     * @var bool
     */
    public readonly bool $valid;

    /***
     * Указывает что переданная строка не корректна.
     * @var bool $invalidInput
     */
    public readonly bool $invalidInput;

    /**
     * Принимает ИНН без лишних символов.
     * @param string $inn
     */
    public function __construct(string $inn)
    {
        $this->inn = $inn;

        if(!ctype_digit($inn)){
            $this->invalidInput = true;
            $this->valid = false;
            $this->check = [];
            return;
        }

        $length = strlen($inn);

        if($length === 10){
            $this->check = [ $this->checkDigit10() ];
            $this->valid = $this->check[0] === intval( $inn[9] );
            $this->invalidInput = false;
            return;
        }

        if( $length === 12 ){
            $this->check = [ $this->checkDigit11(), $this->checkDigit12() ];
            $this->valid = $this->check[0] === intval( $this->inn[10] )
                && $this->check[1] === intval($this->inn[11]);
            $this->invalidInput = false;
            return;
        }


        $this->invalidInput = true;
        $this->valid = false;
        $this->check = [];
    }

    protected function checkDigit10():int
    {
        $sum = 0;
        foreach (self::CHECK_ROW as $i => $weight)
            $sum += $weight * $this->inn[$i];
        return $sum % 11 % 10;
    }
    protected function checkDigit11():int
    {
        $sum = 0;
        foreach ([7, ...self::CHECK_ROW] as $i => $weight)
        {
            $sum += $weight * $this->inn[$i];
        }
        return $sum % 11 % 10;
    }
    protected function checkDigit12():int
    {
        $sum = 0;
        foreach ([3, 7, ...self::CHECK_ROW] as $i => $weight)
        {
            $sum += $weight * $this->inn[$i];
        }
        return $sum % 11 % 10;
    }

}
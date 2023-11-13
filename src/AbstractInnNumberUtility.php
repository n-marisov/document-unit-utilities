<?php

namespace Maris\DocumentNumber\Utility;

use Maris\DocumentNumber\Utility\CheckDigit\InnCheckDigit;
use Maris\Interfaces\Document\Aggregate\InnNumberAggregateInterface;
use Maris\Interfaces\Document\AggregateNotNull\InnNumberAggregateNotNullInterface;
use Maris\Interfaces\Document\Model\InnNumberInterface;
use Maris\Interfaces\Document\Utility\InnNumberUtilityInterface;
use RuntimeException;
use Stringable;
use Throwable;

/***
 * Абстрактный класс для работы с ИНН.
 */
abstract class AbstractInnNumberUtility implements InnNumberUtilityInterface
{

    /**
     * Создает новый объект с инициализированным значением.
     * @param string $value
     * @return InnNumberInterface
     */
    abstract protected function newInstance( string $value ):InnNumberInterface;

    /**
     * Последняя ошибка.
     * @var Throwable|false
     */
    protected Throwable|false $lastError = false;

    /**
     * Возвращает последнюю ошибку.
     * @return false|Throwable
     */
    public function getLastError(): false|Throwable
    {
        return $this->lastError;
    }

    protected function newError( string $massage, int $code = 0, ?Throwable $previous = null ):Throwable
    {
        return new RuntimeException( $massage, $code, $previous );
    }

    /***
     * Устанавливает ошибку.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param mixed|null $return
     * @return bool|null
     */
    protected function initError( string $message, int $code = 0, ?Throwable $previous = null, mixed $return = null ): ?bool
    {
        $this->lastError = $this->newError( $message, $code, $previous );
        return $return;
    }

    /**
     * @inheritDoc
     */
    public function format(InnNumberInterface|InnNumberAggregateInterface $inn): string
    {
        return (string) is_a($inn,InnNumberAggregateInterface::class) ? $inn->getInn() : $inn ;
    }

    /**
     * @inheritDoc
     */
    public function valid(string|Stringable $number): bool
    {
        $this->lastError = false;
        $number = trim( (string) $number);

        $check = new InnCheckDigit($number);

        if( $check->invalidInput ){
            if(!ctype_digit($number))
                return $this->initError(
                    message: 'Найдены не числовые символы.',
                    code: 1,
                    return: false
                );

            elseif(!in_array( strlen($number),[10,12] ))
                return $this->initError(
                    message: 'Не соответствует длина.',
                    code: 2,
                    return: false
                );
        }

        if(!$check->valid)
            return $this->initError(
                message: 'Не сошлась контрольная сумма.',
                code: 3,
                return: false
            );

        return true;
    }

    /**
     * @inheritDoc
     */
    public function create(string|Stringable $number): ?InnNumberInterface
    {
        return $this->valid($number) ? $this->newInstance( trim( (string) $number) ) : null;
    }

    /**
     * @inheritDoc
     */
    public function parse( string|Stringable $parsed ): iterable
    {
        $result = [];
        preg_match_all("/\D(\d{10}|\d{12})\D/"," $parsed ",$matches );

        foreach ($matches[1] as $str)
            $result[] = $this->create($str);
        return array_filter( $result, fn($value) => !is_null( $value ) );
    }

    /**
     * @inheritDoc
     */
    public function isPerson(InnNumberAggregateNotNullInterface|InnNumberInterface $inn): bool
    {
        $inn = (string) is_a($inn,InnNumberAggregateNotNullInterface::class) ? $inn->getInn() : $inn;
        return strlen( $inn ) === 12;
    }

    /**
     * @inheritDoc
     */
    public function isLegal(InnNumberAggregateNotNullInterface|InnNumberInterface $inn): bool
    {
        $inn = (string) is_a($inn,InnNumberAggregateNotNullInterface::class) ? $inn->getInn() : $inn;
        return strlen( $inn ) === 10;
    }

    /**
     * @inheritDoc
     */
    public function isRussian(InnNumberAggregateNotNullInterface|InnNumberInterface $inn): bool
    {
        $inn = (string) is_a($inn,InnNumberAggregateNotNullInterface::class) ? $inn->getInn() : $inn;
        return !str_starts_with( $inn, "9909" );
    }

    /**
     * @inheritDoc
     */
    public function fnsCode(InnNumberAggregateNotNullInterface|InnNumberInterface $inn): string
    {
        $inn = (string) is_a($inn,InnNumberAggregateNotNullInterface::class) ? $inn->getInn() : $inn;
        return substr( $inn,0,4 );
    }

    /**
     * @inheritDoc
     */
    public function position( InnNumberAggregateNotNullInterface|InnNumberInterface $inn ): string
    {
        $inn = (string) is_a($inn,InnNumberAggregateNotNullInterface::class) ? $inn->getInn() : $inn;
        return substr( $inn,4,match (strlen($inn)){
            12 => 6,
            10 => 5
        });
    }
}
<?php declare(strict_types=1);

require_once 'Enum/Enum.php';

class TaskStatus extends Enum
{
    public const ACTIVE = 1;
    public const DONE = 2;

    private static $textValues = [
        self::ACTIVE => 'active',
        self::DONE => 'done',
    ];

    /**
     * TaskStatus constructor.
     * @param $value
     * @throws ReflectionException
     */
    public function __construct($value)
    {
        $enumValue = $value;
        if (is_string($value)) {
            $this->validateTextValue($value);
            foreach (self::$textValues as $mapKey => $mapValue) {
                if ($value === $mapValue) {
                    $enumValue = $mapKey;
                    break;
                }
            }
        }
        parent::__construct($enumValue);
    }

    /**
     * @return string
     */
    public function getTextValue(): string
    {
        return self::$textValues[$this->getValue()];
    }

    /**
     * @param string $value
     */
    private function validateTextValue(string $value):void
    {
        if (!in_array($value, self::$textValues, true)) {
            throw new LogicException(
                sprintf('Value %s is not allowed for %s', $value, static::class)
            );
        }
    }
}

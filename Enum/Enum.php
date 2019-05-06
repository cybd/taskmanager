<?php declare(strict_types=1);

class Enum
{
    private $value;
    private $key;

    /**
     * Enum constructor.
     * @param $value
     * @throws ReflectionException
     */
    public function __construct($value)
    {
        $this->value = $value;
        $this->validate();
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @throws ReflectionException
     */
    private function validate(): void
    {
        $constantArray = (new \ReflectionClass(static::class))->getConstants();
        $values = [];
        foreach ($constantArray as $key => $value) {
            if ($value === $this->value) {
                $this->key = $key;
            }
            if (in_array($value, $values, true)) {
                throw new LogicException(
                    sprintf('Enum %s cannot contain duplicate values %s => %s',
                        static::class,
                        $key,
                        $value
                    )
                );
            }
            $values[] = $value;
        }
    }
}

<?php

namespace lip\enum;

/**
 * 枚举基类。
 *
 * 创建一个枚举类，继承该类，使用类 const 作为枚举值。
 *
 * @author lip8up <lip8up@qq.com>
 * @license MIT
 * @example 详细说明 <https://github.com/lip8up/php-enum>
 */
abstract class Enum implements \JsonSerializable
{
    /**
     * 当前枚举的 key。
     *
     * @var string
     */
    protected string $key;

    /**
     * 当前枚举的 value。
     *
     * @var mixed 枚举值
     */
    protected $value;

    /**
     * 当前枚举的 label。
     *
     * @var string
     */
    protected string $label;

    /**
     * 存储每个枚举类的所有常量。
     *
     * @var array
     */
    protected static array $allConstants = [];

    /**
     * 存储枚举类的实例。
     *
     * @var array
     */
    protected static array $instances = [];

    /**
     * 构造函数。将构造函数设成 protected，预防直接 new。
     *
     * @param string $key 枚举 key
     * @param mixed $value 枚举 value
     * @param string $label 枚举 label
     */
    protected function __construct(string $key, $value, $label = '')
    {
        $this->key = $key;
        $this->value = $value;
        $this->label = $label;
    }

    /**
     * 获取枚举 key。
     *
     * @return string
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * 获取枚举 value。
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * 获取枚举 label。
     *
     * @return string
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * 枚举 value 的字符串形式。
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }

    /**
     * 获取全部常量列表，格式：`[ key1 => [value1, label1], ... ]`。
     *
     * @return array
     *
     * @example #
     * <code>
     * ```php
     * // 对于下面的 Some
     * class Some extends Enum
     * {
     *     private const One = [1, '一'];
     *     private const Two = [2, '二'];
     *     private const Three = [3, '三'];
     * }
     * // 调用 Some::allConstants()，结果为：
     * [
     *     'One' => [1, '一'],
     *     'Two' => [2, '二'],
     *     'Three' => [3, '三'],
     * ]
     * ```
     * </code>
     */
    public static function allConstants(): array
    {
        $class = static::class;
        if (!isset(self::$allConstants[$class])) {
            $rel = new \ReflectionClass($class);
            $list = $rel->getConstants();
            $allConstants = [];
            foreach ($list as $key => $value) {
                // 当不是 [value, label] 格式的数组时，重复 value 作为 label
                $allConstants[$key] = is_array($value) ? $value : [$value, $value];
            }
            self::$allConstants[$class] = $allConstants;
        }
        return self::$allConstants[$class];
    }

    /**
     * 获取全部 key 列表。
     *
     * @return array
     */
    public static function allKeys(): array
    {
        $constants = self::allConstants();
        $keys = array_keys($constants);
        return $keys;
    }

    /**
     * 获取全部 value 列表。
     *
     * @return array
     */
    public static function allValues(): array
    {
        return self::columnValues(0);
    }

    /**
     * 获取全部 label 列表。
     *
     * @return array
     */
    public static function allLabels(): array
    {
        return self::columnValues(1);
    }

    /**
     * 获取 $value 对应的 label 或 null，若不传参数或传 null，返回整个 map，例如：[1 => '一', 2 => '二', 3 => '三']。
     *
     * @param mixed $value 要转换的 value
     *
     * @return string|null|array
     */
    public static function valueToLabel($value = null)
    {
        $map = self::columnValues(1, 0);
        return $value == null ? $map : ($map[$value] ?? null);
    }

    /**
     * 获取 $label 对应的 value 或 null，若不传参数或传 null，返回整个 map，例如：['一' => 1, '二' => 2, '三' => 3]
     *
     * @param string $label 要转换的 label
     *
     * @return mixed|null|array
     */
    public static function labelToValue(string $label = null)
    {
        $map = self::columnValues(0, 1);
        return $label == null ? $map : ($map[$label] ?? null);
    }

    /**
     * allValues、allLabels、valueToLabel、labelToValue 等方法的公用方法。
     *
     * @param string|int|null $columnKey
     * @param string|int|null $indexKey
     * @return array
     */
    private static function columnValues($columnKey, $indexKey = null): array
    {
        $constants = self::allConstants();
        $pairs = array_values($constants);
        $result = array_column($pairs, $columnKey, $indexKey);
        return $result;
    }

    /**
     * 判断是否为合法的 key。
     *
     * @param string $key 要判断的 key
     * @return bool
     */
    public static function isValidKey(string $key): bool
    {
        return in_array($key, self::allKeys());
    }

    /**
     * 判断是否为合法的 value。
     *
     * @param mixed $value 要判断的 value
     * @return bool
     */
    public static function isValidValue($value): bool
    {
        return in_array($value, self::allValues());
    }

    /**
     * 判断是否为合法的 label。
     *
     * @param string $label 要判断的 label
     * @return bool
     */
    public static function isValidLabel(string $label): bool
    {
        return in_array($label, self::allLabels());
    }

    /**
     * 自定义 __callStatic，实现子类 const 值作为方法调用。
     *
     * @link https://www.php.net/manual/zh/language.oop5.overloading.php#object.callstatic
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $class = static::class;
        if (!isset(self::$instances[$class][$name])) {
            $allConstants = self::allConstants();
            if (!isset($allConstants[$name]) && !\array_key_exists($name, $allConstants)) {
                $message = "No static method or enum constant '$name' in class " . $class;
                throw new \BadMethodCallException($message);
            }
            [$value, $label] = $allConstants[$name];
            return self::$instances[$class][$name] = new static($name, $value, $label);
        }
        return clone self::$instances[$class][$name];
    }

    /**
     * 当被 json_encode 时的实际对象。
     *
     * @return mixed
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @example #
     * <code>
     * ```php
     * // 对于下面的 Some
     * class Some extends Enum
     * {
     *     private const One = [1, '一'];
     *     private const Two = [2, '二'];
     *     private const Three = [3, '三'];
     * }
     * // 调用 json_encode(Some::One())，返回的字符串为：
     * {"key":"One","value":1,"label":"\u4e00"}
     * ```
     * </code>
     */
    public function jsonSerialize()
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'label' => $this->label
        ];
    }
}

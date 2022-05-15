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
     * 构造函数。将构造函数设成 private，预防直接 new。
     *
     * @param string $key 枚举 key
     * @param mixed $value 枚举 value
     * @param string $label 枚举 label
     */
    private function __construct(string $key, $value, $label = '')
    {
        $this->key = $key;
        $this->value = $value;
        $this->label = $label;
    }

    /**
     * 从 key 构建 Enum 实例，若不存在，则返回 null。
     *
     * @param string $key 键
     * @return static|null
     */
    public static function fromKey(string $key)
    {
        $constants = self::allConstants();
        if (isset($constants[$key])) {
            // 不要使用 new static，以免产生的实例与通过 SomeEnum::SomeKey() 方式产生的实例不同。
            return static::$key();
        }
        return null;
    }

    /**
     * 从 value 构建 Enum 实例，若不存在，则返回 null。
     *
     * @param mixed $value 值
     * @return static|null
     */
    public static function fromValue($value)
    {
        $constants = self::allConstants(true);
        if (isset($constants[$value])) {
            [$key] = $constants[$value];
            // 不要使用 new static，以免产生的实例与通过 SomeEnum::SomeKey() 方式产生的实例不同。
            return static::$key();
        }
        return null;
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
     * @param string $valueAsKey 使用 value 作为主键，默认使用 key 作为主键。
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
     * // 调用 Some::allConstants(true)，结果为：
     * [
     *     1 => ['One', '一'],
     *     2 => ['Two', '二'],
     *     3 => ['Three', '三'],
     * ]
     * ```
     * </code>
     */
    public static function allConstants($valueAsKey = false): array
    {
        $class = static::class;
        $cacheKey = $class . ($valueAsKey ? '#value' : '#key');
        if (!isset(self::$allConstants[$cacheKey])) {
            $rel = new \ReflectionClass($class);
            $list = $rel->getConstants();
            $allConstants = [];
            foreach ($list as $key => $it) {
                // 当不是 [value, label] 格式的数组时，重复 value 作为 label
                $item = is_array($it) ? $it : [$it, $it];
                if ($valueAsKey) {
                    [$value, $label] = $item;
                    $allConstants[$value] = [$key, $label];
                } else {
                    $allConstants[$key] = $item;
                }
            }
            self::$allConstants[$cacheKey] = $allConstants;
        }
        return self::$allConstants[$cacheKey];
    }

    /**
     * 作为列表返回，以便前端使用，返回值用类 js 格式表示：`[ { key: key1, value: value1, label: label1 }, ... ]`。
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
     * // 调用 Some::asList()，结果为：
     * [
     *     [ 'key' => 'One', 'value' => 1, 'label' => '一' ],
     *     [ 'key' => 'Two', 'value' => 2, 'label' => '二' ],
     *     [ 'key' => 'Three', 'value' => 3, 'label' => '三' ],
     * ]
     * ```
     * </code>
     */
    public static function asList(): array
    {
        $all = self::allConstants();
        $list = [];
        foreach ($all as $key => [$value, $label]) {
            array_push($list, ['key' => $key, 'value' => $value, 'label' => $label]);
        }
        return $list;
    }

    /**
     * 将实例的一部分，作为列表返回，返回值用类 js 格式表示：`{ value, label }`。注意，这是实例方法。
     *
     * @param array $parts 默认 `['value', 'label']`，即只返回 value、label
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
     * // 调用 Some::One()->parts()，结果为：['value' => 1, 'label' => '一']
     * // 调用 Some::Two()->parts(['key', 'value', 'label'])，结果为：['key' => 'Two', 'value' => 2, 'label' => '二']
     * ```
     * </code>
     */
    public function parts($parts = ['value', 'label']): array
    {
        $result = [];
        foreach ($parts as $key) {
            $result[$key] = $this->$key;
        }
        return $result;
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
     * 获取 $value 对应的 label，如不存在，返回 $default，若不传参数，返回整个 map，例如：[1 => '一', 2 => '二', 3 => '三']。
     *
     * @param mixed $value 要转换的 value
     * @param mixed $default 默认的 label
     *
     * @return string|null|array
     */
    public static function valueToLabel($value = null, $default = null)
    {
        $map = self::columnValues(1, 0);
        return func_num_args() == 0 ? $map : ($value !== null ? ($map[$value] ?? $default) : $default);
    }

    /**
     * 将一批 value 转换成对应的 label，若不存在，使用 $default 取代。
     *
     * @param array $values 一批值
     * @param mixed $default 默认的 value
     * @return array
     */
    public static function valuesToLabels(array $values, $default = null): array
    {
        $labels = [];
        foreach ($values as $value) {
            $labels[] = static::valueToLabel($value, $default);
        }
        return $labels;
    }

    /**
     * 获取 $label 对应的 value，如不存在，返回 $default，若不传参数，返回整个 map，例如：['一' => 1, '二' => 2, '三' => 3]
     *
     * @param string $label 要转换的 label
     * @param mixed $default 默认的 label
     *
     * @return mixed|null|array
     */
    public static function labelToValue(string $label = null, $default = null)
    {
        $map = self::columnValues(0, 1);
        return func_num_args() == 0 ? $map : ($label !== null ? ($map[$label] ?? $default) : $default);
    }

    /**
     * 将一批 label 转换成对应的 value，若不存在，使用 $default 取代。
     *
     * @param array $labels
     * @param mixed $default 默认的 label
     * @return array
     */
    public static function labelsToValues(array $labels, $default = null): array
    {
        $values = [];
        foreach ($labels as $label) {
            $values[] = static::labelToValue($label, $default);
        }
        return $values;
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
     * 自定义 __call，实现子类 isXxx() 调用，Xxx 为枚举 key。
     *
     * @link https://www.php.net/manual/zh/language.oop5.overloading.php#object.call
     */
    public function __call(string $name, array $arguments)
    {
        // 支持 isXxx() 调用
        if (substr($name, 0, 2) === 'is' && count($arguments) == 0) {
            $key = substr($name, 2);
            $enum = self::fromKey($key);
            if ($enum !== null) {
                return $enum->value() === $this->value;
            } else {
                static::throwBadMethod($key);
            }
        }
    }

    /**
     * 自定义 __callStatic，实现子类 Key 值作为方法调用。
     *
     * 2022-03-13 更新：增加 isXxx($value) 调用，Xxx 为枚举 key。
     *
     * @link https://www.php.net/manual/zh/language.oop5.overloading.php#object.callstatic
     */
    public static function __callStatic(string $name, array $arguments)
    {
        // 支持 isXxx($value) 调用
        if (substr($name, 0, 2) === 'is' && count($arguments) == 1) {
            $key = substr($name, 2);
            $enum = self::fromKey($key);
            if ($enum !== null) {
                $value = $arguments[0];
                return $enum->value() === $value;
            } else {
                static::throwBadMethod($key);
            }
        }

        $class = static::class;
        if (!isset(self::$instances[$class][$name])) {
            $allConstants = self::allConstants();
            if (!isset($allConstants[$name]) && !\array_key_exists($name, $allConstants)) {
                static::throwBadMethod($name);
            }
            [$value, $label] = $allConstants[$name];
            self::$instances[$class][$name] = new static($name, $value, $label);
        }
        return self::$instances[$class][$name];
    }

    private static function throwBadMethod(string $name)
    {
        $message = "No enum constant '$name' in class " . static::class;
        throw new \BadMethodCallException($message);
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

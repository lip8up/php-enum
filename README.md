# php-enum

PHP 枚举基类，使用`类常量`作为枚举值，每一项均含有 `key`、`value` 与 `label`。

## 安装

```bash
composer require lip/php-enum
```

## 概念

一个枚举，通常包含：`key`、`value`，如 `typescript` 中的下列枚举：

```ts
enum Direction {
  Up = 1,
  Down = 2,
  Left = 3,
  Right = 4
}
```

其中 `Up`、`Down`、`Left`、`Right` 为 `key`，`1`、`2`、`3`、`4` 为 `value`。

一些常见的情形，还需要一个文字描述，特别是中文环境，将上面代码改造一下：

```ts
enum Direction {
  Up = [1, '上'],
  Down = [2, '下'],
  Left = [3, '左'],
  Right = [4, '右']
}
```

本库将这种文字描述，定义为枚举的 `label`，即上面的`上`、`下`、`左`、`右`。

由于 PHP 本身不支持枚举，这里撰写一个类 `lip\enum\Enum`，继承该类，定义类常量，作为枚举值。

## 使用

### 标准用法（推荐）

每一个类常量均为一个`数组`，其中，第一项为 `value`，第二项为 `label`，顺序不能变。

下例中，One、Two、Three 为 `key`，1、2、3 是 `value`，一、二、三是 `label`。

```php
<?php
use lip\enum\Enum;

/**
 * 一个很有意思的枚举。
 * @method static self One()    One的函数说明
 * @method static self Two()    Two的函数说明
 * @method static self Three()  Three的函数说明
 */
final class Some extends Enum
{
    private const One = [1, '一'];
    private const Two = [2, '二'];
    private const Three = [3, '三'];
}
```

定义一个使用 `Some` 枚举作为参数的函数，使用`强类型`进行标注：

```php
function useSome(Some $some) {
    // ...
}
```

此时，如果用别的值，传递给上述函数 `useSome`，则会报错，比如传递 `Some::One`，由于它的值为 `[1, '一']`，类型为 `array`，而非 `Some`，故而会报错。

`Some` 继承自 `lip\enum\Enum` 类，该类赋予 `Some` 一个很重要的能力：

> 将类常量定义，转换成对应的静态方法，调用此方法，会获得对应的 `Some` 类实例。

例如 `Some::One()` 对应的就是含有常量 `Some::One` 值的 `Some` 类实例，可以无障碍地传给 `useSome`：

```php
useSome(Some::One());
```

注意 `Some::One()` 是一个静态方法，它通过类 `lip\enum\Enum` 里的魔术方法 [__callStatic](https://www.php.net/manual/zh/language.oop5.overloading.php#object.callstatic) 添加，有多少个类常量，就有多少个对应名字的静态方法。

那么 `useSome` 里如何获取枚举相应的 `key`、`value`、`label` 呢？调用相应的`实例方法`即可，例如：

```php
function useSome(Some $some)
{
    $key = $some->key();
    $value = $some->value();
    $label => $some->label();
    // $some 可以直接参与字符串连接运算，相当于 $some->value() 参与了运算，例如：
    echo 'some value is: ' . $some;
    // 相当于
    echo 'some value is: ' . $some->value();
}
```

更多 `lip\enum\Enum` 类实例 & 静态方法参见下面的[`方法列表`](#方法列表)部分。

> 强烈建议将类常量定义为 `private`，以免被访问到，始终使用 `Some::ConstName()` 的形式获取相应的枚举实例。

### 简洁用法（不需要或者不关注 label 时使用）

此用法不关注 `label`，每个类常量均为单一值，此时 `value`、`label` 相同，均为该常量值，举例来说：

```php
/**
 * 另一个很有意思的枚举。
 * @method static self Haha() 哈哈大笑
 * @method static self Bibi() You can you up, no can no bibi
 */
final class Other extends Enum
{
    private const Haha = 'hh';
    private const Bibi = 'bb';
}

// Other 实例的 value 与 label 是相同的，都是 hh
Other::Haha()->value() == Other::Haha()->label()   // true
```

### 语法提示

由于 `lip\enum\Enum` 类使用了魔术方法 [`__callStatic`](https://www.php.net/manual/zh/language.oop5.overloading.php#object.callstatic)，导致在调用诸如 `Some::One()` 时，其结果不能正确地，被开发工具反射正确的类型，为使开发工具能正确地识别类型，可在枚举类的注释中，为每一个类常量增加一行 `@method static self ConstName() 函数说明`，具体参见上面的例子。

### 无法被实例化

基类 `lip\enum\Enum` 将构造函数设为 `protected`，禁止通过构造函数实例化枚举类，请始终使用 `Some::ConstName()` 的形式。

## 方法列表

### 实例方法

方法名 | 说明
--- | ---
key() | 获取枚举 key。
value() | 获取枚举 value。
label() | 获取枚举 label。
__toString() | 无法被主动调用，在对实例本身进行字符串连接时，会被调用，此处实现为：`(string)$this->value`

### 静态方法

子类定义的类常量名，不要与👇的静态方法同名。

方法名 | 说明
--- | ---
allConstants() | 获取全部常量列表，格式：`[ key1 => [value1, label1], ... ]`，例如：`[ 'One' => [1, '一'], 'Two' => [2, '二'], 'Three' => [3, '三'] ]`。
allKeys() | 获取全部 key 列表。
allValues() | 获取全部 value 列表。
allLabels() | 获取全部 label 列表。
valueToLabel(mixed $value = null) | 获取 $value 对应的 label 或 null。若不传参数或传 null，返回整个 `map`，例如：`[1 => '一', 2 => '二', 3 => '三']`。
labelToValue(string $label = null) | 获取 $label 对应的 value 或 null。若不传参数或传 null，返回整个 `map`。例如：`['一' => 1, '二' => 2, '三' => 3]`。
isValidKey(string $key) | 判断是否为合法的 key。
isValidValue(mixed $value) | 判断是否为合法的 value。
isValidLabel(string $label) | 判断是否为合法的 label。

## 总是返回新实例

相同的类常量值，每次调用静态方法，均返回一个新的类实例，即：

```php
Some::One() !== Some::One()  // true
Some::One() === Some::One()  // false
```

## JsonSerializable

`lip\enum\Enum` 类实现了接口 `JsonSerializable`，例如：

```php
json_encode(Some::One()); // '{"key":"One","value":1,"label":"\u4e00"}'
```

## 单元测试

运行 `./runtest.sh` 执行单元测试，已 **100%** 测试通过，请放心使用。

## 结语

有任何建议或改进，请发 [issue](https://github.com/lip8up/php-enum/issues/new)。

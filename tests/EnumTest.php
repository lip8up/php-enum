<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use lip\enum\Enum;

/**
 * 一个很有意思的枚举。
 * @method static self one()
 * @method static self two()
 * @method static self three()
 */
final class Some extends Enum
{
    const one = [1, '一'];
    const two = [2, '二'];
    const three = [3, '三'];
}

/**
 * 另一个很有意思的枚举。
 * @method static self haha()
 * @method static self bibi()
 */
final class Other extends Enum
{
    const haha = 'hh';
    const bibi = 'bb';
}

final class EnumTest extends TestCase
{
    public function testInstanceOf()
    {
        $this->assertInstanceOf(Some::class, Some::one());
        $this->assertInstanceOf(Other::class, Other::haha());
    }

    public function testConstructorProtectedSome()
    {
        $this->expectError();
        new Some('three', 3);
    }

    public function testConstructorProtectedOther()
    {
        $this->expectError();
        new Other('bibi', 'bb');
    }

    public function testBadStaticCallSome()
    {
        $this->expectException(\BadMethodCallException::class);
        Some::badCall();
    }

    public function testBadStaticCallOther()
    {
        $this->expectException(\BadMethodCallException::class);
        Other::badCall();
    }

    public function testClone()
    {
        $this->assertNotSame(Some::one(), Some::one());
        $this->assertNotSame(Other::haha(), Other::haha());
    }

    public function testKey()
    {
        $this->assertSame('one', Some::one()->key());
        $this->assertSame('two', Some::two()->key());
        $this->assertSame('three', Some::three()->key());
        $this->assertSame('haha', Other::haha()->key());
        $this->assertSame('bibi', Other::bibi()->key());
    }

    public function testValue()
    {
        $this->assertSame(1, Some::one()->value());
        $this->assertSame(2, Some::two()->value());
        $this->assertSame(3, Some::three()->value());
        $this->assertSame('hh', Other::haha()->value());
        $this->assertSame('bb', Other::bibi()->value());
    }

    public function testLabel()
    {
        $this->assertSame('一', Some::one()->label());
        $this->assertSame('二', Some::two()->label());
        $this->assertSame('三', Some::three()->label());
        $this->assertSame('hh', Other::haha()->label());
        $this->assertSame('bb', Other::bibi()->label());
    }

    public function testToString()
    {
        $this->assertSame('1--', Some::one() . '--');
        $this->assertSame('2--', Some::two() . '--');
        $this->assertSame('3--', Some::three() . '--');
        $this->assertSame('hh--', Other::haha() . '--');
        $this->assertSame('bb--', Other::bibi() . '--');
    }

    public function testAllConstants()
    {
        $this->assertEquals(Some::allConstants(), [
            'one' => [1, '一'],
            'two' => [2, '二'],
            'three' => [3, '三'],
        ]);
        $this->assertEquals(Other::allConstants(), [
            'haha' => ['hh', 'hh'],
            'bibi' => ['bb', 'bb'],
        ]);
    }

    public function testAllKeys()
    {
        $this->assertEquals(Some::allKeys(), ['one', 'two', 'three']);
        $this->assertEquals(Other::allKeys(), ['haha', 'bibi']);
    }

    public function testAllValues()
    {
        $this->assertEquals(Some::allValues(), [1, 2, 3]);
        $this->assertEquals(Other::allValues(), ['hh', 'bb']);
    }

    public function testAllLabels()
    {
        $this->assertEquals(Some::allLabels(), ['一', '二', '三']);
        $this->assertEquals(Other::allLabels(), ['hh', 'bb']);
    }

    public function testValueToLabel()
    {
        $this->assertEquals(Some::valueToLabel(), [1 => '一', 2 => '二', 3 => '三']);
        $this->assertEquals(Some::valueToLabel(null), [1 => '一', 2 => '二', 3 => '三']);
        $this->assertEquals(Some::valueToLabel(1), '一');
        $this->assertEquals(Some::valueToLabel(2), '二');
        $this->assertEquals(Some::valueToLabel(3), '三');
        $this->assertEquals(Some::valueToLabel(6), null);

        $this->assertEquals(Other::valueToLabel(), ['hh' => 'hh', 'bb' => 'bb']);
        $this->assertEquals(Other::valueToLabel(null), ['hh' => 'hh', 'bb' => 'bb']);
        $this->assertEquals(Other::valueToLabel('hh'), 'hh');
        $this->assertEquals(Other::valueToLabel('bb'), 'bb');
    }

    public function testLabelToValue()
    {
        $this->assertEquals(Some::labelToValue(), ['一' => 1, '二' => 2, '三' => 3]);
        $this->assertEquals(Some::labelToValue(null), ['一' => 1, '二' => 2, '三' => 3]);
        $this->assertEquals(Some::labelToValue('一'), 1);
        $this->assertEquals(Some::labelToValue('二'), 2);
        $this->assertEquals(Some::labelToValue('三'), 3);
        $this->assertEquals(Some::labelToValue('六'), null);

        $this->assertEquals(Other::labelToValue(), ['hh' => 'hh', 'bb' => 'bb']);
        $this->assertEquals(Other::labelToValue(null), ['hh' => 'hh', 'bb' => 'bb']);
        $this->assertEquals(Other::labelToValue('hh'), 'hh');
        $this->assertEquals(Other::labelToValue('bb'), 'bb');
        $this->assertEquals(Other::labelToValue('xxx'), null);
    }

    public function testIsValidKey()
    {
        $this->assertSame(Some::isValidKey('one'), true);
        $this->assertSame(Some::isValidKey('onex'), false);
        $this->assertSame(Other::isValidKey('haha'), true);
        $this->assertSame(Other::isValidKey('bb'), false);
    }

    public function testIsValidValue()
    {
        $this->assertSame(Some::isValidValue(1), true);
        $this->assertSame(Some::isValidValue(8), false);
        $this->assertSame(Other::isValidValue('hh'), true);
        $this->assertSame(Other::isValidValue('bi'), false);
    }

    public function testIsValidLabel()
    {
        $this->assertSame(Some::isValidLabel('一'), true);
        $this->assertSame(Some::isValidLabel('六'), false);
        $this->assertSame(Other::isValidLabel('hh'), true);
        $this->assertSame(Other::isValidLabel('bi'), false);
    }

    public function testJsonEncode()
    {
        $this->assertSame(json_encode(Some::one()), '{"key":"one","value":1,"label":"\u4e00"}');
        $this->assertSame(json_encode(Other::haha()), '{"key":"haha","value":"hh","label":"hh"}');
    }
}

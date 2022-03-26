<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use lip\enum\Enum;

/**
 * 一个很有意思的枚举。
 * @method static self One() 这是一个描述
 * @method static self Two() 这是另一个描述
 * @method static self Three() 这是第三个描述
 */
final class Some extends Enum
{
    private const One = [1, '一'];
    private const Two = [2, '二'];
    private const Three = [3, '三'];
}

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

final class EnumTest extends TestCase
{
    public function testInstanceOf()
    {
        $this->assertInstanceOf(Some::class, Some::One());
        $this->assertInstanceOf(Other::class, Other::Haha());
    }

    public function testConstructorPrivateSome()
    {
        $this->expectError();
        new Some('Three', 3);
    }

    public function testConstructorPrivateOther()
    {
        $this->expectError();
        new Other('Bibi', 'bb');
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

    public function testFromKey()
    {
        $some = Some::fromKey('One');
        $this->assertNotNull($some);
        $this->assertEquals('One', $some->key());
        $this->assertEquals(1, $some->value());

        $some = Some::fromKey('OneXX00');
        $this->assertNull($some);
    }

    public function testFromValue()
    {
        $one = Some::fromValue(1);
        $this->assertNotNull($one);
        $this->assertEquals('One', $one->key());
        $this->assertEquals(1, $one->value());

        $some = Some::fromValue(888);
        $this->assertNull($some);
    }

    public function testSame()
    {
        $this->assertSame(Some::One(), Some::One());
        $this->assertSame(Other::Haha(), Other::Haha());
        $one = Some::One();
        switch ($one) {
            case Some::One():
                $this->assertTrue(true);
                break;
            case Some::Two():
                $this->assertTrue(false);
                break;
            case Some::Three():
                $this->assertTrue(false);
                break;
            default:
                $this->assertTrue(false);
                break;
        }
    }

    public function testSameFromEqual()
    {
        $this->assertSame(Some::One(), Some::fromValue(1));
        $this->assertNotSame(Some::One(), Some::fromValue(2));
        $this->assertSame(Other::Haha(), Other::fromKey('Haha'));
        $this->assertNotSame(Other::Haha(), Other::fromKey('Bibi'));
    }

    public function testEqual()
    {
        $this->assertEquals(Some::One(), Some::fromValue(1));
        $this->assertTrue(Some::One() == Some::fromValue(1));
        $this->assertNotEquals(Some::One(), Some::fromValue(2));
        $this->assertFalse(Some::One() == Some::fromValue(2));
        $this->assertEquals(Other::Haha(), Other::fromKey('Haha'));
        $this->assertTrue(Other::Haha() == Other::fromKey('Haha'));
        $this->assertNotEquals(Other::Haha(), Other::fromKey('Bibi'));
        $this->assertFalse(Other::Haha() == Other::fromKey('Bibi'));
        $one = Some::fromValue(1);
        switch ($one) {
            case Some::One():
                $this->assertTrue(true);
                break;
            case Some::Two():
                $this->assertTrue(false);
                break;
            case Some::Three():
                $this->assertTrue(false);
                break;
            default:
                $this->assertTrue(false);
                break;
        }
    }

    public function testKey()
    {
        $this->assertSame('One', Some::One()->key());
        $this->assertSame('Two', Some::Two()->key());
        $this->assertSame('Three', Some::Three()->key());
        $this->assertSame('Haha', Other::Haha()->key());
        $this->assertSame('Bibi', Other::Bibi()->key());
    }

    public function testValue()
    {
        $this->assertSame(1, Some::One()->value());
        $this->assertSame(2, Some::Two()->value());
        $this->assertSame(3, Some::Three()->value());
        $this->assertSame('hh', Other::Haha()->value());
        $this->assertSame('bb', Other::Bibi()->value());
    }

    public function testLabel()
    {
        $this->assertSame('一', Some::One()->label());
        $this->assertSame('二', Some::Two()->label());
        $this->assertSame('三', Some::Three()->label());
        $this->assertSame('hh', Other::Haha()->label());
        $this->assertSame('bb', Other::Bibi()->label());
    }

    public function testToString()
    {
        $this->assertSame('1--', Some::One() . '--');
        $this->assertSame('2--', Some::Two() . '--');
        $this->assertSame('3--', Some::Three() . '--');
        $this->assertSame('hh--', Other::Haha() . '--');
        $this->assertSame('bb--', Other::Bibi() . '--');
    }

    public function testAllConstants()
    {
        $this->assertEquals(Some::allConstants(), [
            'One' => [1, '一'],
            'Two' => [2, '二'],
            'Three' => [3, '三'],
        ]);
        $this->assertEquals(Some::allConstants(true), [
            1 => ['One', '一'],
            2 => ['Two', '二'],
            3 => ['Three', '三'],
        ]);
        $this->assertEquals(Other::allConstants(), [
            'Haha' => ['hh', 'hh'],
            'Bibi' => ['bb', 'bb'],
        ]);
    }

    public function testAsList()
    {
        $this->assertEquals(Some::asList(), [
            [ 'key' => 'One', 'value' => 1, 'label' => '一' ],
            [ 'key' => 'Two', 'value' => 2, 'label' => '二' ],
            [ 'key' => 'Three', 'value' => 3, 'label' => '三' ],
        ]);
        $this->assertEquals(Other::asList(), [
            [ 'key' => 'Haha', 'value' => 'hh', 'label' => 'hh' ],
            [ 'key' => 'Bibi', 'value' => 'bb', 'label' => 'bb' ],
        ]);
    }

    public function testAllKeys()
    {
        $this->assertEquals(Some::allKeys(), ['One', 'Two', 'Three']);
        $this->assertEquals(Other::allKeys(), ['Haha', 'Bibi']);
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
        $this->assertEquals(Some::valueToLabel(null), null);
        $this->assertEquals(Some::valueToLabel(1), '一');
        $this->assertEquals(Some::valueToLabel(2), '二');
        $this->assertEquals(Some::valueToLabel(3), '三');
        $this->assertEquals(Some::valueToLabel(6), null);
        $this->assertEquals(Some::valueToLabel(6, '-'), '-');

        $this->assertEquals(Other::valueToLabel(), ['hh' => 'hh', 'bb' => 'bb']);
        $this->assertEquals(Other::valueToLabel(null), null);
        $this->assertEquals(Other::valueToLabel('hh'), 'hh');
        $this->assertEquals(Other::valueToLabel('bb'), 'bb');
    }

    public function testLabelToValue()
    {
        $this->assertEquals(Some::labelToValue(), ['一' => 1, '二' => 2, '三' => 3]);
        $this->assertEquals(Some::labelToValue(null), null);
        $this->assertEquals(Some::labelToValue('一'), 1);
        $this->assertEquals(Some::labelToValue('二'), 2);
        $this->assertEquals(Some::labelToValue('三'), 3);
        $this->assertEquals(Some::labelToValue('六'), null);
        $this->assertEquals(Some::labelToValue('六', '-'), '-');

        $this->assertEquals(Other::labelToValue(), ['hh' => 'hh', 'bb' => 'bb']);
        $this->assertEquals(Other::labelToValue(null), null);
        $this->assertEquals(Other::labelToValue('hh'), 'hh');
        $this->assertEquals(Other::labelToValue('bb'), 'bb');
        $this->assertEquals(Other::labelToValue('xxx'), null);
    }

    public function testIsValidKey()
    {
        $this->assertSame(Some::isValidKey('One'), true);
        $this->assertSame(Some::isValidKey('Onex'), false);
        $this->assertSame(Other::isValidKey('Haha'), true);
        $this->assertSame(Other::isValidKey('Bb'), false);
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

    public function testIsXxxStaticCall()
    {
        $this->assertTrue(Some::isOne(1));
        $this->assertFalse(Some::isOne('1'));
        $this->assertTrue(Some::isTwo(2));
        $this->assertFalse(Some::isTwo(1));
        $this->expectException(\BadMethodCallException::class);
        $this->assertFalse(Some::isXxx(1));
    }

    public function testIsXxxCall()
    {
        $this->assertTrue(Some::One()->isOne());
        $this->assertTrue(Some::Two()->isTwo());
        $this->assertFalse(Some::Two()->isOne());
        $this->expectException(\BadMethodCallException::class);
        $this->assertFalse(Some::One()->isXxx());
    }

    public function testJsonEncode()
    {
        $this->assertSame(json_encode(Some::One()), '{"key":"One","value":1,"label":"\u4e00"}');
        $this->assertSame(json_encode(Other::Haha()), '{"key":"Haha","value":"hh","label":"hh"}');
    }
}

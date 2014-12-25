<?php

class PrototypeTest extends PHPUnit_Framework_TestCase
{
    public function testExtend()
    {
        $tester = $this;

        $o = new Rde\Prototype;
        $o['sayHello'] = function($o){
            return "Hello";
        };
        $o['sum'] = function($o, $a, $b) use($tester) {
            $tester->assertTrue(isset($o['sum']), '確認注入程序');
            return $a + $b;
        };

        $this->assertFalse(method_exists($o, 'sayHello'), '測試method_exists');
        $this->assertTrue(is_callable(array($o, 'sayHelloxx')), '測試is_callable sayHelloxx(不存在)');
        $this->assertFalse($o->hasDriver('sayHelloxx'), '測試hasDriver(sayHelloxx)');
        $this->assertTrue(is_callable(array($o, 'sayHello')), '測試is_callable');
        $this->assertTrue($o->hasDriver('sayHello'), '測試hasDriver(sayHello)');

        $this->assertEquals(
            'Hello',
            $o->sayHello(),
            '檢查建構注入');

        $o->extend('sayHello', function($o, $name){
                return "hello {$name}";
            });

        $this->assertEquals(
            'hello abc',
            $o->sayHello('abc'),
            '檢查動態注入覆寫');

        $this->assertEquals(
            9,
            $o->sum(1, 8),
            '檢查注入驅動處理');
    }

    public function testArrayAccess()
    {
        $tester = $this;
        $o = new Rde\Prototype();

        $o['verify'] = function($o, array $balls) use($tester) {

            $tester->assertInstanceOf('\\Rde\\Prototype', $o, '檢查容器');

            return 5 == count(array_unique($balls));
        };

        $this->assertTrue(isset($o['verify']), '檢查驅動');

        $this->assertTrue(
            $o->verify(array(1,2,3,4,5)),
            '檢查注入驗證回傳');
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessageRegExp /沒有安裝\[\w+\]處理驅動/
     */
    public function testException()
    {
        $o = new Rde\Prototype();

        $o->getColde();
    }

    public function testToString()
    {
        $o = new Rde\Prototype();

        $this->assertEquals('['.get_class($o).']', (string) $o, '檢查預設轉型(string)');

        $str = 'hello world';
        $o->extend('__toString', function() use($str){
            return $str;
        });

        $this->assertEquals($str, (string) $o, '檢查自訂轉型(string)');
    }
}

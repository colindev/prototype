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

    public function testEventWeight()
    {
        $tester = $this;
        $o = new Rde\Prototype();
        $event_cnt = 0;

        // 期望順序: 2
        $o->on('test.before', function($payload) use($tester, &$event_cnt){
                ++$event_cnt;
                $tester->assertEquals(array(99), $payload, '檢查參數傳遞');
                $tester->assertEquals(2, $event_cnt, '檢查呼叫順序');
                return 7;
            }, 7);

        // 期望順序: 3
        $o->on('test.before', function($payload) use($tester, &$event_cnt){
                ++$event_cnt;
                $tester->assertEquals(3, $event_cnt, '檢查呼叫順序');
            }, 7);

        // 期望順序: 1
        $o->on('test.before', function($payload) use($tester, &$event_cnt){
                ++$event_cnt;
                $tester->assertEquals(1, $event_cnt, '檢查呼叫順序');
            }, 9);

        $o->extend('test', function($self, $arg){});

        $o->test(99);

        $this->assertEquals(3, $event_cnt, '檢查事件是否全執行');
    }
}

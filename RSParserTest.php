<?php

/**
 * Created by PhpStorm.
 * User: karboom
 * Date: 16-3-26
 * Time: 下午2:14
 */
namespace Karboom;

class RestfulParserTest extends \PHPUnit_Framework_TestCase
{
    public $rsparser;

    public function setUp() {
        $this->rsparser = new RestfulParser(30, array('/\d+/'));
    }

    public function testResult() {
        $url = '/www.test.com/student/1/pet/2/?bet_age=20,30&name=123&exc_name=B&inc_height=12,13';

        $result = $this->rsparser->parse($url, array(), array());

        $this->assertEquals([20, 30], $result['filters'][0]['value']);
    }
}

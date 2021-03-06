<?php

/**
 * Test class for NKAuthentication.
 * Generated by PHPUnit on 2011-12-07 at 13:53:36.
 */
class NKAuthenticationTest extends PHPUnit_Framework_TestCase
{
  /**
   * @var NKAuthentication
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    $this->object = $this->getMock('NKAuthentication', array('getToken', 'tokenAvailable'));
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {

  }

  /**
   * @expectedException NKConfigException
   */
  public function testSetConfigFail()
  {
    $this->object->setConfig(false);
  }

  public function testSetConfigArray()
  {
    $result = $this->object->setConfig(array('secret' => 'some_secret', 'key' => 'some_key'));

    $this->assertInstanceOf('NKAuthentication', $result);
    $this->assertInstanceOf('NKConfig', $this->object->getConfig());
    $this->assertSame('some_secret', $this->object->getConfig()->secret);
    $this->assertSame('some_key', $this->object->getConfig()->key);
  }

  public function testSetConfigObject()
  {
    $conf = new NKConfig();
    $conf->secret = 'some_secret';
    $conf->key = 'some_key';

    $result = $this->object->setConfig($conf);

    $this->assertInstanceOf('NKAuthentication', $result);
    $this->assertInstanceOf('NKConfig', $this->object->getConfig());
    $this->assertSame('some_secret', $this->object->getConfig()->secret);
    $this->assertSame('some_key', $this->object->getConfig()->key);
  }

  /**
   * @expectedException NKConfigException
   */
  public function testGetConfigFail()
  {
    $this->object->getConfig();
  }

  /**
   *
   */
  public function testGetConfigPass()
  {
    $this->object->setConfig(array('id' => 42, 'key' => 'some_key'));
    $this->assertInstanceOf('NKConfig', $this->object->getConfig());
  }

  /**
   * @expectedException NKAuthenticationUnauthorisedException
   */
  public function testGetServiceFail()
  {
    $this->object->expects($this->any())->method('tokenAvailable')->will($this->returnValue(false));
    $this->object->getService();
  }

  public function testGetServicePass()
  {
    $this->object->expects($this->any())->method('tokenAvailable')->will($this->returnValue(true));
    $this->object->setConfig(array('secret' => 'some_secret', 'key' => 'some_key'));
    $result = $this->object->getService();

    $this->assertInstanceOf('NKService', $result);
  }

  /**
   *
   */
  public function testAuthenticatedFail()
  {
    $this->object->expects($this->any())->method('tokenAvailable')->will($this->returnValue(false));
    $this->assertFalse($this->object->authenticated());
  }

  public function testAuthenticatedPass()
  {

    $service = $this->getMock('NKService', array('me'));
    $service->expects($this->any())->method('me')->will($this->returnValue(new NKUser('person.abc')));

    $this->object = $this->getMock('NKAuthentication', array('getToken', 'tokenAvailable', 'getService'));
    $this->object->expects($this->any())->method('tokenAvailable')->will($this->returnValue(true));
    $this->object->expects($this->any())->method('getService')->will($this->returnValue($service));

    $this->assertTrue($this->object->authenticated());
  }

  /**
   * @expectedException NKAuthenticationUnauthorisedException
   */
  public function testUserFail()
  {
    $this->object->expects($this->any())->method('tokenAvailable')->will($this->returnValue(false));
    $this->object->user();
  }

  public function testUserPass()
  {
    $service = $this->getMock('NKService', array('me'));
    $service->expects($this->any())->method('me')->will($this->returnValue(new NKUser('person.abc')));

    $this->object = $this->getMock('NKAuthentication', array('getToken', 'tokenAvailable', 'getService'));
    $this->object->expects($this->any())->method('tokenAvailable')->will($this->returnValue(true));
    $this->object->expects($this->any())->method('getService')->will($this->returnValue($service));

    $result = $this->object->user();

    $this->assertInstanceOf('NKUser', $result);
    $this->assertSame('person.abc', $result->id());
  }
}
?>

<?php

namespace Behat\Mink\Tests;

use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Tests\Helper\Stringer;
use Behat\Mink\WebAssert;
use PHPUnit\Framework\TestCase;

class WebAssertTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;
    /**
     * @var WebAssert
     */
    private $assert;

    /**
     * @before
     */
    public function prepareSession()
    {
        $this->session = $this->getMockBuilder('Behat\\Mink\\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->session->expects($this->any())
            ->method('getDriver')
            ->will($this->returnValue($this->getMockBuilder('Behat\Mink\Driver\DriverInterface')->getMock()));

        $this->assert = new WebAssert($this->session);
    }

    public function testAddressEquals()
    {
        $this->session
            ->expects($this->exactly(2))
            ->method('getCurrentUrl')
            ->will($this->returnValue('http://example.com/script.php/sub/url?param=true#webapp/nav'))
        ;

        $this->assertCorrectAssertion('addressEquals', array('/sub/url#webapp/nav'));
        $this->assertWrongAssertion(
            'addressEquals',
            array('sub_url'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Current page is "/sub/url#webapp/nav", but "sub_url" expected.'
        );
    }

    public function testAddressEqualsEmptyPath()
    {
        $this->session
            ->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn('http://example.com')
        ;

        $this->assertCorrectAssertion('addressEquals', array('/'));
    }

    public function testAddressEqualsEndingInScript()
    {
        $this->session
            ->expects($this->exactly(2))
            ->method('getCurrentUrl')
            ->will($this->returnValue('http://example.com/script.php'))
        ;

        $this->assertCorrectAssertion('addressEquals', array('/script.php'));
        $this->assertWrongAssertion(
            'addressEquals',
            array('/'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Current page is "/script.php", but "/" expected.'
        );
    }

    public function testAddressNotEquals()
    {
        $this->session
            ->expects($this->exactly(2))
            ->method('getCurrentUrl')
            ->will($this->returnValue('http://example.com/script.php/sub/url'))
        ;

        $this->assertCorrectAssertion('addressNotEquals', array('sub_url'));
        $this->assertWrongAssertion(
            'addressNotEquals',
            array('/sub/url'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Current page is "/sub/url", but should not be.'
        );
    }

    public function testAddressNotEqualsEndingInScript()
    {
        $this->session
            ->expects($this->exactly(2))
            ->method('getCurrentUrl')
            ->will($this->returnValue('http://example.com/script.php'))
        ;

        $this->assertCorrectAssertion('addressNotEquals', array('/'));
        $this->assertWrongAssertion(
            'addressNotEquals',
            array('/script.php'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Current page is "/script.php", but should not be.'
        );
    }

    public function testAddressMatches()
    {
        $this->session
            ->expects($this->exactly(2))
            ->method('getCurrentUrl')
            ->will($this->returnValue('http://example.com/script.php/sub/url'))
        ;

        $this->assertCorrectAssertion('addressMatches', array('/su.*rl/'));
        $this->assertWrongAssertion(
            'addressMatches',
            array('/suburl/'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Current page "/sub/url" does not match the regex "/suburl/".'
        );
    }

    public function testCookieEquals()
    {
        $this->session->
            expects($this->any())->
            method('getCookie')->
            will($this->returnValueMap(
                array(
                    array('foo', 'bar'),
                    array('bar', 'baz'),
                )
            ));

        $this->assertCorrectAssertion('cookieEquals', array('foo', 'bar'));
        $this->assertWrongAssertion(
            'cookieEquals',
            array('bar', 'foo'),
            'Behat\Mink\Exception\ExpectationException',
            'Cookie "bar" value is "baz", but should be "foo".'
        );
    }

    public function testCookieExists()
    {
        $this->session->
            expects($this->any())->
            method('getCookie')->
            will($this->returnValueMap(
                array(
                    array('foo', '1'),
                    array('bar', null),
                )
            ));

        $this->assertCorrectAssertion('cookieExists', array('foo'));
        $this->assertWrongAssertion(
            'cookieExists',
            array('bar'),
            'Behat\Mink\Exception\ExpectationException',
            'Cookie "bar" is not set, but should be.'
        );
    }

    public function testStatusCodeEquals()
    {
        $this->session
            ->expects($this->exactly(2))
            ->method('getStatusCode')
            ->will($this->returnValue(200))
        ;

        $this->assertCorrectAssertion('statusCodeEquals', array(200));
        $this->assertWrongAssertion(
            'statusCodeEquals',
            array(404),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Current response status code is 200, but 404 expected.'
        );
    }

    public function testStatusCodeNotEquals()
    {
        $this->session
            ->expects($this->exactly(2))
            ->method('getStatusCode')
            ->will($this->returnValue(404))
        ;

        $this->assertCorrectAssertion('statusCodeNotEquals', array(200));
        $this->assertWrongAssertion(
            'statusCodeNotEquals',
            array(404),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Current response status code is 404, but should not be.'
        );
    }

    public function testResponseHeaderEquals()
    {
        $this->session
            ->expects($this->any())
            ->method('getResponseHeader')
            ->will($this->returnValueMap(
                array(
                    array('foo', 'bar'),
                    array('bar', 'baz'),
                )
            ));

        $this->assertCorrectAssertion('responseHeaderEquals', array('foo', 'bar'));
        $this->assertWrongAssertion(
            'responseHeaderEquals',
            array('bar', 'foo'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Current response header "bar" is "baz", but "foo" expected.'
        );
    }

    public function testResponseHeaderNotEquals()
    {
        $this->session
            ->expects($this->any())
            ->method('getResponseHeader')
            ->will($this->returnValueMap(
                array(
                    array('foo', 'bar'),
                    array('bar', 'baz'),
                )
            ));

        $this->assertCorrectAssertion('responseHeaderNotEquals', array('foo', 'baz'));
        $this->assertWrongAssertion(
            'responseHeaderNotEquals',
            array('bar', 'baz'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Current response header "bar" is "baz", but should not be.'
        );
    }

    public function testResponseHeaderContains()
    {
        $this->session
            ->expects($this->any())
            ->method('getResponseHeader')
            ->will($this->returnValueMap(
                array(
                    array('foo', 'bar'),
                    array('bar', 'baz'),
                )
            ));

        $this->assertCorrectAssertion('responseHeaderContains', array('foo', 'ba'));
        $this->assertWrongAssertion(
            'responseHeaderContains',
            array('bar', 'bz'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The text "bz" was not found anywhere in the "bar" response header.'
        );
    }

    public function testResponseHeaderNotContains()
    {
        $this->session
            ->expects($this->any())
            ->method('getResponseHeader')
            ->will($this->returnValueMap(
                array(
                    array('foo', 'bar'),
                    array('bar', 'baz'),
                )
            ));

        $this->assertCorrectAssertion('responseHeaderNotContains', array('foo', 'bz'));
        $this->assertWrongAssertion(
            'responseHeaderNotContains',
            array('bar', 'ba'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The text "ba" was found in the "bar" response header, but it should not.'
        );
    }

    public function testResponseHeaderContainsObjectWithToString()
    {
        $this->session
            ->expects($this->any())
            ->method('getResponseHeader')
            ->will($this->returnValueMap(
              array(
                array('foo', 'bar'),
                array('bar', 'baz'),
              )
            ));

        $this->assertCorrectAssertion('responseHeaderContains', array('foo', new Stringer('ba')));
        $this->assertWrongAssertion(
            'responseHeaderContains',
            array('bar', 'bz'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The text "bz" was not found anywhere in the "bar" response header.'
        );
    }

    public function testResponseHeaderNotContainsObjectWithToString()
    {
        $this->session
            ->expects($this->any())
            ->method('getResponseHeader')
            ->will(
                $this->returnValueMap(
                    array(
                        array('foo', 'bar'),
                        array('bar', 'baz'),
                    )
                )
            );

        $this->assertCorrectAssertion('responseHeaderNotContains', array('foo', new Stringer('bz')));
        $this->assertWrongAssertion(
            'responseHeaderNotContains',
            array('bar', 'ba'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The text "ba" was found in the "bar" response header, but it should not.'
        );
    }

    public function testResponseHeaderMatches()
    {
        $this->session
            ->expects($this->any())
            ->method('getResponseHeader')
            ->will($this->returnValueMap(
                array(
                    array('foo', 'bar'),
                    array('bar', 'baz'),
                )
            ));

        $this->assertCorrectAssertion('responseHeaderMatches', array('foo', '/ba(.*)/'));
        $this->assertWrongAssertion(
            'responseHeaderMatches',
            array('bar', '/b[^a]/'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The pattern "/b[^a]/" was not found anywhere in the "bar" response header.'
        );
    }

    public function testResponseHeaderNotMatches()
    {
        $this->session
            ->expects($this->any())
            ->method('getResponseHeader')
            ->will($this->returnValueMap(
                array(
                    array('foo', 'bar'),
                    array('bar', 'baz'),
                )
            ));

        $this->assertCorrectAssertion('responseHeaderNotMatches', array('foo', '/bz/'));
        $this->assertWrongAssertion(
            'responseHeaderNotMatches',
            array('bar', '/b[ab]z/'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The pattern "/b[ab]z/" was found in the text of the "bar" response header, but it should not.'
        );
    }

    public function testPageTextContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getText')
            ->will($this->returnValue("Some  page\n\ttext"))
        ;

        $this->assertCorrectAssertion('pageTextContains', array('PAGE text'));
        $this->assertWrongAssertion(
            'pageTextContains',
            array('html text'),
            'Behat\\Mink\\Exception\\ResponseTextException',
            'The text "html text" was not found anywhere in the text of the current page.'
        );
    }

    public function testPageTextNotContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getText')
            ->will($this->returnValue("Some  html\n\ttext"))
        ;

        $this->assertCorrectAssertion('pageTextNotContains', array('PAGE text'));
        $this->assertWrongAssertion(
            'pageTextNotContains',
            array('HTML text'),
            'Behat\\Mink\\Exception\\ResponseTextException',
            'The text "HTML text" appears in the text of this page, but it should not.'
        );
    }

    public function testPageTextMatches()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getText')
            ->will($this->returnValue('Some page text'))
        ;

        $this->assertCorrectAssertion('pageTextMatches', array('/PA.E/i'));
        $this->assertWrongAssertion(
            'pageTextMatches',
            array('/html/'),
            'Behat\\Mink\\Exception\\ResponseTextException',
            'The pattern /html/ was not found anywhere in the text of the current page.'
        );
    }

    public function testPageTextNotMatches()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getText')
            ->will($this->returnValue('Some html text'))
        ;

        $this->assertCorrectAssertion('pageTextNotMatches', array('/PA.E/i'));
        $this->assertWrongAssertion(
            'pageTextNotMatches',
            array('/HTML/i'),
            'Behat\\Mink\\Exception\\ResponseTextException',
            'The pattern /HTML/i was found in the text of the current page, but it should not.'
        );
    }

    public function testResponseContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getContent')
            ->will($this->returnValue('Some page text'))
        ;

        $this->assertCorrectAssertion('responseContains', array('PAGE text'));
        $this->assertWrongAssertion(
            'responseContains',
            array('html text'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The string "html text" was not found anywhere in the HTML response of the current page.'
        );
    }

    public function testResponseNotContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getContent')
            ->will($this->returnValue('Some html text'))
        ;

        $this->assertCorrectAssertion('responseNotContains', array('PAGE text'));
        $this->assertWrongAssertion(
            'responseNotContains',
            array('HTML text'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The string "HTML text" appears in the HTML response of this page, but it should not.'
        );
    }

    public function testResponseContainsObjectWithToString()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getContent')
            ->will($this->returnValue('Some page text'))
        ;

        $this->assertCorrectAssertion('responseContains', array(new Stringer('PAGE text')));
        $this->assertWrongAssertion(
            'responseContains',
            array('html text'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The string "html text" was not found anywhere in the HTML response of the current page.'
        );
    }

    public function testResponseNotContainsObjectWithToString()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getContent')
            ->will($this->returnValue('Some html text'))
        ;

        $this->assertCorrectAssertion('responseNotContains', array(new Stringer('PAGE text')));
        $this->assertWrongAssertion(
            'responseNotContains',
            array('HTML text'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The string "HTML text" appears in the HTML response of this page, but it should not.'
        );
    }

    public function testResponseMatches()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getContent')
            ->will($this->returnValue('Some page text'))
        ;

        $this->assertCorrectAssertion('responseMatches', array('/PA.E/i'));
        $this->assertWrongAssertion(
            'responseMatches',
            array('/html/'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The pattern /html/ was not found anywhere in the HTML response of the page.'
        );
    }

    public function testResponseNotMatches()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('getContent')
            ->will($this->returnValue('Some html text'))
        ;

        $this->assertCorrectAssertion('responseNotMatches', array('/PA.E/i'));
        $this->assertWrongAssertion(
            'responseNotMatches',
            array('/HTML/i'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The pattern /HTML/i was found in the HTML response of the page, but it should not.'
        );
    }

    public function testElementsCount()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('findAll')
            ->with('css', 'h2 > span')
            ->will($this->returnValue(array(1, 2)))
        ;

        $this->assertCorrectAssertion('elementsCount', array('css', 'h2 > span', 2));
        $this->assertWrongAssertion(
            'elementsCount',
            array('css', 'h2 > span', 3),
            'Behat\\Mink\\Exception\\ExpectationException',
            '2 elements matching css "h2 > span" found on the page, but should be 3.'
        );
    }

    public function testElementExists()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(4))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->onConsecutiveCalls(1, null, 1, null))
        ;

        $this->assertCorrectAssertion('elementExists', array('css', 'h2 > span'));
        $this->assertWrongAssertion(
            'elementExists',
            array('css', 'h2 > span'),
            'Behat\\Mink\\Exception\\ElementNotFoundException',
            'Element matching css "h2 > span" not found.'
        );

        $this->assertCorrectAssertion('elementExists', array('css', 'h2 > span', $page));
        $this->assertWrongAssertion(
            'elementExists',
            array('css', 'h2 > span', $page),
            'Behat\\Mink\\Exception\\ElementNotFoundException',
            'Element matching css "h2 > span" not found.'
        );
    }

    public function testElementExistsWithArrayLocator()
    {
        $container = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session->expects($this->never())
            ->method('getPage')
        ;

        $container
            ->expects($this->exactly(2))
            ->method('find')
            ->with('named', array('element', 'Test'))
            ->will($this->onConsecutiveCalls(1, null))
        ;

        $this->assertCorrectAssertion('elementExists', array('named', array('element', 'Test'), $container));
        $this->assertWrongAssertion(
            'elementExists',
            array('named', array('element', 'Test'), $container),
            'Behat\\Mink\\Exception\\ElementNotFoundException',
            'Element with named "element Test" not found.'
        );
    }

    public function testElementNotExists()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(4))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->onConsecutiveCalls(null, 1, null, 1))
        ;

        $this->assertCorrectAssertion('elementNotExists', array('css', 'h2 > span'));
        $this->assertWrongAssertion(
            'elementNotExists',
            array('css', 'h2 > span'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'An element matching css "h2 > span" appears on this page, but it should not.'
        );

        $this->assertCorrectAssertion('elementNotExists', array('css', 'h2 > span', $page));
        $this->assertWrongAssertion(
            'elementNotExists',
            array('css', 'h2 > span', $page),
            'Behat\\Mink\\Exception\\ExpectationException',
            'An element matching css "h2 > span" appears on this page, but it should not.'
        );
    }

    /**
     * @dataProvider getArrayLocatorFormats
     */
    public function testElementNotExistsArrayLocator($selector, $locator, $expectedMessage)
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->once())
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->once())
            ->method('find')
            ->with($selector, $locator)
            ->will($this->returnValue(1))
        ;

        $this->assertWrongAssertion(
            'elementNotExists',
            array($selector, $locator),
            'Behat\\Mink\\Exception\\ExpectationException',
            $expectedMessage
        );
    }

    public function getArrayLocatorFormats()
    {
        return array(
            'named' => array(
                'named',
                array('button', 'Test'),
                'An button matching locator "Test" appears on this page, but it should not.',
            ),
            'custom' => array(
                'custom',
                array('test', 'foo'),
                'An element matching custom "test foo" appears on this page, but it should not.',
            ),
        );
    }

    public function testElementTextContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('getText')
            ->will($this->returnValue('element text'))
        ;

        $this->assertCorrectAssertion('elementTextContains', array('css', 'h2 > span', 'text'));
        $this->assertWrongAssertion(
            'elementTextContains',
            array('css', 'h2 > span', 'html'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The text "html" was not found in the text of the element matching css "h2 > span".'
        );
    }

    public function testElementTextNotContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('getText')
            ->will($this->returnValue('element text'))
        ;

        $this->assertCorrectAssertion('elementTextNotContains', array('css', 'h2 > span', 'html'));
        $this->assertWrongAssertion(
            'elementTextNotContains',
            array('css', 'h2 > span', 'text'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The text "text" appears in the text of the element matching css "h2 > span", but it should not.'
        );
    }

    public function testElementContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('getHtml')
            ->will($this->returnValue('element html'))
        ;

        $this->assertCorrectAssertion('elementContains', array('css', 'h2 > span', 'html'));
        $this->assertWrongAssertion(
            'elementContains',
            array('css', 'h2 > span', 'text'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The string "text" was not found in the HTML of the element matching css "h2 > span".'
        );
    }

    public function testElementNotContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('getHtml')
            ->will($this->returnValue('element html'))
        ;

        $this->assertCorrectAssertion('elementNotContains', array('css', 'h2 > span', 'text'));
        $this->assertWrongAssertion(
            'elementNotContains',
            array('css', 'h2 > span', 'html'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The string "html" appears in the HTML of the element matching css "h2 > span", but it should not.'
        );
    }

    public function testElementAttributeContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('hasAttribute')
            ->will($this->returnValue(true))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('getAttribute')
            ->with('name')
            ->will($this->returnValue('foo'))
        ;

        $this->assertCorrectAssertion('elementAttributeContains', array('css', 'h2 > span', 'name', 'foo'));
        $this->assertWrongAssertion(
            'elementAttributeContains',
            array('css', 'h2 > span', 'name', 'bar'),
            'Behat\\Mink\\Exception\\ElementHtmlException',
            'The text "bar" was not found in the attribute "name" of the element matching css "h2 > span".'
        );
    }

    public function testElementAttributeExists()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->at(0))
            ->method('hasAttribute')
            ->with('name')
            ->will($this->returnValue(true))
        ;

        $element
            ->expects($this->at(1))
            ->method('hasAttribute')
            ->with('name')
            ->will($this->returnValue(false))
        ;

        $this->assertCorrectAssertion('elementAttributeExists', array('css', 'h2 > span', 'name'));
        $this->assertWrongAssertion(
            'elementAttributeExists',
            array('css', 'h2 > span', 'name'),
            'Behat\\Mink\\Exception\\ElementHtmlException',
            'The attribute "name" was not found in the element matching css "h2 > span".'
        );
    }

    public function testElementAttributeNotContains()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('find')
            ->with('css', 'h2 > span')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('hasAttribute')
            ->will($this->returnValue(true))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('getAttribute')
            ->with('name')
            ->will($this->returnValue('foo'))
        ;

        $this->assertCorrectAssertion('elementAttributeNotContains', array('css', 'h2 > span', 'name', 'bar'));
        $this->assertWrongAssertion(
            'elementAttributeNotContains',
            array('css', 'h2 > span', 'name', 'foo'),
            'Behat\\Mink\\Exception\\ElementHtmlException',
            'The text "foo" was found in the attribute "name" of the element matching css "h2 > span".'
        );
    }

    public function testFieldExists()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('findField')
            ->with('username')
            ->will($this->onConsecutiveCalls($element, null))
        ;

        $this->assertCorrectAssertion('fieldExists', array('username'));
        $this->assertWrongAssertion(
            'fieldExists',
            array('username'),
            'Behat\\Mink\\Exception\\ElementNotFoundException',
            'Form field with id|name|label|value "username" not found.'
        );
    }

    public function testFieldNotExists()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('findField')
            ->with('username')
            ->will($this->onConsecutiveCalls(null, $element))
        ;

        $this->assertCorrectAssertion('fieldNotExists', array('username'));
        $this->assertWrongAssertion(
            'fieldNotExists',
            array('username'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'A field "username" appears on this page, but it should not.'
        );
    }

    public function testFieldValueEquals()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(4))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(4))
            ->method('findField')
            ->with('username')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(4))
            ->method('getValue')
            ->will($this->returnValue(234))
        ;

        $this->assertCorrectAssertion('fieldValueEquals', array('username', 234));
        $this->assertWrongAssertion(
            'fieldValueEquals',
            array('username', 235),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The field "username" value is "234", but "235" expected.'
        );
        $this->assertWrongAssertion(
            'fieldValueEquals',
            array('username', 23),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The field "username" value is "234", but "23" expected.'
        );
        $this->assertWrongAssertion(
            'fieldValueEquals',
            array('username', ''),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The field "username" value is "234", but "" expected.'
        );
    }

    public function testFieldValueNotEquals()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(4))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(4))
            ->method('findField')
            ->with('username')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(4))
            ->method('getValue')
            ->will($this->returnValue(235))
        ;

        $this->assertCorrectAssertion('fieldValueNotEquals', array('username', 234));
        $this->assertWrongAssertion(
            'fieldValueNotEquals',
            array('username', 235),
            'Behat\\Mink\\Exception\\ExpectationException',
            'The field "username" value is "235", but it should not be.'
        );
        $this->assertCorrectAssertion('fieldValueNotEquals', array('username', 23));
        $this->assertCorrectAssertion('fieldValueNotEquals', array('username', ''));
    }

    public function testCheckboxChecked()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('findField')
            ->with('remember_me')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('isChecked')
            ->will($this->onConsecutiveCalls(true, false))
        ;

        $this->assertCorrectAssertion('checkboxChecked', array('remember_me'));
        $this->assertWrongAssertion(
            'checkboxChecked',
            array('remember_me'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Checkbox "remember_me" is not checked, but it should be.'
        );
    }

    public function testCheckboxNotChecked()
    {
        $page = $this->getMockBuilder('Behat\\Mink\\Element\\DocumentElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $element = $this->getMockBuilder('Behat\\Mink\\Element\\NodeElement')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->session
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($page))
        ;

        $page
            ->expects($this->exactly(2))
            ->method('findField')
            ->with('remember_me')
            ->will($this->returnValue($element))
        ;

        $element
            ->expects($this->exactly(2))
            ->method('isChecked')
            ->will($this->onConsecutiveCalls(false, true))
        ;

        $this->assertCorrectAssertion('checkboxNotChecked', array('remember_me'));
        $this->assertWrongAssertion(
            'checkboxNotChecked',
            array('remember_me'),
            'Behat\\Mink\\Exception\\ExpectationException',
            'Checkbox "remember_me" is checked, but it should not be.'
        );
    }

    private function assertCorrectAssertion($assertion, $arguments)
    {
        try {
            call_user_func_array(array($this->assert, $assertion), $arguments);
        } catch (ExpectationException $e) {
            $this->fail('Correct assertion should not throw an exception: '.$e->getMessage());
        }
    }

    private function assertWrongAssertion($assertion, $arguments, $exceptionClass, $exceptionMessage)
    {
        if ('Behat\Mink\Exception\ExpectationException' !== $exceptionClass && !is_subclass_of($exceptionClass, 'Behat\Mink\Exception\ExpectationException')) {
            throw new \LogicException('Wrong expected exception for the failed assertion. It should be a Behat\Mink\Exception\ExpectationException.');
        }

        try {
            call_user_func_array(array($this->assert, $assertion), $arguments);
            $this->fail('Wrong assertion should throw an exception');
        } catch (ExpectationException $e) {
            $this->assertInstanceOf($exceptionClass, $e);
            $this->assertSame($exceptionMessage, $e->getMessage());
        }
    }
}

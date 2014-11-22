<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22/11/14
 * Time: 11:57
 */

namespace FeedIo\Parser;


use FeedIo\Feed;

abstract class ParserTestAbstract extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \FeedIo\ParserAbstract
     */
    protected $object;

    const SAMPLE_FILE = '';

    /**
     * @return \FeedIo\ParserAbstract
     */
    abstract public function getObject();

    public function setUp()
    {
        $this->object = $this->getObject();
    }

    public function testCanHandle()
    {
        $document = $this->buildDomDocument(static::SAMPLE_FILE);
        $this->assertTrue($this->object->canHandle($document));
    }

    public function testGetMainElement()
    {
        $document = $this->buildDomDocument(static::SAMPLE_FILE);
        $element = $this->object->getMainElement($document);
        $this->assertInstanceOf('\DomElement', $element);
    }

    public function testBuildFeedRuleSet()
    {
        $ruleSet = $this->object->buildFeedRuleSet();
        $this->assertInstanceOf('\FeedIo\Parser\RuleSet', $ruleSet);
    }

    public function testBuildItemRuleSet()
    {
        $ruleSet = $this->object->buildItemRuleSet();
        $this->assertInstanceOf('\FeedIo\Parser\RuleSet', $ruleSet);
    }

    public function testParseBody()
    {
        $document = $this->buildDomDocument(static::SAMPLE_FILE);
        $feed = $this->object->parse($document, new Feed());
        $this->assertInstanceOf('\FeedIo\Feed', $feed);

        $this->assertNotEmpty($feed->getTitle(), 'title must not be empty');
        $this->assertNotEmpty($feed->getLink(), 'link must not be empty');
        $this->assertNotEmpty($feed->getLastModified(), 'lastModified must not be empty');
        $this->assertTrue($feed->valid(), 'the feed must contain an item');

        $item = $feed->current();
        $this->assertInstanceOf('\FeedIo\Feed\ItemInterface', $item);
        if ( $item instanceof \FeedIo\Feed\ItemInterface ){
            $this->assertNotEmpty($item->getTitle());
            $this->assertNotEmpty($item->getDescription());
            $this->assertNotEmpty($item->getPublicId());
            $this->assertNotEmpty($item->getLastModified());
            $this->assertNotEmpty($item->getLink());
            $optionalFields = $item->getOptionalFields();
            $this->assertCount(1, $optionalFields->getFields());
            $this->assertTrue($optionalFields->has('author'));
        }
    }

    /**
     * @param $filename
     * @return \DOMDocument
     */
    protected function buildDomDocument($filename)
    {
        $file = dirname(__FILE__) . "/../../samples/{$filename}";
        $domDocument = new \DOMDocument();
        $domDocument->load($file, LIBXML_NOBLANKS | LIBXML_COMPACT);

        return $domDocument;
    }
}
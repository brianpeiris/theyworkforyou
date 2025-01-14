<?php

/**
 * Provides test methods for glossary functionality.
 */
class GlossaryTest extends TWFY_Database_TestCase
{

    /**
     * Loads the glossary testing fixture.
     */
    public function getDataSet()
    {
        return $this->createMySQLXMLDataSet(dirname(__FILE__).'/_fixtures/glossary.xml');
    }

    /**
     * Ensures the database is prepared and the glossary class is included for every test.
     */
    public function setUp()
    {
        parent::setUp();
        
        include_once('www/includes/easyparliament/glossary.php');
    }

    /**
     * Test that glossarising a single word works as expected.
     */
    public function testGlossariseNormal()
    {
        $args['sort'] = "regexp_replace";
        $glossary = new GLOSSARY($args);
		
        $this->assertEquals('<a href="/glossary/?gl=1" title="In a general election, each Constituency chooses an MP to represent them...." class="glossary">constituency</a>', $glossary->glossarise('constituency'));
    }

    /**
     * Test that glossarising a word within a link works as expected.
     */
    public function testGlossariseInLink()
    {
        $args['sort'] = "regexp_replace";
        $glossary = new GLOSSARY($args);
		
        $this->assertEquals('<a href="#">constituency</a>', $glossary->glossarise('<a href="#">constituency</a>'));
    }

    /**
     * Test that glossarising a word bounded by other characters without spaces works as expected.
     */
    public function testGlossariseInString()
    {
        $args['sort'] = "regexp_replace";
        $glossary = new GLOSSARY($args);
		
        $this->assertEquals('fooconstituencybar', $glossary->glossarise('fooconstituencybar'));
    }

    /**
     * Test that glossarising a word bounded by other characters with spaces works as expected.
     */
    public function testGlossariseInSpacedString()
    {
        $args['sort'] = "regexp_replace";
        $glossary = new GLOSSARY($args);
		
        $this->assertEquals('foo <a href="/glossary/?gl=1" title="In a general election, each Constituency chooses an MP to represent them...." class="glossary">constituency</a> bar', $glossary->glossarise('foo constituency bar'));
    }

    /**
     * Test that glossarising a single Wikipedia title works as expected.
     */
    public function testWikipediaLinkNormal()
    {
        $args['sort'] = "regexp_replace";
        $glossary = new GLOSSARY($args);
		
        $this->assertEquals('<a href="https://en.wikipedia.org/wiki/MP">MP</a>', $glossary->glossarise('MP'));
    }

    /**
     * Test that glossarising a Wikipedia title within a link works as expected.
     */
    public function testWikipediaLinkInLink()
    {
        $args['sort'] = "regexp_replace";
        $glossary = new GLOSSARY($args);
		
        $this->assertEquals('<a href="#">MP</a>', $glossary->glossarise('<a href="#">MP</a>'));
    }

    /**
     * Test that glossarising a Wikipedia title bounded by other characters without spaces works as expected.
     */
    public function testWikipediaLinkInString()
    {
        $args['sort'] = "regexp_replace";
        $glossary = new GLOSSARY($args);
		
        $this->assertEquals('fooMPbar', $glossary->glossarise('fooMPbar'));
    }

    /**
     * Test that glossarising a Wikipedia title bounded by other characters with spaces works as expected.
     */
    public function testWikipediaLinkInSpacedString()
    {
        $args['sort'] = "regexp_replace";
        $glossary = new GLOSSARY($args);
		
        $this->assertEquals('foo <a href="https://en.wikipedia.org/wiki/MP">MP</a> bar', $glossary->glossarise('foo MP bar'));
    }
}

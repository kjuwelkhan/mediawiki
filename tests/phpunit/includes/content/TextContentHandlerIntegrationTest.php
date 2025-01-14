<?php

use MediaWiki\Title\Title;

/**
 * @group ContentHandler
 * @group Database
 *        ^--- needed, because we do need the database to test link updates
 */
class TextContentHandlerIntegrationTest extends MediaWikiLangTestCase {

	public static function provideGetParserOutput() {
		yield 'Basic render' => [
			'title' => 'TextContentTest_testGetParserOutput',
			'model' => CONTENT_MODEL_TEXT,
			'text' => "hello ''world'' & [[stuff]]\n",
			'expectedHtml' => "<pre>hello ''world'' &amp; [[stuff]]\n</pre>",
			'expectedFields' =>	[ 'Links' => [] ]
		];
		yield 'Multi line render' => [
			'title' => 'TextContentTest_testGetParserOutput',
			'model' => CONTENT_MODEL_TEXT,
			'text' => "Test 1\nTest 2\n\nTest 3\n",
			'expectedHtml' => "<pre>Test 1\nTest 2\n\nTest 3\n</pre>",
			'expectedFields' =>	[ 'Links' => [] ]
		];
	}

	/**
	 * @dataProvider provideGetParserOutput
	 * @covers TextContentHandler::fillParserOutput
	 */
	public function testGetParserOutput( $title, $model, $text, $expectedHtml,
		$expectedFields = null, $parserOptions = null
	) {
		$title = Title::newFromText( $title );
		$content = ContentHandler::makeContent( $text, $title, $model );
		$contentRenderer = $this->getServiceContainer()->getContentRenderer();
		$po = $contentRenderer->getParserOutput( $content, $title, null, $parserOptions );

		$html = $po->getText( [
			'deduplicateStyles' => false,
		] );
		$html = preg_replace( '#<!--.*?-->#sm', '', $html ); // strip comments

		if ( $expectedHtml !== null ) {
			$this->assertEquals( $expectedHtml, trim( $html ) );
		}

		if ( $expectedFields ) {
			foreach ( $expectedFields as $field => $exp ) {
				$getter = 'get' . ucfirst( $field );
				$v = $po->$getter();

				if ( is_array( $exp ) ) {
					$this->assertArrayEquals( $exp, $v );
				} else {
					$this->assertEquals( $exp, $v );
				}
			}
		}
	}
}

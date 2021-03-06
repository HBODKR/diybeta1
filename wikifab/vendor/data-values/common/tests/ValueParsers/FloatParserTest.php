<?php

namespace ValueParsers\Test;

use DataValues\NumberValue;
use ValueParsers\FloatParser;

/**
 * @covers ValueParsers\FloatParser
 *
 * @group ValueParsers
 * @group DataValueExtensions
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FloatParserTest extends StringValueParserTest {

	/**
	 * @see ValueParserTestBase::getInstance
	 *
	 * @return FloatParser
	 */
	protected function getInstance() {
		return new FloatParser();
	}

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$argLists = array();

		$valid = array(
			// Ignoring a single trailing newline is an intended PCRE feature
			"0\n" => 0,

			'0' => 0,
			'1' => 1,
			'42' => 42,
			'01' => 01,
			'9001' => 9001,
			'-1' => -1,
			'-42' => -42,

			'0.0' => 0,
			'1.0' => 1,
			'4.2' => 4.2,
			'0.1' => 0.1,
			'90.01' => 90.01,
			'-1.0' => -1,
			'-4.2' => -4.2,
		);

		foreach ( $valid as $value => $expected ) {
			// Because PHP turns them into ints/floats using black magic
			$value = (string)$value;

			// Because 1 is an int but will come out as a float
			$expected = (float)$expected;

			$expected = new NumberValue( $expected );
			$argLists[] = array( $value, $expected );
		}

		return $argLists;
	}

	/**
	 * @see StringValueParserTest::invalidInputProvider
	 */
	public function invalidInputProvider() {
		$argLists = parent::invalidInputProvider();

		$invalid = array(
			// Trimming is currently not supported
			' 0 ',

			'foo',
			'',
			'--1',
			'1-',
			'1 1',
			'1,',
			',1',
			',1,',
			'one',
			'0x20',
			'+1',
			'1+1',
			'1-1',
			'1.2.3',
		);

		foreach ( $invalid as $value ) {
			$argLists[] = array( $value );
		}

		return $argLists;
	}

}

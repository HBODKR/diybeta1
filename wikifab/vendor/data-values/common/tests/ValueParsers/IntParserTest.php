<?php

namespace ValueParsers\Test;

use DataValues\NumberValue;
use ValueParsers\IntParser;

/**
 * @covers ValueParsers\IntParser
 *
 * @group ValueParsers
 * @group DataValueExtensions
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class IntParserTest extends StringValueParserTest {

	/**
	 * @see ValueParserTestBase::getInstance
	 *
	 * @return IntParser
	 */
	protected function getInstance() {
		return new IntParser();
	}

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$argLists = array();

		$valid = array(
			'0' => 0,
			'1' => 1,
			'42' => 42,
			'01' => 01,
			'9001' => 9001,
			'-1' => -1,
			'-42' => -42,
		);

		foreach ( $valid as $value => $expected ) {
			// Because PHP turns them into ints using black magic
			$value = (string)$value;

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
			'foo',
			'4.2',
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
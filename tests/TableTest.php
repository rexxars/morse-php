<?php
namespace Morse;

/**
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */
class TableTest extends \PHPUnit_Framework_TestCase {
    public function testCanGetAsIfArray() {
        $table = new Table();
        $this->assertSame('10001', $table['=']);
    }

    public function testCanUseIssetAsIfArray() {
        $table = new Table();
        $this->assertSame(true, isset($table['=']));
        $this->assertSame(false, isset($table['%']));
    }

    public function testCanSetNewCharacters() {
        $table = new Table();
        $table['%'] = '10000001';
        $this->assertSame('10000001', $table['%']);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Can't override predefined character
     */
    public function testCantOverwritePredefinedCharacters() {
        $table = new Table();
        $table['A'] = '101';
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Value must be a string of zeroes and ones (0/1)
     */
    public function testCantUseNonMorseCharactersAsValueForCustomCharacter() {
        $table = new Table();
        $table['¤'] = '123';
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage There is already a character with value 1
     */
    public function testCantUseSameMorseCodeAsOtherCharacter() {
        $table = new Table();
        $table['¤'] = '1';
    }

    public function testCanUnsetPreviouslySetCustomCharacters() {
        $table = new Table();
        $table['%'] = '10000001';
        $this->assertSame(true, isset($table['%']));

        unset($table['%']);
        $this->assertSame(false, isset($table['%']));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Can't unset a predefined morse code
     */
    public function testCantUnsetPredefinedCharacters() {
        $table = new Table();
        unset($table['A']);
    }
}
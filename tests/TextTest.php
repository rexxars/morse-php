<?php
namespace Morse;

/**
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 */
class TextTest extends \PHPUnit_Framework_TestCase {
    public function testCanTranslateToMorse() {
        $this->assertSame(
            '. ... .--. . -.  .... --- ...- .-.. .- -. -.. ... -.. .- .-..',
            (new Text())->toMorse('Espen Hovlandsdal')
        );
    }

    public function testInsertsInvalidCharacterReplacement() {
        $this->assertSame(
            '...- .- ..-. ..-. .-.. . .-.  . .-.  --. # -.. -',
            (new Text())->toMorse('Vaffler er gødt')
        );
    }

    public function testCanUseAlternativeCharacterReplacement() {
        $text = (new Text())->setInvalidCharacterReplacement('¤');
        $this->assertSame(
            '...- .- ..-. ..-. .-.. . .-.  . .-.  --. ¤ -.. -',
            $text->toMorse('Vaffler er gødt')
        );
    }

    public function testCanUseAlternativeWordSeparator() {
        $text = (new Text())->setWordSeparator('¤');
        $this->assertSame(
            '...- .- ..-. ..-. .-.. . .-.¤. .-.¤--. --- -.. -',
            $text->toMorse('Vaffler er godt')
        );
    }

    public function testCanTranslateFromMorse() {
        $this->assertSame(
            'ESPEN HOVLANDSDAL',
            (new Text())->fromMorse('. ... .--. . -.  .... --- ...- .-.. .- -. -.. ... -.. .- .-..')
        );
    }

    public function testHandlesInvalidCharacterReplacement() {
        $this->assertSame(
            'VAFFLER ER GDT',
            (new Text())->fromMorse('...- .- ..-. ..-. .-.. . .-.  . .-.  --. # -.. -')
        );
    }

    public function testCanTranslateAlternativeCharacterReplacement() {
        $text = (new Text())->setInvalidCharacterReplacement('¤');
        $this->assertSame(
            'VAFFLER ER GDT',
            $text->fromMorse('...- .- ..-. ..-. .-.. . .-.  . .-.  --. ¤ -.. -')
        );
    }

    public function testCanTranslateAlternativeWordSeparator() {
        $text = (new Text())->setWordSeparator('¤');
        $this->assertSame(
            'VAFFLER ER GODT',
            $text->fromMorse('...- .- ..-. ..-. .-.. . .-.¤. .-.¤--. --- -.. -')
        );
    }

    public function testHandlesInvalidAtEndOfString() {
        $text = (new Text())->setInvalidCharacterReplacement('¤');
        $this->assertSame(
            'AVSLAG',
            $text->fromMorse($text->toMorse('AVSLAG%'))
        );
    }

    public function testCanPassCustomTable() {
        $table = new Table();
        $table['%'] = '001100110011';

        $text = new Text($table);
        $this->assertSame(
            'AVSLAG -30%',
            $text->fromMorse($text->toMorse('Avslag -30%'))
        );
    }
}
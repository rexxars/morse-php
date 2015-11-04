<?php
namespace Morse;

/**
 * Based on code from the "Morse Code Generation from Text" article on CodeProject -
 * http://www.codeproject.com/Articles/85339/Morse-Code-Generation-from-Text
 *
 * @author Walt Fair Jr
 * @author Espen Hovlandsdal <espen@hovlandsdal.com>
 * @license http://www.codeproject.com/info/cpol10.aspx The Code Project Open License (CPOL) 1.02
 * @link https://github.com/rexxars/morse-php
 */
class Wav {
    const DIT = '.';
    const DAH = '-';
    const SPC = '/';

    protected $sampleRate = 11050;
    protected $twopi = 6.283185307;
    protected $cwSpeed = 30;
    protected $frequency = 700;
    protected $data = null;
    protected $timingCodes;

    private $phase  = 0;
    private $dPhase = 0;

    private $bytes = [];

    public function __construct($table = null) {
        $this->timingCodes = $table ? $table : new Table();
    }

    public function setCwSpeed($speed) {
        if (!is_numeric($speed)) {
            throw new \Exception('$Speed must be numeric');
        }

        $this->cwSpeed = $speed;
        return $this;
    }

    public function setSampleRate($rate) {
        if (!is_numeric($rate)) {
            throw new \Exception('Sample rate must be numeric');
        }

        $this->sampleRate = $rate;
        return $this;
    }

    public function setFrequency($frequency) {
        if (!is_numeric($frequency)) {
            throw new \Exception('Frequency must be numeric');
        }

        $this->frequency = $frequency;
        return $this;
    }

    public function generate($text) {
        $this->reset();

        $this->toneTime = 1.0 / $this->frequency; // sec per wavelength
        if ($this->cwSpeed < 15) {
            // use Farnsworth spacing
            $this->ditTime = 1.145 / 15.0;
            $this->charsPc = 122.5 / $this->cwSpeed - 31.0 / 6.0;
        } else {
            $this->ditTime = 1.145 / $this->cwSpeed;
            $this->charsPc = 3;
        }

        $this->wordsPc = floor(2 * $this->charsPc + 0.5);
        $this->dahTime = 3 * $this->ditTime;
        $this->sampleDt = 1.0 / $this->sampleRate;
        $this->phase = 0;
        $this->dPhase = 0;
        $this->slash = false;

        $this->oscReset();

        $dit = 0;
        while ($dit < $this->ditTime) {
            $x = $this->osc();
            // The dit and dah sound both rise during the first half dit-time
            if ($dit < (0.5 * $this->ditTime)) {
                $x = $x * sin((pi() / 2.0) * $dit / (0.5 * $this->ditTime));
                $this->bytes[self::DIT] .= chr(floor(120 * $x + 128));
                $this->bytes[self::DAH] .= chr(floor(120 * $x + 128));
            } else if ($dit > (0.5 * $this->ditTime)) {
                // During the second half dit-time, the dit sound decays
                // but the dah sound stays constant
                $this->bytes[self::DAH] .= chr(floor(120 * $x + 128));
                $x = $x * sin((pi() / 2.0) * ($this->ditTime - $dit) / (0.5 * $this->ditTime));
                $this->bytes[self::DIT] .= chr(floor(120 * $x + 128));
            } else {
                $this->bytes[self::DIT] .= chr(floor(120 * $x + 128));
                $this->bytes[self::DAH] .= chr(floor(120 * $x + 128));
            }
            $this->bytes[self::SPC] .= chr(128);
            $dit += $this->sampleDt;
        }

        // At this point the dit ans space sound have been generated
        // During the next dit-time, the dah sound amplitude is constant
        $dit = 0;
        while ($dit < $this->ditTime) {
            $x = $this->osc();
            $this->bytes[self::DAH] .= chr(floor(120 * $x + 128));
            $dit += $this->sampleDt;
        }

        // During the 3rd dit-time, the dah-sound has a constant amplitude
        // then decays during that last half dit-time
        $dit = 0;
        while ($dit < $this->ditTime) {
            $x = $this->osc();
            if ($dit > (0.5 * $this->ditTime)) {
                $x = $x * sin((pi() / 2.0) * ($this->ditTime - $dit) / (0.5 * $this->ditTime));
                $this->bytes[self::DAH] .= chr(floor(120 * $x + 128));
            } else {
                $this->bytes[self::DAH] .= chr(floor(120 * $x + 128));
            }
            $dit += $this->sampleDt;
        }

        // Convert the text to morse code string
        $text = strtoupper($text);
        $sound = '';
        for ($i = 0; $i < strlen($text); $i++) {
            if ($text[$i] == ' ') {
                for ($j = 0; $j < $this->wordsPc; $j++) {
                    $sound .= $this->bytes[self::SPC];
                }
            } else if (isset($this->timingCodes[$text[$i]])) {
                $xchar = $this->timingCodes[$text[$i]];

                for ($k = 0; $k < strlen($xchar); $k++) {
                    if ($xchar[$k] == '0') {
                        $sound .= $this->bytes[self::DIT];
                    } else {
                        $sound .= $this->bytes[self::DAH];
                    }
                    $sound .= $this->bytes[self::SPC];
                }

                for ($j = 1; $j < $this->charsPc; $j++) {
                    $sound .= $this->bytes[self::SPC];
                }
            }
        }

        $n = strlen($sound);
        for ($i = 0; $i < $n; $i++) {
            $x = ord($sound[$i]);
        }

        // Write out the WAVE file
        $x = $n + 32;
        $soundSize = '';

        for ($i = 0; $i < 4; $i++) {
            $soundSize .= chr($x % 256);
            $x = floor($x / 256);
        }

        $riffHeader = 'RIFF' . $soundSize . 'WAVE';
        $x = $this->sampleRate;
        $sampleRateString = '';

        for ($i = 0; $i < 4; $i++) {
            $sampleRateString .= chr($x % 256);
            $x = floor($x / 256);
        }

        $headerString = 'fmt ' . chr(16) . chr(0) . chr(0) . chr(0) . chr(1) . chr(0) . chr(1) . chr(0);
        $headerString .= $sampleRateString . $sampleRateString . chr(1) . chr(0) . chr(8) . chr(0);
        $x = $n;
        $sampleString = '';

        for ($i = 0; $i < 4; $i++) {
            $sampleString .= chr($x % 256);
            $x = floor($x / 256);
        }

        $sound = 'data' . $sampleString . $sound;
        $wav = $riffHeader . $headerString . $sound;

        return $wav;
    }

    private function osc() {
        $this->phase += $this->dPhase;
        if ($this->phase >= $this->twopi) {
            $this->phase -= $this->twopi;
        }
        return sin($this->phase);
    }

    private function oscReset() {
        $this->phase = 0;
        $this->dPhase = $this->twopi * $this->sampleDt / $this->toneTime;
    }

    private function reset() {
        $this->bytes = [
            self::DIT => '',
            self::DAH => '',
            self::SPC => ''
        ];

        $this->data = null;
    }
}

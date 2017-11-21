<?php

namespace Test\Cli\Question;

use Neutrino\Cli\Output\QuestionHelper;
use Neutrino\Cli\Output\Writer;
use Neutrino\Cli\Question\ChoiceQuestion;
use Neutrino\Cli\Question\ConfirmationQuestion;
use Neutrino\Cli\Question\Question;
use Neutrino\Support\Reflacker;
use Test\TestCase\TestCase;

class QuestionHelperTest extends TestCase
{
    protected static $file = __DIR__ . '/test';

    protected $stdin;

    public function tearDown()
    {
        parent::tearDown();
        $this->closeStdIn();

        @unlink(self::$file);
    }

    public function setUp()
    {
        parent::setUp();

        @unlink(self::$file);
    }

    protected function getStdIn()
    {
        if (isset($this->stdin)) {
            return $this->stdin;
        }

        return $this->stdin = fopen(self::$file, 'r');
    }

    protected function closeStdIn()
    {
        if (isset($this->stdin)) {
            fclose($this->stdin);
            $this->stdin = null;
        }
    }

    public function mockStdIn($mock)
    {
        file_put_contents(self::$file, $mock);
    }

    public function dataPrompt()
    {
        return [
            [null, 'Ask this', new Question('Ask this', null)],
            ['[a, b, c]', 'Ask this', new ChoiceQuestion('Ask this', ['a', 'b', 'c'], 0)],
            ['[a, b, c] (a)', 'Ask this', new ChoiceQuestion('Ask this', ['a', 'b', 'c'], 0, 'a')],
            ['[yes, no] (n)', 'Ask this', new ConfirmationQuestion('Ask this', false)],
            ['[yes, no] (y)', 'Ask this', new ConfirmationQuestion('Ask this', true)],
        ];
    }

    /**
     * @dataProvider dataPrompt
     */
    public function testPrompt($infos, $questionStr, $question)
    {
        $output = $this->createMock(Writer::class);

        $output->expects($this->once())->method('question')->with($questionStr);
        if ($infos === null) {
            $output->expects($this->never())->method('info');
        } else {
            $output->expects($this->once())->method('info')->with($infos);
        }

        Reflacker::invoke(QuestionHelper::class, 'prompt', $output, $question);
    }

    public function testDoAsk()
    {
        $output = $this->createMock(Writer::class);
        $question = new Question('test');

        $this->mockStdIn('test');

        $this->assertEquals(
            'test',
            Reflacker::invoke(QuestionHelper::class, 'doAsk', $output, $this->getStdIn(), $question)
        );
    }

    /**
     * @return array
     */
    public function dataAskQuestion()
    {
        return [
            ['', "\n", new Question('test')],
            ['abc', "\n", new Question('test', 'abc')],
            [false, "\n", new ConfirmationQuestion('Ask this', false)],
            [true, "\n", new ConfirmationQuestion('Ask this', true)],
            [true, "y", new ConfirmationQuestion('Ask this', false)],
            [false, "n", new ConfirmationQuestion('Ask this', true)],
            ['a', "\n", new ChoiceQuestion('Ask this', ['a', 'b', 'c'], 0, 'a')],
            ['b', "b", new ChoiceQuestion('Ask this', ['a', 'b', 'c'], 0, 'a')],
        ];
    }

    /**
     * @dataProvider dataAskQuestion
     */
    public function testAskQuestion($expected, $mock, $question)
    {
        $output = $this->createMock(Writer::class);

        $this->mockStdIn($mock);

        $result = Reflacker::invoke(QuestionHelper::class, 'ask', $output, $this->getStdIn(), $question);

        $this->assertEquals($expected, $result);
    }

    public function testAskChoiceQuestionMultiAttemps()
    {
        $output = $this->createMock(Writer::class);

        $this->mockStdIn("\n\nb");
        $question = new ChoiceQuestion('Ask this', ['a', 'b', 'c'], 3, 'a');

        $result = Reflacker::invoke(QuestionHelper::class, 'ask', $output, $this->getStdIn(), $question);

        $this->assertEquals('b', $result);
    }
}
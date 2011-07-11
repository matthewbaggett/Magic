<?php
/**
 *    Sample minimal test displayer. Generates only
 *    failure messages and a pass count. For command
 *    line use. I've tried to make it look like JUnit,
 *    but I wanted to output the errors as they arrived
 *    which meant dropping the dots.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class MagicTestTextReporter extends SimpleReporter {

    /**
     *    Does nothing yet. The first output will
     *    be sent on the first test start.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     *    Paints the title only.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintHeader($test_name) {
        if (! SimpleReporter::inCli()) {
            header('Content-type: text/plain');
        }
        echo "$test_name\n";
        flush();
    }

    /**
     *    Paints the end of the test with a summary of
     *    the passes and failures.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintFooter($test_name) {
        if ($this->getFailCount() + $this->getExceptionCount() == 0) {
            echo "OK\n";
        } else {
            echo "FAILURES!!!\n";
        }
        echo "Test cases run: " . $this->getTestCaseProgress() .
                "/" . $this->getTestCaseCount() .
                ", Passes: " . $this->getPassCount() .
                ", Failures: " . $this->getFailCount() .
                ", Exceptions: " . $this->getExceptionCount() . "\n\n";
    }

    /**
     *    Paints the test failure as a stack trace.
     *    @param string $message    Failure message displayed in
     *                              the context of the other tests.
     *    @access public
     */
    function paintFail($message) {
        parent::paintFail($message);
        echo $this->getFailCount() . ") $message\n";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        echo "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
        echo "\n";
    }

    /**
     *    Paints a PHP error or exception.
     *    @param string $message        Message to be shown.
     *    @access public
     *    @abstract
     */
    function paintError($message) {
        parent::paintError($message);
        echo "Exception " . $this->getExceptionCount() . "!\n$message\n";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        echo "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
        echo "\n";
    }

    /**
     *    Paints a PHP error or exception.
     *    @param Exception $exception      Exception to describe.
     *    @access public
     *    @abstract
     */
    function paintException($exception) {
        parent::paintException($exception);
        $message = 'Unexpected exception of type [' . get_class($exception) .
                '] with message ['. $exception->getMessage() .
                '] in ['. $exception->getFile() .
                ' line ' . $exception->getLine() . ']';
        echo "Exception " . $this->getExceptionCount() . "!\n$message\n";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        echo "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
        echo "\n";
    }

    /**
     *    echos the message for skipping tests.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    function paintSkip($message) {
        parent::paintSkip($message);
        echo "Skip: $message\n";
    }

   function paintPass($message) {
        parent::paintPass($message);
        echo "  > Pass: ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        echo implode("->", $breadcrumb);
        $message_bits = explode("at [",$message,2);
        echo " -> $message_bits[0]\n";
    }

    /**
     *    Paints formatted text such as dumped privateiables.
     *    @param string $message        Text to show.
     *    @access public
     */
    function paintFormattedMessage($message) {
        echo "$message\n";
        flush();
    }
}
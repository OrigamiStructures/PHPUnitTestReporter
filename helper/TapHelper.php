<?php
/**
 * Process a phpunit 'tap' file to report test results in an html page
 *
 * @author dondrake
 */
class TapHelper {
	
	protected $tap_handle;
	
	public $result = [];
	public $res = [];


	/**
	 * Delimeters to split a line describing a test
	 * 
	 * Typical tests
	 * <pre>
	 * ok 20 - Path\To\ClassTest::testFunction with data set #0 (arg, arg)
	 * not ok 20 - Path\To\ClassTest::testFunction with data set #1 (arg, arg)
	 * </pre>
	 *
	 * @var string
	 */
	protected $split_pattern = '/ \d* - |::| with data set #| \(/';
	protected $json_split_pattern = '/": "|::| with data set #| \(/';
	/**
	 * fist or last line of file
	 * 
	 * @var string
	 */
	protected $ignore_pattern = '/TAP version|\d+\.\.\d+/';
	
	/**
	 * Beginning and ending markers for failed test explanation
	 * 
	 * <pre>
	 *  ---
	 *  message: 'Failed asserting that two arrays are equal.'
	 *  severity: fail
	 *  data:
	 *      got: {  }
	 *      expected: [id]
	 *  ...
	 * </pre>
	 *
	 * @var string
	 */
	protected $failStart = "  ---\n";
	protected $failEnd = "  ...\n";
	
	protected $json_event_test = '/"event": "test"/';


	/**
	 * Array indexes for parsed $result array
	 *
	 * @var string
	 */
	protected $script;
	protected $function;
	protected $test_count;
	
	/**
	 * Collector for failed test explanation
	 *
	 * @var string
	 */
	protected $outcome = '';
	

	/**
	 * Loop through the tap file and create a report array for output parsing
	 */
	public function __construct() {
		$this->tap_handle = fopen('tap.txt', 'r');

		while ($line = fgets($this->tap_handle)) {

			if (preg_match('/ok \d+ - /', $line) === 0) {
//				echo "<p>odd "; // not a normal test line
				if (preg_match($this->ignore_pattern, $line) !== 0) {
//				echo " ignore</p>";
					continue;
					
				} elseif ($line === $this->failStart) {
//				echo " failure</p>";
					$this->failureReport($line);
				
				} else {
//					echo " additional test data: $line</p>";
					$this->multilineData($line);
				}
			} else {
//				echo "<p>parse test</p>";
				$this->parseTest($line);				
			}
		}
		fclose($this->tap_handle);
		
		// now add time entries to the test array
		$this->json_handle = fopen('json.txt', 'r');
		
		// look for a 'test' entry in the json file
		while ($line = fgets($this->json_handle)) {
			if (preg_match($this->json_event_test, $line) === 0) {
				continue;
			} else {
				$this->storeTime($this->jsonTest($line));
			}
		}
		fclose($this->json_handle);
	}
	
	/**
	 * Constructor parsing, find the line that contains test in the json file
	 * 
	 * Once the line is found, parse it to construct the array keys. 
	 * This is step 2 in adding 'time' to a test pased from the 'tap' file. 
	 * 
	 * @param string $line
	 * @return array
	 */
	protected function jsonTest($line) {
		while (preg_match('/"test": /', $line) === 0) {
			$line = fgets($this->json_handle);
		}
		
		// make the array keys
		$keys = preg_split($this->json_split_pattern, trim($line, "\n,\"")) + ['', '', '', '0'];
		$keys[1] = str_replace('\\\\', '\\', $keys[1]);
		
		return $keys;
	}
	
	/**
	 * Constructor parsing, find/store the time entry for a test
	 * 
	 * Typical entry
	 * <pre>
	 *     "time": 1.0736379623413,
	 * </pre>
	 * Time is in the json file. Scan the test to find the time. 
	 * Array keys for proper storage were provided
	 * 
	 * @param array $keys
	 */
	protected function storeTime($keys) {
		list( , $script, $function, $test_count) = $keys;
		do {
			$line = fgets($this->json_handle);
		} while (preg_match('/"time": /', $line) === 0);
		preg_match('/[\d.]+/', $line, $match);
		$this->result[$script][$function][$test_count]['time'] = ($match[0]);
	}

	/**
	 * Constructor parsing of got/expected result in failed test
	 * 
	 * @param string $line
	 */
	protected function failureReport($line) {
		$this->outcome = $line;
		do {
			$line = fgets($this->tap_handle);
			$this->outcome .= $line;			
		} while ($line !== $this->failEnd);
		
		if (!isset($this->result[$this->script][$this->function][0])) {
			$this->test_count = 0;
		}
		$this->result[$this->script][$this->function][$this->test_count]['outcome'] = $this->outcome;
		$this->outcome = '';
	}
	
	/**
	 * Constructor parsing of arguments that contained line breaks
	 * 
	 * @param string $line
	 */
	protected function multilineData($line) {
		$this->result[$this->script][$this->function][$this->test_count][1] .= $line;
	}
	
	/**
	 * Constructor parsing of a test report line
	 * 
	 * @param string $line
	 */
	protected function parseTest($line) {
		list($test_result, $this->script, $this->function, $this->test_count, $arguments) = 
				preg_split($this->split_pattern, $line) + 
				['empty', 'empty', 'empty', '0', ''];
		
		$this->script = trim(str_replace('Failure: ', '', $this->script));
		$this->function = trim($this->function);
		$arguments = !empty($arguments) ? 'array(' . $arguments : $arguments;
		
		if (!isset($this->result[$this->script])) {
			$this->result[$this->script] = [$this->function => [$this->test_count => ['status' => $test_result, 'data' => $arguments]]];

		} else {
			$this->result[$this->script][$this->function][$this->test_count] = ['status' => $test_result, 'data' => $arguments];
		}
		unset($this->result['empty']);
	}
	
	/**
	 * Are any of the tests for a funciton 'not ok'
	 * 
	 * @param array $tests
	 * @return string 'ok' or 'notok'
	 */
	public function functionStatus($tests) {
		$status = array_map(function($value){
			return $value['status'];
		}, $tests);
		return preg_match('/not/', implode('', $status)) === 0 ? 'ok' : 'notok';
	}
	
	/**
	 * Get total time for tests on a function
	 * 
	 * @param array $tests
	 * @return float
	 */
	public function functionTime($tests) {
		return array_reduce($tests, function($result, $value){
			return $result += floatval($value['time']);
		});
	}
	
	/**
	 * Get output string for 'seconds', this test
	 * 
	 * @param array $test
	 * @return string
	 */
	public function testTime($test) {
		return "{$test['time']} seconds";
	}
	
	/**
	 * Get output string for 'data', this test
	 * 
	 * @param array $test
	 * @return string
	 */
	public function testData($test) {
		return $test['data'];
	}
	
	public function testOutcome($test) {
		return $test['outcome'];
	}
	
	/**
	 * Get the status of this test
	 * 
	 * @param type $test
	 * @return string 'ok' or 'notok'
	 */
	public function testStatus($test) {
		return str_replace(' ', '', $test['status']);
	}
	
	/**
	 * Get the number of tests run on a function
	 * 
	 * @param array $tests
	 * @return integer
	 */
	public function countTests($tests) {
		return count($tests);
	}
	
}

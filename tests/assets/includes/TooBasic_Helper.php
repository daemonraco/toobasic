<?php

class TooBasic_Helper {
	//
	// Hidding unwanted methods.
	protected function __construct() {
		
	}
	//
	// Tools @{
	public static function ClearEmails($case, $assertResult = true, $assertReturnValue = true, $promptResult = true) {
		self::RunCommand($case, TESTS_TESTS_ASSETS_DIR.'/cases/scripts/clearemails.sh', $assertResult, $assertReturnValue, $promptResult);
	}
	public static function GetEmail($case, $index, $assertIt = true) {
		$path = "/tmp/fake-mailbox/message_{$index}.eml";

		if($case && $assertIt) {
			$case->assertTrue(is_file($path), "Unable to find message 'message_{$index}.eml'.");
		}

		$contents = false;
		if(is_file($path)) {
			$contents = file_get_contents($path);
		}

		if($case && $assertIt) {
			$case->assertTrue(boolval($contents), "No content obtained for message 'message_{$index}.eml'.");
			$case->assertNotRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $contents, "Content of message 'message_{$index}.eml' seems to have a TooBasic exception.");
			$case->assertNotRegExp(ASSERTION_PATTERN_SMARTY_EXCEPTION, $contents, "Content of message 'message_{$index}.eml' seems to have a Smarty exception.");
			$case->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $contents, "Content of message 'message_{$index}.eml' seems to have a PHP error.");
		}

		return $contents;
	}
	public static function GetUrl($case, $subUrl, $assertIt = true) {
		return self::SendUrl($case, $subUrl, 'GET', null, $assertIt, false);
	}
	public static function GetJSONUrl($case, $subUrl, $assertIt = true) {
		return self::SendJSONUrl($case, $subUrl, 'GET', null, $assertIt, false);
	}
	public static function RunCommand($case, $command, $assertResult = true, $assertReturnValue = true, $promptResult = true) {
		$retValue = false;
		ob_start();
		passthru($command, $retValue);
		$output = ob_get_contents();
		ob_end_clean();

		if(boolval($case) && $assertReturnValue) {
			$case->assertEquals($retValue, 0, "The executed command didn't return zero.");
		}
		if(boolval($case) && $assertResult) {
			$case->assertNotRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $output, "The executed command seems to have returned a TooBasic exception.");
			$case->assertNotRegExp(ASSERTION_PATTERN_SMARTY_EXCEPTION, $output, "The executed command seems to have returned a Smarty exception.");
			$case->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $output, "The executed command seems to have returned a PHP error.");
		}
		if($promptResult) {
			echo "\n\033[1;36mCommand: '{$command}'\033[0m\n";
			if(boolval($output)) {
				echo "{$output}\n";
			}
		}

		return $output;
	}
	public static function SendUrl($case, $subUrl, $method = 'GET', $body = null, $assertIt = true, $jsonBody = true) {
		$url = TRAVISCI_URL_SCHEME.'://localhost';
		$url.= TRAVISCI_URL_PORT ? ':'.TRAVISCI_URL_PORT : '';
		$url.= (TRAVISCI_URI ? TRAVISCI_URI : '').'/';
		$url.= $subUrl;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if($body) {
			if($jsonBody) {
				$dataString = json_encode($body);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
				curl_setopt($ch, CURLOPT_HTTPHEADER, [
					'Content-Type: application/json',
					'Content-Length: '.strlen($dataString)
				]);
			} else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			}
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		curl_close($ch);

		if(boolval($case) && $assertIt) {
			$case->assertTrue(boolval($response), "No response obtained for '{$subUrl}'.");
			$case->assertNotRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$subUrl}' seems to have a TooBasic exception.");
			$case->assertNotRegExp(ASSERTION_PATTERN_SMARTY_EXCEPTION, $response, "Response to '{$subUrl}' seems to have a Smarty exception.");
			$case->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$subUrl}' seems to have a PHP error.");
		}

		return $response;
	}
	public static function SendJSONUrl($case, $subUrl, $method = 'GET', $body = null, $assertIt = true, $jsonBody = true) {
		$json = json_decode(self::SendUrl($case, $subUrl, $method, $body, $assertIt));

		if(boolval($case) && $assertIt) {
			$case->assertTrue(boolval($json), 'Response is not a JSON string.');
		}

		return $json;
	}
	// @}
}

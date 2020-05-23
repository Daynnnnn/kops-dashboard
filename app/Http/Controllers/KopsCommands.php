<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use mikehaertl\shellcommand\Command as ShellCommand;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

use DB;

class KopsCommands extends Controller
{

	private function Decryption($string, $password) {
    	$SplitStringArray = explode(',', $string);

    	foreach ($SplitStringArray as $increment => $SplitString) {
			$HandledSplitStringArray[$increment] = false;

			$key = hash( 'sha256', env('ENCRYPT_KEY') );
			$iv = substr( hash('sha256', $password), 0, 16 );

			$HandledSplitStringArray[$increment] = openssl_decrypt( $SplitString, env('ENCRYPT_METHOD'), $key, 0, $iv );
		}

		return base64_decode (implode('', $HandledSplitStringArray));
    }

    private function getIAMKeys($name, $password) {

		// [KD-TMP] needs some sort of auth
		$EncryptedIAM = false;

		$iamTable = DB::table('iam')->get();

		foreach ($iamTable as $key => $iamRecord) {
			if ($iamRecord->name === $name) {
				$EncryptedIAMKey = $iamRecord->encrypted_iam_key;
				$EncryptedIAMSecret = $iamRecord->encrypted_iam_secret;
				$EncryptedIAM = true;
			}
		}

		if ($EncryptedIAM !== true) {
			return false;
		}

		return array(
			'AWS_ACCESS_KEY_ID' 	=> $this->Decryption($EncryptedIAMKey, $password),
			'AWS_SECRET_ACCESS_KEY' => $this->Decryption($EncryptedIAMSecret, $password)
		);
	}

	public function getInstanceGroups(Request $request) {

		dd($this->getIAMKeys($request->input('IAM_NAME'), $request->input('IAM_PASSWORD')));

		$command = new ShellCommand('export && export && kops get ig');
		$command->addArg('--name='.$request->input('KOPS_NAME'));
		$command->addArg('--state=s3://'.$request->input('KOPS_STATE_STORE'));
		if ($request->input('KOPS_CONFIRM') === 'true') {
			$command->addArg('--yes');
		}

		if ($command->execute()) {
			return response($command->getOutput(), 200)->header('Content-Type', 'text/plain');
		} else {
			return response($command->getError(), 500)->header('Content-Type', 'text/plain');
		}
	}	

}

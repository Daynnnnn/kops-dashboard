<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use DB;

class IAM extends Controller
{
	/*
		Credits to https://wppeople.net/blog/a-simple-two-way-function-to-encrypt-or-decrypt-a-string/ for encrypt function
	*/
	private function Encrypt($string, $password) {

		if (strlen(base64_encode($string)) >= 16) {
			$SplitStringArray = str_split(base64_encode($string), 16);
		} else {
			$SplitStringArray['0'] = base64_encode($string);
		}

		foreach ($SplitStringArray as $increment => $SplitString) {
			$HandledSplitStringArray[$increment] = false;

			$key = hash( 'sha256', env('ENCRYPT_KEY') );
			$iv = substr( hash('sha256', $password), 0, 16 );

			$HandledSplitStringArray[$increment] = openssl_encrypt( $SplitString, env('ENCRYPT_METHOD'), $key, 0, $iv );
		}

		return implode(',', $HandledSplitStringArray);
	}

	public function create(Request $request) {

		// [KD-TMP] needs some sort of auth

		$EncryptedIAMKey = $this->Encryption($request->input('IAM_KEY'), $request->input('IAM_PASSWORD'), 'e');
		$EncryptedIAMSecret = $this->Encryption(base64_encode($request->input('IAM_SECRET')), $request->input('IAM_PASSWORD'), 'e');

		DB::table('iam')->insertOrIgnore([
		    'name' => $request->input('IAM_NAME'),
		    'encrypted_iam_key' => $EncryptedIAMKey,
		    'encrypted_iam_secret' => $EncryptedIAMSecret,
		    'created_at' => date('Y-m-d H:i:s'),
		    'updated_at' => date('Y-m-d H:i:s')
		]);

		return response('Stored IAM Keys', 200)->header('Content-Type', 'text/plain');
	}

	public function delete(Request $request) {

		// [KD-TMP] needs some sort of auth

		$iamTable = DB::table('iam')->get();

		foreach ($iamTable as $key => $iamRecord) {
			if ($iamRecord->name === $request->input('IAM_NAME')) {
				$Record = $iamRecord->id;
				break;
			}
		}

		if (!isset($Record)) {
			return response('Record with that name not in database', 400)->header('Content-Type', 'text/plain');
		}

		DB::table('iam')->where('id', '=', $Record)->delete();

		return response('Deleted IAM Keys', 200)->header('Content-Type', 'text/plain');
	}
}

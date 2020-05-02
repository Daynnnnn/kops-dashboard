<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use DB;

class IAM extends Controller
{
	/*
		Credits to https://wppeople.net/blog/a-simple-two-way-function-to-encrypt-or-decrypt-a-string/ for encrypt function
	*/
	private function Encryption($string, $password, $action = 'e') {
		$output = false;
		$key = hash( 'sha256', env('ENCRYPT_KEY') );
		$iv = substr( hash('sha256', $password), 0, 16 );

		if( $action == 'e' ) {
			$output = base64_encode( openssl_encrypt( $string, env('ENCRYPT_METHOD'), $key, 0, $iv ) );
		} elseif( $action == 'd' ){
			$output = openssl_decrypt( base64_decode( $string ), env('ENCRYPT_METHOD'), $key, 0, $iv );
		}

		return $output;
	}

	public function create(Request $request) {

		// [KD-TMP] needs some sort of auth

		$EncryptedIAM = $this->Encryption($request->input('IAM_USER').':'.$request->input('IAM_SECRET'), $request->input('PASSWORD'), 'e');

		DB::table('iam')->insertOrIgnore([
		    'name' => $request->input('NAME'),
		    'encrypted_iam' => $EncryptedIAM,
		    'created_at' => date('Y-m-d H:i:s'),
		    'updated_at' => date('Y-m-d H:i:s')
		]);

		return response('Stored IAM Keys', 200)->header('Content-Type', 'text/plain');
	}

	public function delete(Request $request) {

		// [KD-TMP] needs some sort of auth

		$iamTable = DB::table('iam')->get();

		foreach ($iamTable as $key => $iamRecord) {
			if ($iamRecord->name === $request->input('NAME')) {
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

	public function get(Request $request) {

		// [KD-TMP] needs some sort of auth

		$iamTable = DB::table('iam')->get();

		foreach ($iamTable as $key => $iamRecord) {
			if ($iamRecord->name === $request->input('NAME')) {
				$EncryptedIAM = $iamRecord->encrypted_iam;
			}
		}

		if (!isset($EncryptedIAM)) {
			return response('Record with that name not in database', 400)->header('Content-Type', 'text/plain');
		}

		$UnencryptedIAM = $this->Encryption($EncryptedIAM, $request->input('PASSWORD'), 'd');

		return response($UnencryptedIAM, 200)->header('Content-Type', 'text/plain');
	}
}

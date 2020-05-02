<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use mikehaertl\shellcommand\Command as ShellCommand;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class KopsCommands extends Controller
{

	private function SpawnBucket($bucket) {
		$s3Client = new S3Client([
			'profile' => 'default',
			'region' => 'eu-west-1',
			'version' => '2006-03-01'
		]);
	
		$OwnedBuckets = $s3Client->listBuckets();
		foreach ($OwnedBuckets['Buckets'] as $OwnedBucket) {
			if($OwnedBucket['Name'] === $bucket) {
				return true;
			}
		}
 		
		try {
			$s3Client->createBucket([
				'Bucket' => $bucket,
			]);
			return true;
		} catch (AwsException $e) {
			return false;
		}

	}

	public function create(Request $request) {

		$bucket = $request->input('name').'-'.substr(md5($request->input('name')), 0, 6);

		if ($this->SpawnBucket($bucket) !== true) {
			return response('Couldn\'t create bucket ' . $bucket, 200)->header('Content-Type', 'text/plain');
		}

		$command = new ShellCommand('kops create cluster');
		$command->addArg('--name='.$request->input('name'));
		$command->addArg('--state=s3://'.$bucket);
		$command->addArg('--zones='.$request->input('zones'));
		$command->addArg('--node-count='.$request->input('node_count'));
		if ($request->input('confirm') === 'true') {
			$command->addArg('--yes');
		}

		if ($command->execute()) {
			return response($command->getOutput(), 200)->header('Content-Type', 'text/plain');
		} else {
			return response($command->getError(), 500)->header('Content-Type', 'text/plain');
		}
	}

	public function delete(Request $request) {

		$bucket = $request->input('name').'-'.substr(md5($request->input('name')), 0, 6);

		$command = new ShellCommand('kops delete cluster');
		$command->addArg('--name='.$request->input('name'));
		$command->addArg('--state=s3://'.$bucket);
		if ($request->input('confirm') === 'true') {
			$command->addArg('--yes');
		}

		if ($command->execute()) {
			return response($command->getOutput(), 200)->header('Content-Type', 'text/plain');
		} else {
			return response($command->getError(), 500)->header('Content-Type', 'text/plain');
		}
	}

}

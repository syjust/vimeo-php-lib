<?php
require_once('vimeo.php');
require_once('constants.php');

class VimeoApi {

	private $vimeo = "";
	public $appName = "testVimeoApi";

	/*
	 * ----------------------------------------
	 * Constructor & Base Functions
	 * ----------------------------------------
	 */
	function __construct() {
		$this->vimeo = new phpVimeo(
			VimeoConstants::CONSUMER_KEY,
			VimeoConstants::CONSUMER_SECRET,
			VimeoConstants::ACCESS_TOKEN,
			VimeoConstants::ACCESS_TOKEN_SECRET
		);
	}
	// HELP()
	// ------
	function help($mess = null) {
		echo "\n";
		echo "HELP invoked\n";
		if ($mess) {
			echo "$mess\n";
		}
		echo "\n";
		echo "USAGE : php VimeoApi options\n";
		echo "where options are :\n";
		echo "\t--list\t\tprint the list of current videos uploaded\n";
		echo "\t--check\t\tcheck the current user quota\n";
		echo "\t--upload 'filename'\tupload the file given as 'filename'\n";
		echo "\t--info\t\tcheck all info of all videos\n";
		echo "\tthats all for moment\n";
		echo "\n";
	}

	/*
	 * ----------------------------------------
	 * VIMEO FUNCTIONS
	 * ----------------------------------------
	 */
	 
	function getList() {
		return $this->vimeo->call('vimeo.videos.getAll', array('user_id' => VimeoConstants::USER_ID));
	}
	function printList() {
		try {
			$videos = $this->getList();
			echo print_r($videos, true);
		} catch (VimeoAPIException $e) {
			$this->apiError($e);
		}
	}

	function checkQuota() {
		$quota = "";
		try {
			$quota = $this->vimeo->call('vimeo.videos.upload.getQuota', array('user_id' => VimeoConstants::USER_ID));
			echo print_r($quota, true);
		} catch (VimeoAPIException $e) {
			$this->apiError($e);
		}
		return $quota;
	}

	function apiError($e) {
		echo "Encountered an API error -- code {$e->getCode()} - {$e->getMessage()}\n";
	}

	function getTicket() {
		$ticket = "";
		try {
			$ticket = $this->vimeo->call('vimeo.videos.upload.getTicket', array('user_id' => VimeoConstants::USER_ID));
		} catch (VimeoAPIException $e) {
			$this->apiError($e);;
		}
	}

	function upload($videoFile) {
		try {
			$quota = $this->checkQuota();
			$video_id = $this->vimeo->upload($videoFile);
			if ($video_id) {
				$this->vimeo->call('vimeo.videos.setTitle', array('title' => basename($videoFile), 'video_id' => $video_id));
				//$this->vimeo->call('vimeo.videos.setDescription', array('description' => 'YOUR_DESCRIPTION', 'video_id' => $video_id));
			} else {
				$this->help('no video_id, upload failed');
			}
		} catch (VimeoAPIException $e) {
			$this->apiError($e);
		}
	}

	function getInfo($id) {
		return $this->vimeo->call('vimeo.videos.getInfo', array('video_id' => $id));
	}
	function printInfo($videoInfo = array()) {

		$info = array();

		if (sizeof($videoInfo) == 0 ) {
			$videos = $this->getList();

			foreach ($videos->videos->video As $video) {
				array_push($info, $this->getInfo($video->id));
			}

		} else {
			if ($videoInfo['id']) {
				$info = $this->getInfo($videoInfo['id']);
			} else {
				$info = "nothing to do with video title today : ".$videoInfo['title']."\n";
				return;
			}
		}
		echo print_r($info, true);

	}
	
	/*
	 * ----------------------------------------
	 * MAIN function & run it
	 * ----------------------------------------
	 */
	function main($args) {
		$api = new VimeoApi();
		$app = array_shift($args);
		if ($app) {
			$api->appName = $app;
		}
		if ($args) {
			if (sizeof($args) >= 1) {
				while (sizeof($args) > 0) {
					$string = array_shift($args);
					echo "arg : $string\n";
					switch($string) {
						case '--list' : $api->printList(); break;
						case '--check' : $api->checkQuota(); break;
						case '--info' : $videoInfo = array();
							if (sizeof($args) >= 1) {
								$tmpArg = array_shift($args);
								if (preg_match('/^[0-9]{8}$/', $tmpArg)) {
									$videoInfo['id'] = $tmpArg;
								} else if (preg_match('/^--.*/', $tmpArg)) {
									array_pop($args, $tmpArg);
								} else {
									$videoInfo['title'] = $tmpArg;
								}
							}
							$api->printInfo($videoInfo);
							break;
						case '--upload' :
							if (sizeof($args) >= 1) {
								$file = array_shift($args);
								if (is_file($file)) {
									$api->upload($file);
								} else {
									$api->help("$file is not a valid 'filename'");
								}
							} else {
								$api->help("--upload need a valid 'filename' as argument");
							}
							break;
						default : $api->help("$string : unrocognized option");
					}
				}
			} else {
				$api->help("Empty args array");
			}
		} else {
			$api->help("No args found");
		}
	}
}

if (sizeof($argv) > 1) {
	VimeoApi::main($argv);
}

?>

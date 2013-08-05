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
		echo "\t--delete 'id'\tdelete video with id 'id'\n";
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
		$videos = $this->getList();
		echo print_r($videos, true);
	}

	function checkQuota() {
		$quota = "";
		$quota = $this->vimeo->call('vimeo.videos.upload.getQuota', array('user_id' => VimeoConstants::USER_ID));
		echo print_r($quota, true);
		return $quota;
	}

	function apiError($e) {
		echo "Encountered an API error -- code {$e->getCode()} - {$e->getMessage()}\n";
	}

	function uploadVimeo($videoFile) {
		$video_id = "";
		$video_id = $this->vimeo->upload($videoFile);
		if ($video_id) {
			$this->vimeo->call('vimeo.videos.setTitle', array('title' => basename($videoFile), 'video_id' => $video_id));
			//$this->vimeo->call('vimeo.videos.setDescription', array('description' => 'YOUR_DESCRIPTION', 'video_id' => $video_id));
		} else {
			$this->help('no video_id, setTitle or upload failed');
		}
	}
	function deleteVimeo($id) {
		$this->vimeo->call('vimeo.videos.delete', array('video_id' => $id));
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
					try {
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
							case '--delete' :
								if (sizeof($args) >= 1) {
									$id = array_shift($args);
									# TODO : implement deleteVimeo function
									# TODO : change other CRUD functionsm with vimeo suffix
									if (preg_match("/^[0-9]{8}$/", $id)) {
										$api->deleteVimeo($id);
									} else {
										$api->help("--delete need a valid 'id' as argument, '$id' is not one");
									}
								} else {
									$api->help("--delete need a valid 'id' as argument");
								}
								break;
							case '--upload' :
								if (sizeof($args) >= 1) {
									$file = array_shift($args);
									if (is_file($file)) {
										$api->uploadVimeo($file);
									} else {
										$api->help("$file is not a valid 'filename'");
									}
								} else {
									$api->help("--upload need a valid 'filename' as argument");
								}
								break;
							default : $api->help("$string : unrocognized option");
						}
					} catch (Exception $e) {
						#VimeoAPIException 
						$this->apiError($e);
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

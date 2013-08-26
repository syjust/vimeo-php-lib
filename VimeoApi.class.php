<?php
require_once('vimeo.php');
require_once('constants.php');

class VimeoApi {

	private $vimeo = "";
	public $appName = "VimeoApi";

	/*
	 * ----------------------------------------
	 * Constructor & Base Functions
	 * ----------------------------------------
	 */
	function __construct() {
		echo __FILE__."->".__METHOD__."\n";
		$this->vimeo = new phpVimeo(
			VimeoConstants::CONSUMER_KEY,
			VimeoConstants::CONSUMER_SECRET,
			VimeoConstants::ACCESS_TOKEN,
			VimeoConstants::ACCESS_TOKEN_SECRET
		);
	}

	/*
	 * ----------------------------------------
	 * VIMEO FUNCTIONS
	 * ----------------------------------------
	 */
	 
	function getList() {
		echo __FILE__."->".__METHOD__."\n";
		return $this->vimeo->call('vimeo.videos.getAll', array('user_id' => VimeoConstants::USER_ID));
	}
	function printList() {
		echo __FILE__."->".__METHOD__."\n";
		$videos = $this->getList();
		echo print_r($videos, true);
	}

	function checkQuota() {
		echo __FILE__."->".__METHOD__."\n";
		$quota = "";
		$quota = $this->vimeo->call('vimeo.videos.upload.getQuota', array('user_id' => VimeoConstants::USER_ID));
		echo print_r($quota, true);
		return $quota;
	}

	function apiError($e) {
		echo __FILE__."->".__METHOD__."\n";
		echo "Encountered an API error -- code {$e->getCode()} - {$e->getMessage()}\n";
	}

	function setTitle($video_id, $fileName) {
		echo __FILE__."->".__METHOD__."\n";
		$this->vimeo->call('vimeo.videos.setTitle', array('title' => $fileName, 'video_id' => $video_id));
	}
	function uploadVimeo($videoFile) {
		echo __FILE__."->".__METHOD__."\n";
		$video_id = "";
		$video_id = $this->vimeo->upload($videoFile);
		if ($video_id) {
			$this->setTitle($video_id, basename($videoFile));
			//$this->vimeo->call('vimeo.videos.setDescription', array('description' => 'YOUR_DESCRIPTION', 'video_id' => $video_id));
		} else {
			$this->help('no video_id, setTitle or upload failed');
		}
	}
	function deleteVimeo($id) {
		echo __FILE__."->".__METHOD__."\n";
		$this->vimeo->call('vimeo.videos.delete', array('video_id' => $id));
	}

	function getInfo($id) {
		echo __FILE__."->".__METHOD__."\n";
		return $this->vimeo->call('vimeo.videos.getInfo', array('video_id' => $id));
	}
	function printInfo($videoInfo = array()) {
		echo __FILE__."->".__METHOD__."\n";

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
}

?>

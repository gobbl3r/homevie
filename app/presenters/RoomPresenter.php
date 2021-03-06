<?php

namespace App;

use Nette;

/**
 * Description of RoomPresenter
 *
 * @author David Kuna
 */
class RoomPresenter extends BasePresenter {
	
	/** @var Nette\Database\Context */
	private $database;
	private $roomId;
	
	const SALT = "3-L*)!dZ";
	
	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}
	
	public function renderDefault()
	{
		$this->template->rooms = $this->database->table('room')
				->order('created_at DESC')
				->limit(5);
	}
	
	private function getSeesionId(){
		$id = $this->context->session->getId();
		if(empty($id)){
			$this->context->session->start();
			return $this->getSeesionId();
		}else{
			return $id;
		}
	}
	
	private function generateToken(){
		$sessionId = $this->getSeesionId();
		return md5(self::SALT . $sessionId . time());
	}
	
	public function renderView($roomId)
	{
		$this->roomId = $roomId;
		$this->template->room = $this->database->table('room')->get($this->roomId);
		//$this->template->openTokData = $this->getOpenTokData();
		
		$data['token'] = $this->generateToken();
		$data['room_id'] = $this->roomId;
		$data['phpsessid'] = $this->getSeesionId();
		//$data['owner'] = 0;
		$this->template->token = $data['token'];
		$this->sessions->createOrUpdate($data);
		$this->context->httpResponse->setCookie('TOKEN', $data['token'], '1 days', null, null, null, false);
	}
	
	private function getOpenTokData(){
		$apiObj = new \OpenTokSDK(\API_Config::API_KEY, \API_Config::API_SECRET);
		$session = $apiObj->create_session();

		$data['apiKey'] = \API_Config::API_KEY;
		$data['sessionId'] = $session->getSessionId();
		$data['token'] = $apiObj->generate_token($data['sessionId']);

		return $data;
	}
}

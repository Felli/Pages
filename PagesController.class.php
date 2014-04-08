<?php
// Copyright 2014 Aleksandr Tsiolko

if (!defined("IN_ESOTALK")) exit;

class PagesController extends ETController {

	protected function model()
	{
		return ET::getInstance("pagesModel");
	}

	protected function plugin()
	{
		return ET::$plugins["Pages"];
	}

	public function index($pageSlug = false)
	{
		list($pageId,$slug)  = explode('-',trim($pageSlug));
		if(!is_numeric($pageId)){
			$this->redirect(URL(""));
		}
		
		$page = $this->model()->getById((int)$pageId);

		// Stop here with a 404 header if the page wasn't found.
		if (!$page) {
			$this->render404(T("message.pageNotFound"), true);
			return false;
		}elseif(!ET::$session->userId and $page['hideFromGuests']){
			$this->render404(T("message.pageNotFound"), true);
			return false;
		}
		$this->title = $page["title"];
		
		if (strlen($page['content']) > 155) {
			$description = substr($page['content'], 0, 155) . " ...";
			$description = str_replace(array("\n\n", "\n"), " ", $description);
		}else{
			$description = $page["content"];
		}		
		$this->addToHead("<meta name='description' content='".sanitizeHTML($description)."'>");
		
		$this->data("page", $page);
		$this->render($this->plugin()->getView("page"));
	}
}

<?php
// Copyright 2014 Aleksandr Tsiolko

if (!defined("IN_ESOTALK")) exit;

class PagesAdminController extends ETAdminController {

	protected function model()
	{
		return ET::getInstance("pagesModel");
	}

	protected function plugin()
	{
		return ET::$plugins["Pages"];
	}

	public function index()
	{
		$pages = $this->model()->get();

		$this->addCSSFile($this->plugin()->getResource("admin.css"));

		$this->addJSFile("core/js/lib/jquery.ui.js");
		$this->addJSFile($this->plugin()->getResource("admin.js"));
		$this->addJSLanguage("message.confirmDelete");

		$this->data("pages", $pages);
		$this->render($this->plugin()->getView("admin/pages"));
	}

	public function edit($pageId = "")
	{
		if (!($page = $this->model()->getById((int)$pageId))) {
			$this->render404();
			return;
		}

		$form = ETFactory::make("form");
		$form->action = URL("admin/pages/edit/".$page["pageId"]);
		$form->setValues($page);

		if ($form->isPostBack("cancel")) $this->redirect(URL("admin/pages"));

		if ($form->validPostBack("save")) {

			$data = array(
				"title" => $form->getValue("title"),
				"content" => $form->getValue("content"),
				"slug"=>slug($form->getValue("slug")),
				"hideFromGuests" => (bool)$form->getValue("hideFromGuests"),
				"menu" => $form->getValue("menu")
			);

			$model = $this->model();
			$model->updateById($page["pageId"], $data);

			if ($model->errorCount()) $form->errors($model->errors());

			else $this->redirect(URL("admin/pages"));
		}

		$this->data("form", $form);
		$this->data("page", $page);
		$this->render($this->plugin()->getView("admin/editPage"));
	}


	public function create()
	{
		$form = ETFactory::make("form");
		$form->action = URL("admin/pages/create");

		if ($form->isPostBack("cancel")) $this->redirect(URL("admin/pages"));

		if ($form->validPostBack("save")) {

			$model = $this->model();

			$data = array(
				"title" => $form->getValue("title"),
				"content" => $form->getValue("content"),
				"slug"=>slug($form->getValue("slug")),
				"hideFromGuests" => (bool)$form->getValue("hideFromGuests"),
				"menu" => $form->getValue("menu"),
				"position" => $model->count()
			);

			$model->create($data);

			if ($model->errorCount()) $form->errors($model->errors());

			else $this->redirect(URL("admin/pages"));
		}

		$this->data("form", $form);
		$this->data("page", null);
		$this->render($this->plugin()->getView("admin/editPage"));
	}


	public function delete($pageId = "")
	{
		if (!$this->validateToken()) return;

		// Get this field's details. If it doesn't exist, show an error.
		if (!($page = $this->model()->getById((int)$pageId))) {
			$this->render404();
			return;
		}

		$this->model()->deleteById($page["pageId"]);

		$this->redirect(URL("admin/pages"));
	}

	public function reorder()
	{
		if (!$this->validateToken()) return;

		$ids = (array)R("ids");

		for ($i = 0; $i < count($ids); $i++) {
			$this->model()->updateById($ids[$i], array("position" => $i));
		}
	}

}

<?php

namespace App\Presenters;

use Nette;

class HomepagePresenter extends Nette\Application\UI\Presenter {

	protected $moviesModel;
	protected $prettyNameModel;

	/**
	 * @persistent
	 */
	public $selectedTags = [];

	public function __construct(\App\Model\MoviesModel $moviesModel, \App\Model\PrettyNameModel $prettyNameModel) {
		parent::__construct();
		$this->moviesModel = $moviesModel;
		$this->prettyNameModel = $prettyNameModel;
	}

	public function renderDefault() {
		$this->template->movies = $this->moviesModel->fetchAll($this->selectedTags);
		$this->template->cantParse = $this->moviesModel->getCantParse();
		$this->template->allTags = $this->moviesModel->getAllTags();
		$this->template->selectedTags = $this->selectedTags;
	}

	public function actionPlay($path) {
		$this->moviesModel->playMovie($path);
		$this->redirect("default");
	}

	public function renderPrettyNameList($movie = null) {
		$suggestionsList = [];
		if (isset($movie)) {
			$suggestionsList = $this->prettyNameModel->getPrettyNameMoviesSuggestions($movie);
			if (count($suggestionsList) == 1) {
				$this->redirect("prettyName", ['id' => $suggestionsList[0]['id']]);
			}
		}
		$this->presenter->template->suggestionsList = $suggestionsList;
	}

	public function renderPrettyName($id) {
		$prettyName = $this->prettyNameModel->createPrettyName($id);
		$this->template->prettyName = $prettyName;
	}

	public function renderWatch($path) {
		$arr = explode("/", $path);
		$filename = array_pop($arr);
		$mime = @finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
		header('Content-type: ' . $mime ?: 'video/x-matroska');
		header('Content-Length: ' . filesize($path)); // provide file size
		header("Content-Disposition: inline; filename=" . $filename . ";");
		header("Expires: -1");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		readfile($path);
	}

	public function createComponentPrettyNameForm() {
		$form = new \Nette\Application\UI\Form;

		$form->addText("movie", "Movie:")
			->setRequired();

		$form->addSubmit("submit");

		$form->onSuccess[] = function ($form, $values) {
			$this->redirect("prettyNameList", ["movie" => $values["movie"]]);
		};

		return $form;
	}

	public function handleSelectTag($tag) {
		if (!isset($this->selectedTags[$tag])) {
			$this->selectedTags[$tag] = true;
		} else {
			$this->selectedTags[$tag] = !$this->selectedTags[$tag];
		}

	}

}

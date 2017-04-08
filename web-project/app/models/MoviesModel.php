<?php

namespace App\Model;

use App\Model\TheMovieDbApiModel;

/**
 * Description of MoviesModel
 *
 * @author Raja
 */
class MoviesModel {

	/**
	 * @var TheMovieDbApiModel
	 */
	protected $theMovieDbApiModel;
	protected $cantParse;
	protected $movies = [];

	public function __construct(TheMovieDbApiModel $theMovieDbApiModel) {
		$this->theMovieDbApiModel = $theMovieDbApiModel;
	}

	protected $listOfDirs = [
		"E:/Filmy/HD/",
		"E:/Filmy/NEW/",
		//"E:/Filmy/SEEN/",
	];
	protected $skipFiles = [
		".",
		"..",
		"_new",
	];
	protected $videoExtensions = [
		"mkv",
		"avi",
		"mp4",
	];
	protected $otherExtensions = [
		"sub",
		"srt",
	];

	const VLC_PATH = "C:/Program Files/VideoLAN/VLC/vlc.exe";

	public function fetchAll($filter = []) {
		$list = [];
		foreach ($this->listOfDirs as $dir) {
			$list = array_merge($list, $this->readFromDir($dir));
		}
		sort($list);
		$moviesOnHdd = $this->parseMovies($list);
		$this->movies = $this->decorateMovies($moviesOnHdd);

		if ($filter){
			$filter = array_filter($filter);
			foreach($this->movies as $id => $movie){
				if(array_intersect(array_keys($filter), $movie->getGenre()) != array_keys($filter)){
					$movie->setVisibility(false);
				}
			}

		}
		return $this->movies;
	}

	public function getAllTags() {
		$res = [];
		foreach ($this->movies as $m) {
			$res = array_merge($res, $m->getGenre());
		}
		sort($res);
		return array_unique($res);
	}

	protected function readFromDir($dir) {
		$res = [];
		$handle = opendir($dir);
		while (false !== ($file = readdir($handle))) {
			if (!in_array($file, $this->skipFiles) && is_dir($dir . $file)) {
				$res[] = [
					"name" => iconv("windows-1250", "UTF-8", $file),
					"path" => iconv("windows-1250", "UTF-8", $dir . $file . $this->getVideoFile($dir . $file . "/")),
					"dateCreated" => new \DateTime(date("Y-m-d H:i:s", filemtime($dir . $file))),
				];
			}
		}
		closedir($handle);

		return $res;
	}

	protected function parseMovies($list) {
		$res = [];
		foreach ($list as $line) {
			preg_match("~(.+?)( \- .*)?\(([0-9]+)?%\).*([0-9]{4}).*~i", $line["name"], $matches);
			if (!count($matches)) {
				$this->cantParse[] = $line["path"];
			} else {
				$m = new MovieEntity;
				$m->setName(trim($matches[1]));
				$m->setDirector(trim($matches[2]));
				$m->setRating(trim($matches[3]));
				$m->setYear(trim($matches[4]));
				$m->setPrettyName($line["name"]);
				$m->setFilePath($line["path"]);
				$m->setDateDownloaded($line["dateCreated"]);
				$res[] = $m;
			}
		}

		return $res;
	}

	protected function getVideoFile($dir) {
		$handle = opendir($dir);
		while (false !== ($file = readdir($handle))) {
			if (!in_array($file, $this->skipFiles) && is_file($dir . $file)) {
				$arr = explode(".", $file);
				$extension = array_pop($arr);
				if (in_array($extension, $this->videoExtensions)) {
					return "/" . $file;
				}
				if (!in_array($extension, $this->otherExtensions)) {
					$this->cantParse[] = iconv("windows-1250", "UTF-8", $dir . $file);
				}
			}
		}
		closedir($handle);
		return "";
	}

	protected function decorateMovies($list) {
		foreach ($list as $movie) {
			$msearch = $this->theMovieDbApiModel->searchMovieByName($movie->getName(), $movie->getYear());
			$m = $this->theMovieDbApiModel->getMovie($msearch["id"]);
			if (!empty($m)) {
				$movie->setImage(TheMovieDbApiModel::getImageUrl($m["poster_path"]));
				$genres = array_map(function ($item) {
					return $item["name"];
				}, $m["genres"]);
				$movie->setGenre($genres);
				$movie->setDescription($m['tagline']);
			} else {
				$this->cantParse[] = $movie->getPrettyName();
			}
		}

		return $list;
	}

	public function getCantParse() {
		return $this->cantParse;
	}

	public function playMovie($path) {
		$path = iconv("UTF-8", "windows-1250", $path);
		$cmd = "\"" . self::VLC_PATH . "\" \"" . str_replace("/", "\\", $path) . "\" --fullscreen &";
		$this->execInBackground($cmd);
	}

	protected function execInBackground($cmd) {
		if (substr(php_uname(), 0, 7) == "Windows") {
			pclose(popen("start /B " . $cmd, "r"));
		} else {
			exec($cmd . " > /dev/null &");
		}
	}

}

<?php

namespace App\Model;

/**
 * Description of TheMovieDbApiModel
 *
 * @author Raja
 */
class TheMovieDbApiModel {

    protected $apiKey = null;
    protected $cache;

    protected $predefined = [
        'In Your Eyes' => 226448,
    ];

    const BASE_URL = "https://api.themoviedb.org/3/";
    const MOVIE = "movie/";
    const SERCH_MOVIE = "search/movie/";
    const IMAGE_BASE_URL = "http://image.tmdb.org/t/p/w342";
    const MOVIE_CREDIT_URL = "movie/%s/credits";

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $this->cache = $this->createCache();
    }

    public function searchMovieByName($name, $year) {
        if (isset($this->predefined[$name])){
            return $this->getMovie($this->predefined[$name]);
        }
        
        $url = self::BASE_URL . self::SERCH_MOVIE;
        $params = [
            "query" => $name,
        ];
        $search = $this->getData($url, $params);

        if (isset($search["results"])) {
            foreach($search["results"] as $row){
                $releaseDate = new \DateTime($row["release_date"]);
                if ($releaseDate->format("Y") == $year && ($row['original_title'] == $name || $row['title'] == $name)){
                    return $row;
                }                
            }
            
            return $search["results"][0];
        }
        return [];
    }
    
    public function searchMoviesByName($name){
        $url = self::BASE_URL . self::SERCH_MOVIE;
        $params = [
            "query" => $name,
        ];
        return $this->getData($url, $params);        
    }
    
    public function getMovie($id){
        $url = self::BASE_URL . self::MOVIE . $id;
        $movie = $this->getData($url, []);
        return $movie;
    }
    
    public function getMovieCredit($id){
        $url = self::BASE_URL . sprintf(self::MOVIE_CREDIT_URL, $id);
        
        return $this->getData($url, []);
    }

    public static function getImageUrl($url) {
        return self::IMAGE_BASE_URL . $url;
    }

    protected function getData($url, $params) {
        if (!isset($params["api_key"])) {
            $params["api_key"] = $this->apiKey;
        }

        $contentUrl = $url . "?" . http_build_query($params);
        $contents = $this->cache->load(md5($contentUrl));
        if ($contents === NULL) {
            $contents = file_get_contents($contentUrl);
            $this->cache->save(md5($contentUrl), $contents);
        }

        return json_decode($contents, true);
    }

    protected function createCache() {
        $storage = new \Nette\Caching\Storages\FileStorage('D:/www/myMoviesList/web-project/temp');
        $cache = new \Nette\Caching\Cache($storage);
        return $cache;
    }

}

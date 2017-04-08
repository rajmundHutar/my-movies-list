<?php

namespace App\Model;

/**
 * Description of PrettyNameModel
 *
 * @author Raja
 */
class PrettyNameModel {
    
    protected $theMovieDbApiModel;

    public function __construct(TheMovieDbApiModel $theMovieDbApiModel) {
        $this->theMovieDbApiModel = $theMovieDbApiModel;
    }
    
    public function getPrettyNameMoviesSuggestions($name){
        $list = $this->theMovieDbApiModel->searchMoviesByName($name);
        $res = [];

        foreach ($list["results"] as $movie){
            $res[] = [
                "id" => $movie["id"],
                "name" => $movie["title"],
                "description" => $movie["overview"],
                "image" => $this->theMovieDbApiModel->getImageUrl($movie["poster_path"]),
                "year" => (new \DateTime($movie["release_date"]))->format("Y"),
            ];
        }        
        return $res;
    }
    
    public function createPrettyName($id){
        $movie = $this->theMovieDbApiModel->getMovie($id);
        $movieCredit = $this->theMovieDbApiModel->getMovieCredit($id);
        $director = [];
        foreach($movieCredit["crew"] as $crewMember){
            if ($crewMember["job"] === "Director"){
                $director[] = $crewMember["name"];
            }
        }
        $res = "";
        if (in_array($movie["original_language"], ["cs", "sk"])){
            $res .= $movie["original_title"];
        } else {
            $res .= $movie["title"];
        }         
        if (!empty($director)){
            $res .= " - " . implode(", ", $director);
        }
        $res .= " (%) ";
        $date = new \DateTime($movie["release_date"]);
        $res .= $date->format("Y") . " ";
        $genres = [];
        foreach ($movie["genres"] as $genre){
            $genres[] = $genre["name"];
        }
        $res .= implode(", ", $genres);
        return $res;
    }
}

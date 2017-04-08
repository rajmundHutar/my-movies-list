<?php

namespace App\Model;

/**
 * Description of MovieEntity
 *
 * @author Raja
 */
class MovieEntity
{

    private $name;
    private $prettyName;
    private $rating;
    private $genre;
    private $director;
    private $image;
    private $year;
    private $filePath;
    private $description;
    private $dateDownloaded;
    private $visibility = true;

    /**
     * @return mixed
     */
    public function getDateDownloaded()
    {
        return $this->dateDownloaded;
    }

    /**
     * @param mixed $dateDownloaded
     */
    public function setDateDownloaded($dateDownloaded)
    {
        $this->dateDownloaded = $dateDownloaded;
    }

    function getDescription()
    {
        return $this->description;
    }

    function setDescription($description)
    {
        $this->description = $description;
    }

    function getFilePath()
    {
        return $this->filePath;
    }

    function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    function getYear()
    {
        return $this->year;
    }

    function setYear($year)
    {
        $this->year = $year;
    }

    function getPrettyName()
    {
        return $this->prettyName;
    }

    function getImage()
    {
        return $this->image;
    }

    function setPrettyName($prettyName)
    {
        $this->prettyName = $prettyName;
    }

    function setImage($image)
    {
        $this->image = $image;
    }

    function getName()
    {
        return $this->name;
    }

    function getRating()
    {
        return $this->rating;
    }

    function getGenre()
    {
        return $this->genre;
    }

    function getDirector()
    {
        return $this->director;
    }

    function setName($name)
    {
        $this->name = $name;
    }

    function setRating($rating)
    {
        $this->rating = $rating;
    }

    function setGenre($genre)
    {
        $this->genre = $genre;
    }

    function setDirector($director)
    {
        $this->director = $director;
    }

	/**
	 * @return mixed
	 */
	public function getVisibility() {
		return $this->visibility;
	}

	/**
	 * @param mixed $visibility
	 */
	public function setVisibility($visibility) {
		$this->visibility = $visibility;
	}

}

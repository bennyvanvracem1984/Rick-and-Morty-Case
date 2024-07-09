<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use NickBeen\RickAndMortyPhpApi\Character;
use NickBeen\RickAndMortyPhpApi\Dto\Episode as DtoEpisode;
use NickBeen\RickAndMortyPhpApi\Dto\Location as DtoLocation;
use NickBeen\RickAndMortyPhpApi\Episode;
use NickBeen\RickAndMortyPhpApi\Exceptions\NotFoundException;
use NickBeen\RickAndMortyPhpApi\Location;

class RickAndMortyService
{
    private const START_API_PAGE_NUMBER = 1;

    private Character $character;
    private Location $location;
    private Episode $episode;

    public function __construct()
    {
       $this->character = new Character();
       $this->location = new Location();
       $this->episode = new Episode();
    }

    /**
     * Get all locations.
     *
     * @return ArrayCollection
     * @throws NotFoundException
     */
    public function getAllLocations(): ArrayCollection
    {
        $totalPages = $this->location->get()->info->pages;
        $locationCollection = new ArrayCollection();

        $i = self::START_API_PAGE_NUMBER;

        while($i <= $totalPages)
        {
            /* @var DtoLocation $location */
            foreach (($this->location->page($i)->get())->results as $location)
            {
                $locationCollection->add($location);
            }
            $i++;
        }

        return $locationCollection;
    }

    /**
     * Get all episodes.
     *
     * @return ArrayCollection
     * @throws NotFoundException
     */
    public function getAllEpisodes(): ArrayCollection
    {
        $totalPages = $this->episode->get()->info->pages;
        $episodeCollection = new ArrayCollection();

        $i = self::START_API_PAGE_NUMBER;

        while($i <= $totalPages)
        {
            /* @var DtoEpisode $episode */
            foreach (($this->episode->page($i)->get())->results as $episode)
            {
                $episodeCollection->add($episode);
            }
            $i++;
        }

        return $episodeCollection;
    }

    /**
     * Get all unique dimensions based on the locations.
     *
     * @return ArrayCollection
     */
    public function getAllLocationDimensions(): ArrayCollection
    {
        $locations = $this->getAllLocations();

        $dimensions = $locations->map(function(DtoLocation $location) {
            return $location->dimension;
        });

        return new ArrayCollection(array_unique($dimensions->toArray()));
    }
}

<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use NickBeen\RickAndMortyPhpApi\Character;
use NickBeen\RickAndMortyPhpApi\Dto\Character as DtoCharacter;
use NickBeen\RickAndMortyPhpApi\Dto\Episode as DtoEpisode;
use NickBeen\RickAndMortyPhpApi\Dto\Location as DtoLocation;
use NickBeen\RickAndMortyPhpApi\Episode;
use NickBeen\RickAndMortyPhpApi\Exceptions\NotFoundException;
use NickBeen\RickAndMortyPhpApi\Location;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use function array_unique;
use function str_replace;

class RickAndMortyService
{
    private const START_API_PAGE_NUMBER = 1;
    private const REPLACE_API_URL_CHARACTER = 'https://rickandmortyapi.com/api/character/';

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

    /**
     * Get character by Id.
     *
     * @param int $id
     * @return DtoCharacter
     * @throws NotFoundException
     */
    public function getCharacterById(int $id): DtoCharacter
    {
        return $this->character->get($id);
    }

    /**
     * Get all resident characters info based on a locationId.
     *
     * @param int $id
     * @return object|array
     */
    public function getAllCharactersByLocation(int $id): object|array
    {
        try {
            $residents = new ArrayCollection($this->location->get($id)->residents);
            $residentIds = $residents->map(function(string $url) {
                return (int) str_replace(self::REPLACE_API_URL_CHARACTER, '', $url);
            });

            return $this->getMultipleCharactersById($residentIds);

        } catch (Exception $exception) {
            throw new BadRequestException($exception->getMessage(),$exception->getCode());
        }
    }

    /**
     * Get all resident characters info based on an episodeId.
     *
     * @param int $id
     * @return object|array
     */
    public function getAllCharactersByEpisode(int $id): object|array
    {
        try {
            $episodes = new ArrayCollection($this->episode->get($id)->characters);
            $residentIds = $episodes->map(function(string $url) {
                return (int) str_replace(self::REPLACE_API_URL_CHARACTER, '', $url);
            });

            return $this->getMultipleCharactersById($residentIds);

        } catch (Exception $exception) {
            throw new BadRequestException($exception->getMessage(),$exception->getCode());
        }
    }

    /**
     * Get all unique characters info based on a given location dimension.
     *
     * @param string $dimension
     * @return object|array
     */
    public function getAllCharactersByDimension(string $dimension): object|array
    {
        try {
            $totalPages = $this->location->withDimension($dimension)->get()->info->pages;
            $residentCollection = new ArrayCollection();

            $i = self::START_API_PAGE_NUMBER;

            while($i <= $totalPages)
            {
                /* @var DtoLocation $location */
                foreach (($this->location->withDimension($dimension)->page($i)->get())->results as $location)
                {
                    foreach ($location->residents as $resident)
                    {
                        $residentCollection->add($resident);
                    }
                }
                $i++;
            }

            $residentIds = $residentCollection->map(function(string $url) {
                return (int) str_replace(self::REPLACE_API_URL_CHARACTER, '', $url);
            });

            $ressidentArray = $residentIds->toArray();

            return $this->getMultipleCharactersById(new ArrayCollection(array_unique($ressidentArray)));

        } catch (Exception $exception) {
            throw new BadRequestException($exception->getMessage(),$exception->getCode());
        }
    }

    /**
     * Private function to get multiple character info based on an array of IDs.
     *
     * @param ArrayCollection $characterIds
     * @return object|array
     * @throws NotFoundException
     */
    private function getMultipleCharactersById(ArrayCollection $characterIds): object|array
    {

        return $this->character->get(... $characterIds->toArray());
    }
}

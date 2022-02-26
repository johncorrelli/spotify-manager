<?php

declare(strict_types=1);

namespace App\Models\Spotify;

use App\Exceptions\Spotify\NotPlayingTrackException;
use App\Interfaces\Spotify\PlayerInterface;
use App\Models\Spotify\Playable\Artist;
use App\Models\Spotify\Playable\Track;
use App\Models\Storage\Skippables;

/**
 * @method void            nextTrack()
 * @method PlayerInterface getPlayingTrackOrDie()
 */
trait BlocksCurrent
{
    public function blockSong(Skippables $skippablesDb): void
    {
        $db = $this->getDb($skippablesDb);
        $db->songs[] = $this->formatCurrentTrack();

        $this->save($skippablesDb, $db);
    }

    public function blockArtist(Skippables $skippablesDb): void
    {
        $db = $this->getDb($skippablesDb);
        $db->artists[] = $this->formatCurrentArtist();

        $this->save($skippablesDb, $db);
    }

    public function blockAlbum(Skippables $skippablesDb): void
    {
        $db = $this->getDb($skippablesDb);
        $db->albums[] = $this->formatCurrentAlbum();

        $this->save($skippablesDb, $db);
    }

    protected function save(Skippables $db, object $contents): void
    {
        $db->write($contents);
    }

    protected function getDb(Skippables $skippablesDb): object
    {
        $db = $skippablesDb->get();

        if (!property_exists($db, 'songs')) {
            $db->songs = [];
        }
        if (!property_exists($db, 'albums')) {
            $db->albums = [];
        }
        if (!property_exists($db, 'artists')) {
            $db->artists = [];
        }

        return $db;
    }

    protected function getCurrentTrack(): Track
    {
        $item = $this->getPlayingTrackOrDie();
        $item = $item->getItem();

        if (!($item instanceof Track)) {
            throw new NotPlayingTrackException('A track is not playing!');
        }

        return $item;
    }

    protected function formatCurrentTrack(): object
    {
        $track = $this->getCurrentTrack();

        return (object) [
            'songId' => $track->getId(),
            'albumId' => $track->getAlbum()->getId(),
            'artistIds' => array_map(fn (Artist $artist) => $artist->getId(), $track->getArtists()),
            'comment' => $track->getComment(),
        ];
    }

    protected function formatCurrentArtist(): object
    {
        $track = $this->getCurrentTrack();

        /**
         * @var Artist
         */
        $firstArtist = $track->getArtists()[0];

        return (object) [
            'artistId' => $firstArtist->getId(),
            'comment' => $firstArtist->getComment(),
        ];
    }

    protected function formatCurrentAlbum(): object
    {
        $track = $this->getCurrentTrack();
        $album = $track->getAlbum();

        return (object) [
            'albumId' => $album->getId(),
            'comment' => $album->getComment(),
        ];
    }
}

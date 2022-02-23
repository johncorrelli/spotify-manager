<?php

namespace App\Models\Spotify\Playable;

use App\Interfaces\Spotify\PlayableInterface;

class Track extends BasePlayable implements PlayableInterface
{
    /**
     * @var Artist[]
     */
    protected array $artists;

    protected Album $album;

    protected int $duration;

    public function __construct(string $id, string $name, Album $album, array $artists)
    {
        parent::__construct($id, $name);

        $this->album = $album;
        $this->artists = $artists;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @return Artist[]
     */
    public function getArtists(): array
    {
        return $this->artists;
    }

    public function getAlbum(): Album
    {
        return $this->album;
    }

    public function getComment(): string
    {
        return implode(' ', [
            $this->getName(),
            'by:',
            $this->getArtistNames(),
            'on album:',
            $this->getAlbum()->getName()
        ]);
    }

    private function getArtistNames(): string
    {
        $names = array_map(fn (Artist $artist) => $artist->getName(), $this->getArtists());

        return implode(', ', $names);
    }
}

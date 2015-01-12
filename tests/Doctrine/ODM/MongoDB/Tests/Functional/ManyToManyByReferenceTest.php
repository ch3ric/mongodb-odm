<?php

namespace Doctrine\ODM\MongoDB\Tests\Functional\Ticket;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\ArrayCollection;

class ManyToManyExampleTest extends \Doctrine\ODM\MongoDB\Tests\BaseTest
{
    public function testManyToMany()
    {
        $videoOne = new Video();
        $this->dm->persist($videoOne);
        $videoTwo = new Video();
        $this->dm->persist($videoTwo);
        $this->dm->flush();

        $playlist = new Playlist();
        $playlist->setVideos(new ArrayCollection([$videoOne, $videoTwo]));
        $this->dm->persist($playlist);
        $this->dm->flush();

        $this->assertCount(2, $playlist->getVideos());
        $this->assertCount(1, $videoOne->getPlaylists());
        $this->assertCount(1, $videoTwo->getPlaylists());
        $this->assertEquals(1, $videoOne->getNbPlaylists());
        $this->assertEquals(1, $videoTwo->getNbPlaylists());

        $playlist->removeVideo($videoOne);
        $playlist->removeVideo($videoTwo);
        $this->dm->flush();

        $this->assertCount(0, $playlist->getVideos());
        $this->assertCount(0, $videoOne->getPlaylists());
        $this->assertCount(0, $videoTwo->getPlaylists());
        $this->assertEquals(0, $videoOne->getNbPlaylists());
        $this->assertEquals(0, $videoTwo->getNbPlaylists());
    }
}

/**
 * @ODM\Document
 */
class Playlist
{
    /**
     * @ODM\Id
     */
    protected $id;

    /**
     * @ODM\ReferenceMany(targetDocument="Video", inversedBy="playlists")
     */
    protected $videos;

    public function __construct()
    {
        $this->videos = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setVideos($videos)
    {
        $this->resetVideos();
        foreach ($videos as $video) {
            $this->addVideo($video);
        }
    }

    public function resetVideos()
    {
        foreach ($this->getVideos() as $video) {
            $this->removeVideo($video);
        }
    }

    public function getVideos()
    {
        return $this->videos;
    }

    public function addVideo(Video $video)
    {
        $this->videos->add($video);
        $video->addPlaylist($this);
    }

    public function removeVideo(Video $video)
    {
        $this->videos->removeElement($video);
        $video->removePlaylist($this);
    }
}

/**
 * @ODM\Document
 */
class Video
{
    /**
     * @ODM\Id
     */
    protected $id;

    /**
     * @ODM\Int()
     */
    protected $nbPlaylists = 0;

    /**
     * @ODM\ReferenceMany(targetDocument="Playlist", mappedBy="videos")
     */
    protected $playlists;

    public function __construct()
    {
        $this->playlists = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function addPlaylist(Playlist $playlist)
    {
        $this->playlists->add($playlist);
        $this->nbPlaylists++;
    }

    public function removePlaylist(Playlist $playlist)
    {
        $this->playlists->removeElement($playlist);
        $this->nbPlaylists--;
    }

    public function getPlaylists()
    {
        return $this->playlists;
    }

    public function resetPlaylists()
    {
        $this->playlists->clear();
        $this->nbPlaylists = 0;
    }

    public function setPlaylists(array $playlists)
    {
        $this->resetPlaylists();
        foreach ($playlists as $playlist) {
            $this->addPlaylist($playlist);
        }
    }

    public function getNbPlaylists()
    {
        return $this->nbPlaylists;
    }

    public function setNbPlaylists($nbPlaylists)
    {
        $this->nbPlaylists = $nbPlaylists;
    }
}

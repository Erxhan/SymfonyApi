<?php

namespace Dev\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Image
 *
 * @ORM\Table(name="image")
 * @ORM\Entity(repositoryClass="Dev\ApiBundle\Repository\ImageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Image
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="originalname", type="string", length=255, nullable=false)
     */
    private $originalname;

    /**
     * @var string
     * @ORM\Column(name="mimetype", type="string", length=255, nullable=false)
     */
    private $mimetype;

    /**
     * @var string
     * @ORM\Column(name="uniqname", type="string", length=255, nullable=false, unique=true)
     */
    private $uniqname;

    /**
     * @var UploadedFile
     * @Assert\Image(mimeTypes={"image/jpeg", "image/jpg", "image/png"}, maxSize="8M")
     */
    private $file;


    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function uploadImage()
    {
        if ($this->file === null) {
            return;
        }
        $this->removeImage();

        $this->originalname = $this->file->getClientOriginalName();
        $this->mimetype = $this->file->getMimeType() !== null ? $this->file->getMimeType() : $this->file->getClientMimeType();

        $this->uniqname = sprintf('%s.%s', uniqid(sprintf("%s_", time()), true), $this->file->getClientOriginalExtension());

        $path = $this->getPath();
        $this->file->move($path, $this->uniqname);
        $this->file = null;
    }

    /**
     * @ORM\PreRemove()
     */
    public function removeImage()
    {
        if ($this->uniqname === null) {
            return;
        }
        $path = $this->getPath();
        $file = join(DIRECTORY_SEPARATOR, [$path, $this->uniqname]);

        if (file_exists($file)) {
            unlink($file);
        }
        $this->originalname = null;
        $this->mimetype = null;
        $this->uniqname = null;

    }

    private function getPath()
    {
        $path = join(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..', '..', 'web', 'images']);
        $path = realpath($path);
        if ($path === false) {
            throw new \Exception('The path does not exist.');
        }
        return $path;
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set originalname
     *
     * @param string $originalname
     *
     * @return Image
     */
    public function setOriginalname($originalname)
    {
        $this->originalname = $originalname;

        return $this;
    }

    /**
     * Get originalname
     *
     * @return string
     */
    public function getOriginalname()
    {
        return $this->originalname;
    }

    /**
     * Set mimetype
     *
     * @param string $mimetype
     *
     * @return Image
     */
    public function setMimetype($mimetype)
    {
        $this->mimetype = $mimetype;

        return $this;
    }

    /**
     * Get mimetype
     *
     * @return string
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }

    /**
     * Set uniqname
     *
     * @param string $uniqname
     *
     * @return Image
     */
    public function setUniqname($uniqname)
    {
        $this->uniqname = $uniqname;

        return $this;
    }

    /**
     * Get uniqname
     *
     * @return string
     */
    public function getUniqname()
    {
        return $this->uniqname;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }
}

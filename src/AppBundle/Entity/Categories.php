<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Categories
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoriesRepository")
 */
class Categories
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
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="image_name", type="string", length=255)
     */
    private $imageName;

    /**
     * @var float
     *
     * @ORM\Column(name="discount", type="float")
     */
    private $discount = 0;

    /**
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount(float $discount)
    {
        $this->discount = $discount;
    }

    private $quantity;

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
     * Set name
     *
     * @param string $name
     *
     * @return Categories
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setProductsNumber($quantity) {
        $this->quantity = $quantity;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    /**
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * @param string $imageName
     */
    public function setImageName(string $imageName)
    {
        $this->imageName = $imageName;
    }
}


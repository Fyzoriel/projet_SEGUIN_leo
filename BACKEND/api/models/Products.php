<?php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Products
 *
 * @ORM\Table(name="products", indexes={@ORM\Index(name="IDX_B3BA5A5AC54C8C93", columns={"type_id"}), @ORM\Index(name="IDX_B3BA5A5AA23B42D", columns={"manufacturer_id"})})
 * @ORM\Entity
 */
class Products
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="products_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="height", type="float", precision=10, scale=0, nullable=false)
     */
    private $height;

    /**
     * @var float
     *
     * @ORM\Column(name="length", type="float", precision=10, scale=0, nullable=false)
     */
    private $length;

    /**
     * @var float
     *
     * @ORM\Column(name="max_speed", type="float", precision=10, scale=0, nullable=false)
     */
    private $maxSpeed;

    /**
     * @var int
     *
     * @ORM\Column(name="capacity", type="integer", nullable=false)
     */
    private $capacity;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", precision=10, scale=0, nullable=false)
     */
    private $price;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false, options={"default"="1"})
     */
    private $enabled = true;

    /**
     * @var \Types
     *
     * @ORM\ManyToOne(targetEntity="Types", inversedBy="products")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     * })
     */
    private $type;

    /**
     * @var \Manufacturers
     *
     * @ORM\ManyToOne(targetEntity="Manufacturers", inversedBy="products")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="manufacturer_id", referencedColumnName="id")
     * })
     */
    private $manufacturer;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Models", mappedBy="product")
     */
    protected $model = array();

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Images", mappedBy="product")
     */
    protected $images = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->model = new \Doctrine\Common\Collections\ArrayCollection();
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Products
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set height.
     *
     * @param float $height
     *
     * @return Products
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height.
     *
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set length.
     *
     * @param float $length
     *
     * @return Products
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length.
     *
     * @return float
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set maxSpeed.
     *
     * @param float $maxSpeed
     *
     * @return Products
     */
    public function setMaxSpeed($maxSpeed)
    {
        $this->maxSpeed = $maxSpeed;

        return $this;
    }

    /**
     * Get maxSpeed.
     *
     * @return float
     */
    public function getMaxSpeed()
    {
        return $this->maxSpeed;
    }

    /**
     * Set capacity.
     *
     * @param int $capacity
     *
     * @return Products
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * Get capacity.
     *
     * @return int
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * Set price.
     *
     * @param float $price
     *
     * @return Products
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set enabled.
     *
     * @param bool $enabled
     *
     * @return Products
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled.
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set type.
     *
     * @param \Types|null $type
     *
     * @return Products
     */
    public function setType(\Types $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return \Types|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set manufacturer.
     *
     * @param \Manufacturers|null $manufacturer
     *
     * @return Products
     */
    public function setManufacturer(\Manufacturers $manufacturer = null)
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * Get manufacturer.
     *
     * @return \Manufacturers|null
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * Add model.
     *
     * @param \Models $model
     *
     * @return Products
     */
    public function addModel(\Models $model)
    {
        $this->model[] = $model;

        return $this;
    }

    /**
     * Remove model.
     *
     * @param \Models $model
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeModel(\Models $model)
    {
        return $this->model->removeElement($model);
    }

    /**
     * Get model.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get images.
     *
     * @return ArrayCollection
     */
    public function getImages(): ArrayCollection
    {
        return $this->images;
    }

    /**
     * Add image.
     *
     * @param Images $image
     *
     * @return Products
     */
    public function addImage(Images $image) {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProduct($this);
        }
        return $this;
    }

    /**
     * @param Images $image
     * @return Products
     */
    public function removeImage(Images $image): Products
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getProduct() === $this) {
                $image->setProduct(null);
            }
        }
        return $this;
    }
}
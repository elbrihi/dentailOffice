<?php

namespace DentalOffice\UserBundle\Domain\Entity;

use ApiPlatform\Metadata\Post;
use DentalOffice\PatientBundle\Domain\Entity\Patient;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Stock\ProductBundle\Domain\Entity\Category;
use Stock\ProductBundle\Domain\Entity\Lot;
use Stock\ProductBundle\Domain\Entity\Product;
use Stock\SupplierBundle\Domain\Entity\Supplier;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use DentalOffice\UserBundle\UI\Controller\PostAddNewUserController;
use DentalOffice\UserBundle\UI\Controller\PostUserAuthTokenController;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use DentalOffice\UserBundle\Infrastructure\Persistence\Doctrine\Repository\UserRepository;

#[ApiResource(
    operations:[
        new Post(
            controller: PostAddNewUserController::class,
            uriTemplate: "/user/add",
            normalizationContext: ['groups' => ['read:user']],
            denormalizationContext: ['groups' => ['write:user']],

        ),
        new Post(
            controller: PostUserAuthTokenController::class,
            uriTemplate: "/user/auth-token",
            normalizationContext: ['groups' => ['read:authToken']],
            denormalizationContext: ['groups' => ['write:authToken']],

        ),
    ]
)]



#[ORM\Entity(repositoryClass: UserRepository::class)]



#[UniqueEntity('username')]
class User implements UserInterface , PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:read','read:user','write:user','product:read', 'product:write','supplier:read', 'supplier:write'])]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    #[Groups(['category:read','read:user','write:user','product:read', 'product:write','supplier:read', 'supplier:write'])]
    private ?string $username = null;


    #[ORM\Column]
    #[Groups(['category:read','read:user','write:user','product:read', 'product:write','supplier:read', 'supplier:write'])]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:authToken', 'write:authToken'])]
    
    private ?string $apiToken = null;

    protected $plainPassword;

    #[ORM\OneToMany(targetEntity: Patient::class, mappedBy: 'createdBy')]
    private Collection $patients;

    #[ORM\OneToMany(targetEntity: Patient::class, mappedBy: 'modifiedBy')]
    private Collection $patientdModifieds;


    public function __construct()
    {
        $this->patients = new ArrayCollection();
        $this->patientdModifieds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }


   
    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     *
     * @return void
     */
    public function eraseCredentials(): void
    {

    }

    /**
     * Returns the identifier for this user (e.g. username or email address).
     */
    public function getUserIdentifier(): string
    {
        return "";
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of plainPassword
     */ 
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set the value of plainPassword
     *
     * @return  self
     */ 
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Get the value of apiToken
     */ 
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * Set the value of apiToken
     *
     * @return  self
     */ 
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @return Collection<int, Patient>
     */
    public function getPatients(): Collection
    {
        return $this->patients;
    }

    public function addPatient(Patient $patient): static
    {
        if (!$this->patients->contains($patient)) {
            $this->patients->add($patient);
            $patient->setCreatedBy($this);
        }

        return $this;
    }

    public function removePatient(Patient $patient): static
    {
        if ($this->patients->removeElement($patient)) {
            // set the owning side to null (unless already changed)
            if ($patient->getCreatedBy() === $this) {
                $patient->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Patient>
     */
    public function getPatientdModifieds(): Collection
    {
        return $this->patientdModifieds;
    }

    public function addPatientdModified(Patient $patientdModified): static
    {
        if (!$this->patientdModifieds->contains($patientdModified)) {
            $this->patientdModifieds->add($patientdModified);
            $patientdModified->setModifiedBy($this);
        }

        return $this;
    }

    public function removePatientdModified(Patient $patientdModified): static
    {
        if ($this->patientdModifieds->removeElement($patientdModified)) {
            // set the owning side to null (unless already changed)
            if ($patientdModified->getModifiedBy() === $this) {
                $patientdModified->setModifiedBy(null);
            }
        }

        return $this;
    }

}

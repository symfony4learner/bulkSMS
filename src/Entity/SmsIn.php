<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SmsInRepository")
 */
class SmsIn
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $group_name;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $sms_origin;

    /**
     * @ORM\Column(type="text")
     */
    private $message;

    /**
     * @ORM\Column(type="datetime")
     */
    private $received_on;

    public function getId()
    {
        return $this->id;
    }

    public function getGroupName(): ?string
    {
        return $this->group_name;
    }

    public function setGroupName(string $group_name): self
    {
        $this->group_name = $group_name;

        return $this;
    }

    public function getSmsOrigin(): ?string
    {
        return $this->sms_origin;
    }

    public function setSmsOrigin(string $sms_origin): self
    {
        $this->sms_origin = $sms_origin;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getReceivedOn(): ?\DateTimeInterface
    {
        return $this->received_on;
    }

    public function setReceivedOn(\DateTimeInterface $received_on): self
    {
        $this->received_on = $received_on;

        return $this;
    }
}

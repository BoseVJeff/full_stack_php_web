<?php declare (strict_types = 1);

class UserFile
{
    private int $id;
    private string $name;
    private int $userId;
    private string $upload;
    private ?string $originalName;
    private int $size;
    private ?string $mimetype;

    /**
     * Create a new File instance from a database row
     *
     * @param object $row Database row object containing file information
     */
    public function __construct($row)
    {
        $this->id           = $row['id'];
        $this->name         = $row['name'];
        $this->userId       = $row['user_id'];
        $this->upload       = $row['upload'];
        $this->originalName = $row['original_name'];
        $this->size         = $row['size'];
        $this->mimetype     = $row['mimetype'];
    }

    /**
     * Get the file ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the file name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the user ID
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Get the upload timestamp
     */
    public function getUpload(): string
    {
        return $this->upload;
    }

    /**
     * Get the original file name
     */
    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    /**
     * Get the file size
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Get the file MIME type
     */
    public function getMimetype(): ?string
    {
        return $this->mimetype;
    }
}

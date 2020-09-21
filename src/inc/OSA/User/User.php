<?php


namespace OSA\User;


class User
{
    private int $id;
    private string $name;
    public function __construct(array $data)
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->id;
    }
}
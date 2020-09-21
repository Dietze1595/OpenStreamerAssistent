<?php


namespace OSA\User;


class User
{
    private int $uid;
    private string $name;
    public function __construct(array $data)
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }
}
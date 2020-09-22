<?php
namespace OSA\User;

use OSA\Database\PDO;

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

    public function setName(string $username)
    {

        try {
            $con = PDO::getInstance()->get();
            $stmt = $con->prepare('UPDATE `users` SET `name` = :name WHERE `id` = :id');
            $stmt->bindValue(':name', $username);
            $stmt->bindValue(':id', $this->getUid());
            $stmt->execute();
            $this->name = $username;
        } catch (\Exception $ex) {

        } finally {
            isset($con) && PDO::getInstance()->put($con);
        }
    }
}